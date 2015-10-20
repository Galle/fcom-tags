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
include( plugin_dir_path( __FILE__ ) . 'fcom-tags-admin-page.php');

register_activation_hook( __FILE__, 'fcom_tags_install' );
function fcom_tags_install() {
	global $wpdb;
	global $fcom_tags_db_version;

	$table_name = $wpdb->prefix.'fcom_tag_relations';
	$terms_table_name = $wpdb->prefix.'terms';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
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

add_action( 'pre_delete_term', 'fcom_remove_tags',10,2 );
function fcom_remove_tags($term,$taxonomy) {
    global $wpdb;
    
    $table_name = $wpdb->prefix.'fcom_tag_relations';
    
    if ($taxonomy == 'post_tag')
    {
        $wpdb->query('DELETE FROM '.$table_name.' WHERE tag_id = '.$term.' OR tag_padre_id = '.$term);
    }
}

add_action( 'init', 'fcom_check_widget' );
function fcom_check_widget() {
    if( is_active_widget( '', '', 'fcom_tags_widget' ) ) {
        wp_enqueue_style('fcom-tags-controls-css', plugins_url('/css/fcom_controls.css', __FILE__));
        wp_enqueue_style('fcom-tags-mapa-css', plugins_url('/css/fcom_mapa.css', __FILE__));
        wp_enqueue_script('jquery-js', plugins_url('/js/jquery-2.1.4.min.js', __FILE__));
        wp_enqueue_script('d3-js', plugins_url('/js/d3.min.js', __FILE__));
    }
}
