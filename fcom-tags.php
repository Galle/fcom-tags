<?
/*
Plugin Name: Fcom Tag plugin
Plugin URI:  http://
Description: Plugin que crea tabla para relacionar tags
Version:     
Author:      Guillermo Galleguillos
Author URI:  https://github.com/Galle
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: fcom-tags
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $fcom_tags_db_version;
$fcom_tags_db_version = '1.0';

include( plugin_dir_path( __FILE__ ) . 'fcom-tags-widget.php');
include( plugin_dir_path( __FILE__ ) . 'fcom-tags-json.php');

function fcom_tags_install() {
	global $wpdb;
	global $fcom_tags_db_version;

	$table_name = $wpdb->prefix . 'fcom_tag_relations';
	$terms_table_name = $wpdb->prefix. 'terms';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		tag_id bigint(20) UNSIGNED NOT NULL,
		tag_padre_id bigint(20) UNSIGNED NOT NULL,
		FOREIGN KEY (tag_id) REFERENCES $terms_table_name(term_id),
		FOREIGN KEY (tag_padre_id) REFERENCES $terms_table_name(term_id),
		UNIQUE KEY fcom_tag_id (tag_id,tag_padre_id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'fcom_tag_db_version', $fcom_tags_db_version );
}
register_activation_hook( __FILE__, 'fcom_tags_install' );

add_action( 'wp_delete_term', 'fcom_remove_tags' );
function fcom_remove_tags() {
    global $wpdb;
    if ($taxonomy == 'post_tag')
    {
        $wpdb->query('DELETE FROM '.$table_name.' WHERE tag_id = '.$term_id.' OR tag_padre_id = '.$term_id);
    }
}

add_action('activated_plugin','save_error');
function save_error(){
  file_put_contents(ABSPATH. 'wp-content/plugins/fcom-tags/error.html', ob_get_contents());
}


/*
    Menu para wp-admin
*/

add_action('admin_menu', 'fcom_tags_plugin_menu');
function fcom_tags_plugin_menu() {
	add_menu_page('Fcom Tag Settings', 'Fcom Tag Settings', 'manage_categories', 'fcom-tags-settings.php', 'fcom_tags_settings_page', 'dashicons-admin-generic');
}

add_action( 'admin_init', 'fcom_tags_settings' );
function fcom_tags_settings() {
	register_setting( 'my-plugin-settings-group', 'accountant_name' );
	register_setting( 'my-plugin-settings-group', 'accountant_phone' );
	register_setting( 'my-plugin-settings-group', 'accountant_email' );
}

/*add_action( 'admin_menu', 'fcom_admin_menu' );
function fcom_admin_menu() {
	add_menu_page( 'Jerarquía Tags', 'FCOM jerarquía tags', 'editors', 'fcom/admin-page.php', 'fcom_tags_admin_page', 'dashicons-tickets');
}

function fcom_tags_admin_page(){
	?>
	<div class="wrap">
		<h2>Welcome To My Plugin</h2>
	</div>
	<?php
}
*/
function fcom_tags_settings_page() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'fcom_tag_relations';
    $terms_table_name = $wpdb->prefix. 'terms';
    $term_table = $wpdb->prefix.'term_taxonomy';
    
    wp_enqueue_script('jquery-js', plugins_url('/js/jquery-2.1.4.min.js', __FILE__));
    //wp_enqueue_script('fcom-settings-js', plugins_url('/js/fcom_settings.js', __FILE__));
    wp_enqueue_script( 'fcom-ajax-script', plugins_url( '/js/fcom_settings.js', __FILE__ ));
	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'fcom-ajax-script', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 222 ) );
    
    
    $all_tags = $wpdb->get_results("
	            SELECT term_id, name
	            FROM ".$terms_table_name." 
	            WHERE term_id IN (
	                SELECT term_id
                    FROM ".$term_table." 
                    WHERE taxonomy = 'post_tag'
                    )
	            ");
    ?>

    <div class="wrap">
        <h2>Relacionar etiquetas</h2>
        <form method="post" action="options.php">
        <table class="form-table" style="width:80%">
            <thead>
            <tr>
                <th colspan="2">
                <center>
                Tag seleccionado
                <?php
                    echo '<select name="tag-seleccionado" id="tag-select">';
                    echo '<option disabled selected> -- Seleccione un tag -- </option>';
                    foreach ($all_tags as $tag)
                    {
                        echo '<option value="'.$tag->term_id.'">'.$tag->name.'</option>';
                    }
                    echo '</select>';

                ?>
                </center>
                </th>
            </tr>
            <tr>
                <th style="width:40%">
                Todos Tags
                </th>
                <th style="width:20%">
                Acciones
                </th>
                <th style="width:40%">
                Hijos
                </th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width:40%">
                    <?php
                        echo '<select multiple name="all-tags" id="all-tag-select" style="width:80%">';
                        foreach ($all_tags as $tag)
                        {
                            echo '<option value="'.$tag->term_id.'">'.$tag->name.'</option>';
                        }
                        echo '</select>';

                    ?>
                    </td>
                    <td style="width:20%">
                        <a class="button-secondary dashicons dashicons-arrow-right-alt" id="move-right" style="width:50px"></a><p/>
                        <a class="button-secondary dashicons dashicons-arrow-left-alt" id="move-left" style="width:50px"></a>
                    </td>
                    <td style="width:40%">
                    <?php
                        echo '<select multiple name="all-tags" id="child-tag-select" style="width:80%">';
                        /*foreach ($all_tags as $tag)
                        {
                            echo '<option value="'.$tag->term_id.'">'.$tag->name.'</option>';
                        }
                        echo '</select>';*/

                    ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?//php submit_button(); ?>
        
        

        </form>
    </div>
    <?
}

/*add_action( 'admin_enqueue_scripts', 'fcom_tags_enqueue' );
function fcom_tags_enqueue($hook) {
    if( 'index.php' != $hook ) {
	    // Only applies to dashboard panel
	    return;
    }
        
	wp_enqueue_script( 'fcom-ajax-script', plugins_url( '/js/fcom_settings.js', __FILE__ ));

	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'fcom-ajax-script', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 222 ) );
}*/

// Same handler function...
add_action( 'wp_ajax_fcom_tags_select', 'fcom_tags_select_callback' );
function fcom_tags_select_callback() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'fcom_tag_relations';
    $terms_table_name = $wpdb->prefix. 'terms';
    $term_table = $wpdb->prefix.'term_taxonomy';
	
	$tag_seleccionado = intval( $_POST['selected-tag'] );
	$tags = $wpdb->get_results("
	            SELECT term_id, name
	            FROM ".$terms_table_name." 
	            WHERE term_id IN (
	                SELECT term_id
                    FROM ".$term_table." 
                    WHERE taxonomy = 'post_tag'
                    )
                AND term_id NOT IN (
	                SELECT tag_id
                    FROM ".$table_name." 
                    WHERE tag_padre_id = ".$tag_seleccionado."
                    )
	            ");
    $child_tags = $wpdb->get_results("
	            SELECT term_id, name
	            FROM ".$terms_table_name." 
	            WHERE term_id IN (
	                SELECT term_id
                    FROM ".$term_table." 
                    WHERE taxonomy = 'post_tag'
                    )
                AND term_id IN (
	                SELECT tag_id
                    FROM ".$table_name." 
                    WHERE tag_padre_id = ".$tag_seleccionado."
                    )
	            ");
	$retorno = array('tags'=>$tags, 'child_tags'=>$child_tags);
    echo json_encode($retorno,true);
	wp_die();
}

add_action( 'wp_ajax_fcom_tags_save', 'fcom_tags_save_callback' );
function fcom_tags_save_callback() {
	global $wpdb;
	$whatever = intval( $_POST['whatever'] );
	$whatever += 10;
        echo $whatever;
	wp_die();
}

add_action( 'wp_ajax_fcom_tags_right', 'fcom_tags_right_callback' );
function fcom_tags_right_callback() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'fcom_tag_relations';
    $terms_table_name = $wpdb->prefix. 'terms';
    $term_table = $wpdb->prefix.'term_taxonomy';
   
	$tag_seleccionado = intval( $_POST['selected-tag'] );
	$hijos_raw =$_POST['move-tags'];
	
	$hijos = explode(',',$hijos_raw);
	foreach ($hijos as $hijo)
	{
	    $wpdb->insert($table_name, array(
                'tag_id' => $hijo, //replaced non-existing variables $lq_name, and $lq_descrip, with the ones we set to collect the data - $name and $description
                'tag_padre_id' => $tag_seleccionado,
            ));
    }
    
	$tags = $wpdb->get_results("
	            SELECT term_id, name
	            FROM ".$terms_table_name." 
	            WHERE term_id IN (
	                SELECT term_id
                    FROM ".$term_table." 
                    WHERE taxonomy = 'post_tag'
                    )
                AND term_id NOT IN (
	                SELECT tag_id
                    FROM ".$table_name." 
                    WHERE tag_padre_id = ".$tag_seleccionado."
                    )
	            ");
    $child_tags = $wpdb->get_results("
	            SELECT term_id, name
	            FROM ".$terms_table_name." 
	            WHERE term_id IN (
	                SELECT term_id
                    FROM ".$term_table." 
                    WHERE taxonomy = 'post_tag'
                    )
                AND term_id IN (
	                SELECT tag_id
                    FROM ".$table_name." 
                    WHERE tag_padre_id = ".$tag_seleccionado."
                    )
	            ");
	$retorno = array('tags'=>$tags, 'child_tags'=>$child_tags);
    echo json_encode($retorno,true);
	wp_die();
}

add_action( 'wp_ajax_fcom_tags_left', 'fcom_tags_left_callback' );
function fcom_tags_left_callback() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'fcom_tag_relations';
    $terms_table_name = $wpdb->prefix. 'terms';
    $term_table = $wpdb->prefix.'term_taxonomy';
   
	$tag_seleccionado = intval( $_POST['selected-tag'] );
	$hijos_raw =$_POST['move-tags'];
	$wpdb->query('DELETE FROM '.$table_name.' WHERE tag_id IN ('.$hijos_raw.') AND tag_padre_id = '.$tag_seleccionado);
	
	
	$tags = $wpdb->get_results("
	            SELECT term_id, name
	            FROM ".$terms_table_name." 
	            WHERE term_id IN (
	                SELECT term_id
                    FROM ".$term_table." 
                    WHERE taxonomy = 'post_tag'
                    )
                AND term_id NOT IN (
	                SELECT tag_id
                    FROM ".$table_name." 
                    WHERE tag_padre_id = ".$tag_seleccionado."
                    )
	            ");
    $child_tags = $wpdb->get_results("
	            SELECT term_id, name
	            FROM ".$terms_table_name." 
	            WHERE term_id IN (
	                SELECT term_id
                    FROM ".$term_table." 
                    WHERE taxonomy = 'post_tag'
                    )
                AND term_id IN (
	                SELECT tag_id
                    FROM ".$table_name." 
                    WHERE tag_padre_id = ".$tag_seleccionado."
                    )
	            ");
	$retorno = array('tags'=>$tags, 'child_tags'=>$child_tags);
    echo json_encode($retorno,true);
	wp_die();
}

function fcom_check_widget() {
    if( is_active_widget( '', '', 'fcom_tags_widget' ) ) {
        wp_enqueue_style('fcom-tags-controls-css', plugins_url('/css/fcom_controls.css', __FILE__));
        wp_enqueue_style('fcom-tags-mapa-css', plugins_url('/css/fcom_mapa.css', __FILE__));
        wp_enqueue_script('jquery-js', plugins_url('/js/jquery-2.1.4.min.js', __FILE__));
        wp_enqueue_script('d3-js', plugins_url('/js/d3.min.js', __FILE__));
    }
}

add_action( 'init', 'fcom_check_widget' );

