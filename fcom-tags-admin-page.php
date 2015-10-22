<?php
/*
    Menu para wp-admin
*/
add_action('admin_menu', 'fcom_tags_plugin_menu');
function fcom_tags_plugin_menu() {
	add_menu_page('Fcom Tag Settings', 'Fcom Tag Settings', 'manage_categories', 'fcom-tags-settings.php', 'fcom_tags_settings_page', 'dashicons-tag');
}

function fcom_tags_settings_page() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'fcom_tag_relations';
    $terms_table_name = $wpdb->prefix. 'terms';
    $term_table = $wpdb->prefix.'term_taxonomy';
    
    wp_enqueue_script('jquery-js', plugins_url('/js/jquery-2.1.4.min.js', __FILE__));
    wp_enqueue_script( 'fcom-ajax-script', plugins_url( '/js/fcom_settings.js', __FILE__ ));
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
                        echo '</select>';
                    ?>
                    </td>
                </tr>
            </tbody>
        </table>
        </form>
    </div>
    <?php
}

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
                AND term_id != ".$tag_seleccionado."
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
                'tag_id' => $hijo,
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
