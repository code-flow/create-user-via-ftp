<?php
/*
Plugin Name: FTP-User (Löschen Sie diese Datei nach Benutzung)
Description: Laden Sie diese Datei in /wp-content/mu-plugins/ hoch. Löschen Sie sie, nachdem Sie sich eingeloggt haben. Mehr erfahren Sie auf der Plugin-Seite.
Author: Florian Simeth
Author URI: https://florian-simeth.de?ref=pm_ftp_user_plugin
Plugin URI: https://postmeta.blog/tipps-und-tricks/einen-wordpress-benutzer-ueber-ftp-anlegen/?ref=pm_ftp_user_plugin
*/

/*
 * Passen Sie hier die Benutzerdaten an.
 */
define( 'PM_FTP_USER', 'mike' );
define( 'PM_FTP_PASS', '*********' );
define( 'PM_FTP_MAIL', 'mike@maxmuster-adresse.de' );

/**
 * ************************* Editieren Sie nichts nach dieser Zeile *************************
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

define( 'PM_FILE_NAME', basename( __FILE__ ) );


add_action( 'login_head', 'pm_create_user' );

/**
 * Legt den Benutzer an.
 */
function pm_create_user() {
	if ( ! username_exists( PM_FTP_USER ) && ! email_exists( PM_FTP_MAIL ) ) {
		$user_id = wp_create_user( PM_FTP_USER, PM_FTP_PASS, PM_FTP_MAIL );
		$user    = new WP_User( $user_id );

		$user->set_role( 'administrator' );
	}
}


add_filter( 'plugin_action_links_' . PM_FILE_NAME, 'pm_action_links' );

/**
 * Fügt einen Löschen-Link ein.
 *
 * @param array $links
 *
 * @return array
 */
function pm_action_links( $links ) {
	$url             = admin_url( 'plugins.php?action=pm_delete_ftp_user' );
	$url             = add_query_arg( array( '_pmnonce' => wp_create_nonce( 'pm_delete_ftp_user' ) ), $url );
	$links['delete'] = sprintf(
		'<a href="%s">Datei löschen</a>',
		$url
	);

	return $links;
}


add_action( 'admin_action_pm_delete_ftp_user', 'pm_delete_ftp_user_file' );

/**
 * Löscht die Datei.
 */
function pm_delete_ftp_user_file() {
	check_admin_referer( 'pm_delete_ftp_user', '_pmnonce' );

	#wp_delete_file( __FILE__ );
	$url = add_query_arg(
		array(
			'plugin_status' => 'mustuse',
			'pm_status'     => 'deleted',
		),
		admin_url( 'plugins.php?plugin_status=mustuse' )
	);
	wp_redirect( $url );
}


add_action( 'load-plugins.php', 'pm_add_delete_notice' );

/**
 * Fügt eine Warnung hinzu.
 */
function pm_add_delete_notice() {
	$show_notice = filter_input( INPUT_GET, 'pm_status' ) === 'deleted';

	if ( $show_notice ) {
		add_action( 'admin_notices', 'pm_delete_notice' );
	}
}


/**
 * Zeigt die Warnung an.
 */
function pm_delete_notice() {

	$file_path = str_replace( ABSPATH, '', __FILE__ );
	?>
    <div class="notice notice-error is-dismissible">
        <p><strong>Die Datei konnte nicht gelöscht werden. Bitte löschen Sie sie manuell. Sie finden sie
                unter <?php echo esc_html( $file_path ); ?></strong></p>
    </div>
	<?php
}
