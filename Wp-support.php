<?php
/*
Plugin Name: WP Support by Merka20
Plugin URI: https://merka20.com
Description: This plugin adds support for WordPress with a developer who will help you at all times.
Author: Oscar Domingo
Version: 1.0.0
Author URI: https://merka20.com

Text Domain: MK20-WPSupport
Domain Path: /languages

*/
if (!defined('ABSPATH')) exit; // Salir si acceden directamente


// This just echoes the chosen line, we'll position it later.
function MK20_mensaje()
{
	$Nombre = "Merka20";

	$url = get_site_url() . "/" . "wp-admin/admin.php?page=asistencia-tecnica/admin/listado_tickets.php";
	$Contacto = "https://merka20.com/#contacto"; //$url;

	printf(
		'<p id="dolly"><span>%s</span><span dir="ltr">%s <a href="%s">%s</a></span></p>',
		__('If you need any kind of technical assistance for the web: ', 'MK20-WPSupport'),
		__('Get in contact with ', 'MK20-WPSupport'),
		$Contacto,
		$Nombre
	);
}

// Now we set that function up to execute when the admin_notices action is called.
add_action('admin_notices', 'MK20_mensaje');

// We need some CSS to position the paragraph.
function MK20_css()
{
	echo "
	<style type='text/css'>
	#dolly {
		float: right;
		padding: 5px 10px;
		margin: 0;
		font-size: 12px;
		line-height: 1.6666;
	}
	.rtl #dolly {
		float: left;
	}
	.block-editor-page #dolly {
		display: none;
	}
	@media screen and (max-width: 782px) {
		#dolly,
		.rtl #dolly {
			float: none;
			padding-left: 0;
			padding-right: 0;
		}
	}
	</style>
	";
}

add_action('admin_head', 'MK20_css');

function activate()
{

	//Create the tables of Data Base

	global $wpdb;

	$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}spo_list_tickets(
		`Ticket_Id` INT NOT NULL AUTO_INCREMENT ,
		`Titulo` VARCHAR(200) NULL ,
		`Pregunta` VARCHAR(200) NULL ,
		`Usuario` VARCHAR(100) NULL ,
		`User_email` VARCHAR(200) NULL ,
		`Tipo` VARCHAR(100) NULL ,
		`Estado` BOOLEAN NOT NULL,
		`File` VARCHAR(200) NULL,
		`Fecha`  TIMESTAMP NOT NULL,
		`Fecha_Publicado` date NOT NULL,
		`Fecha_Resuelto` date NULL,
		`Hora` time NOT NULL,
		`New`  BOOLEAN NOT NULL,
		`Res`  BOOLEAN NOT NULL,
		`Code` VARCHAR (100) NULL,
		PRIMARY KEY (`Ticket_Id`)
		);";

	$wpdb->query($sql);


	$sql2 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}spo_answer(
		`Respuesta_Id` INT NOT NULL AUTO_INCREMENT ,
		`Ticket_Id` INT NULL ,
		`Respuesta` VARCHAR(200) NULL ,
		`Usuario` VARCHAR(100) NULL ,		
		`Fecha` date NOT NULL,
		`Hora` time NOT NULL,
		`New`  BOOLEAN NOT NULL,
		`Res`  BOOLEAN NOT NULL,
		PRIMARY KEY (`Respuesta_Id`)
		);";

	$wpdb->query($sql2);

	//Crear el role de desarrollo rol editor(manage_categories)

	add_role(
		'developer',
		__('Developer', 'MK20-WPSupport'),
		array(
			'read'            => true, // Allows a user to read
			'create_posts'      => true, // Allows user to create new posts
			'edit_posts'        => true, // Allows user to edit their own posts
			'edit_others_posts' => true, // Allows user to edit others posts too
			'publish_posts' => true, // Allows the user to publish posts
			'manage_categories' => true, // Allows user to manage post categories
		)
	);

	//Create a new User for developer

	$user = 'User'; // new user
	$pass = 'Pass'; //password
	$email = 'email'; //Email
	if (!username_exists($user)  && !email_exists($email)) {
		$user_id = wp_create_user($user, $pass, $email);
		$user = new WP_User($user_id);
		$user->set_role('developer');
	}
}

function deactivate()
{ }

register_activation_hook(__FILE__, 'activate');
register_deactivation_hook(__FILE__, 'deactivate');


add_action('admin_menu', 'MK20_add_menu');

if (!function_exists('MK20_add_menu')) {
	function MK20_add_menu()
	{
		global $wpdb;

		$tabla_Pregunta = "{$wpdb->prefix}spo_list_tickets";
		$tabla_Respuesta = "{$wpdb->prefix}spo_answer";

		global $current_user, $user_login;
		wp_get_current_user();

		$user_logged = $current_user->user_login;
		$user_email = $current_user->user_email;

		if ($user_logged == 'Merka20') {

			$sqlbc = "SELECT * FROM $tabla_Pregunta WHERE `New`='1'";
			$resultbc = $wpdb->get_results($sqlbc); //$pdo sería el objeto conexión
			$notification_count = count($resultbc);
		} else {

			$sqlbc = "SELECT * FROM $tabla_Respuesta WHERE `New`='1'";
			$resultbc = $wpdb->get_results($sqlbc); //$pdo sería el objeto conexión
			$notification_count = count($resultbc);
		}

		add_menu_page(

			__('List of Support Tickets', 'MK20-WPSupport'),
			$notification_count ? sprintf(__('Support', 'MK20-WPSupport') . " <span class='awaiting-mod'>%d</span>", $notification_count) : __('Support', 'MK20-WPSupport'),
			'manage_categories', //capability manage_options		
			plugin_dir_path(__FILE__) . 'admin/listado_tickets.php', //slug
			null,
			plugin_dir_url(__FILE__) . 'admin/img/icon.png',
			'1'
		);

		add_submenu_page(NULL, '__("Open", "MK20-WPSupport")', '__("Open Tickets","MK20-WPSupport")', 'manage_categories', 'abiertos', 'MK20_abiertos');
		add_submenu_page(NULL, '__("Urgent", "MK20-WPSupport")', '__("Urgent Tickets","MK20-WPSupport")', 'manage_categories', 'urgent', 'MK20_urgent');
		add_submenu_page(NULL, '__("Resolved", "MK20-WPSupport")', '__("Resolved Tickets","MK20-WPSupport")', 'manage_categories', 'resueltos', 'MK20_resueltos');
	}
}

if (!function_exists('MK20_abiertos')) {
	function MK20_abiertos()
	{

		require 'admin/abiertos.php';
	}
}
if (!function_exists('MK20_resueltos')) {
	function MK20_resueltos()
	{
		require 'admin/resueltos.php';
	}
}
if (!function_exists('MK20_urgent')) {
	function MK20_urgent()
	{
		require 'admin/urgent.php';
	}
}

//encolar bootstrap js

if (!function_exists('MK20_encolar_bootstrap_js')) {

	function MK20_encolar_bootstrap_js()
	{

		wp_register_script('bootstrapjs', plugins_url('admin/bootstrap/js/bootstrap.min.js', __FILE__), array('jquery'));
		if (isset($_GET['page']) && $_GET['page'] == 'abiertos') {
			wp_enqueue_script('bootstrapjs');
		}
		if (isset($_GET['page']) && $_GET['page'] == 'resueltos') {
			wp_enqueue_script('bootstrapjs');
		}
		if (isset($_GET['page']) && $_GET['page'] == 'Wp-support/admin/listado_tickets.php') {
			wp_enqueue_script('bootstrapjs');
		}
		if (isset($_GET['page']) && $_GET['page'] == 'urgent') {
			wp_enqueue_script('bootstrapjs');
		}
	}
	add_action('admin_enqueue_scripts', 'MK20_encolar_bootstrap_js');
}

//encolar bootstrap css y css propio

if (!function_exists('MK20_encolar_bootstrap_css')) {

	function MK20_encolar_bootstrap_css()
	{

		wp_register_style('bootstrapcss', plugins_url('admin/bootstrap/css/bootstrap.min.css', __FILE__));
		if (isset($_GET['page']) && $_GET['page'] == 'abiertos') {
			wp_enqueue_style('bootstrapcss');
		}
		if (isset($_GET['page']) && $_GET['page'] == 'resueltos') {
			wp_enqueue_style('bootstrapcss');
		}
		if (isset($_GET['page']) && $_GET['page'] == 'Wp-support/admin/listado_tickets.php') {
			wp_enqueue_style('bootstrapcss');
		}
		if (isset($_GET['page']) && $_GET['page'] == 'urgent') {
			wp_enqueue_style('bootstrapcss');
		}
	}
	add_action('admin_enqueue_scripts', 'MK20_encolar_bootstrap_css');
}

if (!function_exists('MK20_encolar_estilos_propios')) {

	function MK20_encolar_estilos_propios()
	{

		wp_register_style('csspropio', plugins_url('admin/css/style.css', __FILE__), [], filemtime(plugin_dir_path(dirname(__FILE__)) . 'Wp-support/admin/css/style.css'));
		if (isset($_GET['page']) && $_GET['page'] == 'abiertos') {
			wp_enqueue_style('csspropio');
		}
		if (isset($_GET['page']) && $_GET['page'] == 'resueltos') {
			wp_enqueue_style('csspropio');
		}
		if (isset($_GET['page']) && $_GET['page'] == 'Wp-support/admin/listado_tickets.php') {
			wp_enqueue_style('csspropio');
		}
		if (isset($_GET['page']) && $_GET['page'] == 'urgent') {
			wp_enqueue_style('csspropio');
		}
	}

	add_action('admin_enqueue_scripts', 'MK20_encolar_estilos_propios');
}

//encolar js plugin

if (!function_exists('MK20_encolar_js')) {

	function MK20_encolar_js()
	{
		wp_enqueue_media();

		wp_register_script('jspropio', plugins_url('admin/js/list_tickets_new.js', __FILE__), array('jquery', 'wp-i18n'), NULL, true);

		if (isset($_GET['page']) && $_GET['page'] == 'abiertos') {
			wp_enqueue_script('jspropio');
		}
		if (isset($_GET['page']) && $_GET['page'] == 'resueltos') {
			wp_enqueue_script('jspropio');
		}
		if (isset($_GET['page']) && $_GET['page'] == 'Wp-support/admin/listado_tickets.php') {
			wp_enqueue_script('jspropio');
		}
		if (isset($_GET['page']) && $_GET['page'] == 'urgent') {
			wp_enqueue_script('jspropio');
		}

		wp_localize_script('jspropio', 'solicitud', [
			'url' => admin_url('admin-ajax.php'),
			'seguridad' => wp_create_nonce('seg')
		]);
		wp_localize_script('jspropio', 'resolver', [
			'url' => admin_url('admin-ajax.php'),
			'seguridad' => wp_create_nonce('sec')
		]);
		wp_localize_script('jspropio', 'bdelete', [
			'url' => admin_url('admin-ajax.php'),
			'seguridad' => wp_create_nonce('segd')
		]);

		// Tell WP to load translations for our JS.
		wp_set_script_translations('jspropio', 'MK20-WPSupport', plugin_dir_path(__FILE__) . 'languages/');
	}
	add_action('admin_enqueue_scripts', 'MK20_encolar_js');
}

//Load texdomain

if (!function_exists('MK20_load_plugin_textdomain')) {

	function MK20_load_plugin_textdomain()
	{
		load_plugin_textdomain('MK20-WPSupport', FALSE, plugin_basename(dirname(__FILE__)) . '/languages/');
	}
	add_action('plugins_loaded', 'Mk20_load_plugin_textdomain');
}

//Delete tickets

if (!function_exists('MK20_eliminar_tickets')) {

	function MK20_eliminar_tickets()
	{
		$nonce = $_POST['nonce'];
		if (!wp_verify_nonce($nonce, 'seg')) {
			die('__("You are not allowed to perform this action","MK20-WPSupport")');
		}
		$id = $_POST['id'];
		if (isset($id)) {
			global $wpdb;
			$tabla_Pregunta = "{$wpdb->prefix}spo_list_tickets";
			$wpdb->delete($tabla_Pregunta, array('Ticket_Id' => $id));
			return true;
		}
	}

	add_action('wp_ajax_eliminar', 'MK20_eliminar_tickets');
}

//Resolved tickets$is

if (!function_exists('MK20_resolver_tickets')) {

	function MK20_resolver_tickets()
	{
		$nonce = $_POST['nonce'];
		if (!wp_verify_nonce($nonce, 'sec')) {
			die('__("You are not allowed to perform this action","MK20-WPSupport")');
		}

		$id = $_POST['id'];
		$Titulor = $_POST['titulor'];
		$User_email = $_POST['useremailr'];

		if (isset($id)) {

			global $wpdb;
			$tabla_Pregunta = "{$wpdb->prefix}spo_list_tickets";
			$fecha = date_create(date('Y-m-d'));
			$fecha_limite = date_add($fecha, date_interval_create_from_date_string("15 days"));
			$datos = [
				'Estado' => '1',
				'Fecha_Resuelto' => $fecha_limite->format('Y-m-d'),
			];
			$resolver = $wpdb->update($tabla_Pregunta, $datos, array('Ticket_Id' => $id));

			//return true;

			if ($resolver) {
				require_once 'admin/email.php';
				wp_mail("$User_email", "Soporte Web", "El Desarrollador ha dado por resuelto el ticket de soporte con el título:<strong>" . $Titulor . "</strong><br/><br/>Muchas gracias por confiar en Merka20.");
			}
		}
	}

	add_action('wp_ajax_resolved', 'MK20_resolver_tickets');
}

//Delete respondtickets

if (!function_exists('MK20_eliminar_rtickets')) {

	function MK20_eliminar_rtickets()
	{
		echo 'entra en eliminar rtickets';
		$nonce = $_POST['nonce'];
		if (!wp_verify_nonce($nonce, 'segd')) {
			die('__("You are not allowed to perform this action","MK20-WPSupport")');
		}
		$id = $_POST['id'];
		$idr = $_POST['idr'];
		if (isset($id)) {
			global $wpdb;
			$tabla_Respuesta = "{$wpdb->prefix}spo_answer";
			$wpdb->delete($tabla_Respuesta, array('Respuesta_Id' => $idr));
			return true;
		}
	}

	add_action('wp_ajax_deleter', 'MK20_eliminar_rtickets');
}
