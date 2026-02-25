<?php
/*
Plugin Name: PS Chat
Plugin URI: https://cp-psource.github.io/ps-chat/
Description: Bietet Dir einen voll ausgestatteten Chat-Bereich entweder in einem Beitrag, einer Seite, einem Widget oder in der unteren Ecke Ihrer Website. Unterstützt BuddyPress Group-Chats und private Chats zwischen angemeldeten Benutzern. KEINE EXTERNEN SERVER/DIENSTE! NEU: Media-Support für Link-Previews, Bilder und YouTube-Videos.
Author: PSOURCE
Version: 1.2.0
Author URI: https://github.com/cp-psource
Text Domain: psource-chat
Domain Path: /languages
*/

// PS Update Manager - Hinweis wenn nicht installiert
add_action( 'admin_notices', function() {
    // Prüfe ob Update Manager aktiv ist
    if ( ! function_exists( 'ps_register_product' ) && current_user_can( 'install_plugins' ) ) {
        $screen = get_current_screen();
        if ( $screen && in_array( $screen->id, array( 'plugins', 'plugins-network' ) ) ) {
            // Prüfe ob bereits installiert aber inaktiv
            $plugin_file = 'ps-update-manager/ps-update-manager.php';
            $all_plugins = get_plugins();
            $is_installed = isset( $all_plugins[ $plugin_file ] );
            
            echo '<div class="notice notice-warning is-dismissible"><p>';
            echo '<strong>PS Chat:</strong> ';
            
            if ( $is_installed ) {
                // Installiert aber inaktiv - Aktivierungs-Link
                $activate_url = wp_nonce_url(
                    admin_url( 'plugins.php?action=activate&plugin=' . urlencode( $plugin_file ) ),
                    'activate-plugin_' . $plugin_file
                );
                echo sprintf(
                    __( 'Aktiviere den <a href="%s">PS Update Manager</a> für automatische Updates von GitHub.', 'psource-chat' ),
                    esc_url( $activate_url )
                );
            } else {
                // Nicht installiert - Download-Link
                echo sprintf(
                    __( 'Installiere den <a href="%s" target="_blank">PS Update Manager</a> für automatische Updates aller PSource Plugins & Themes.', 'psource-chat' ),
                    'https://github.com/Power-Source/ps-update-manager/releases/latest'
                );
            }
            
            echo '</p></div>';
        }
    }
});


// Needs to be set BEFORE loading psource_chat_utilities.php!
//define('CHAT_DEBUG_LOG', 1);

include_once( dirname( __FILE__ ) . '/lib/psource_chat_utilities.php' );
include_once( dirname( __FILE__ ) . '/lib/psource_chat_wpadminbar.php' );

if ( ( ! defined( 'PSOURCE_CHAT_SHORTINIT' ) ) || ( PSOURCE_CHAT_SHORTINIT != true ) ) {
	include_once( dirname( __FILE__ ) . '/lib/psource_chat_widget.php' );
	include_once( dirname( __FILE__ ) . '/lib/psource_chat_buddypress.php' );
}

// Hauptklasse laden
include_once( dirname( __FILE__ ) . '/includes/class-psource-chat-avatar.php' );
include_once( dirname( __FILE__ ) . '/includes/class-psource-chat-emoji.php' );
include_once( dirname( __FILE__ ) . '/includes/class-psource-chat-media.php' );
include_once( dirname( __FILE__ ) . '/includes/class-psource-chat-upload.php' );
include_once( dirname( __FILE__ ) . '/includes/class-psource-chat-ajax.php' );
include_once( dirname( __FILE__ ) . '/includes/class-psource-chat.php' );



// Lets get things started
$psource_chat = new PSOURCE_Chat();
