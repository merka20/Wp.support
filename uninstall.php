<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 * 
 *
 * @link       https://merka20.com
 * @since      1.0.0 
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}
global $wpdb;

$tabla_Pregunta = $wpdb->prefix . 'spo_list_tickets';
$tabla_Respuesta = $wpdb->prefix . 'spo_answer';

$wpdb->query( "DROP TABLE IF EXISTS $tabla_Pregunta" );
$wpdb->query( "DROP TABLE IF EXISTS $tabla_Respuesta" );



//Eliminar rol y usuario

require_once ABSPATH . 'wp-admin/includes/user.php';

$username = 'Merka20';

if (username_exists($username)) {
  $user = get_user_by('login', $username);
  $success = wp_delete_user($user->ID);
  //wp_die('wp delete gave: <pre>"'.print_r($success).'"</pre>');
}

//Eliminar rol

if( get_role('developer') ) {
    remove_role( 'developer'); 
}
