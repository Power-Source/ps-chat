<?php
/*
Plugin Name: PS Chat
Plugin URI: https://cp-psource.github.io/ps-chat/
Description: Bietet Dir einen voll ausgestatteten Chat-Bereich entweder in einem Beitrag, einer Seite, einem Widget oder in der unteren Ecke Ihrer Website. Unterstützt BuddyPress Group-Chats und private Chats zwischen angemeldeten Benutzern. KEINE EXTERNEN SERVER/DIENSTE! NEU: Media-Support für Link-Previews, Bilder und YouTube-Videos.
Author: PSOURCE
Version: 1.0.0
Author URI: https://github.com/cp-psource
Text Domain: psource-chat
Domain Path: /languages
*/
// PS Update Manager Integration
add_action( 'plugins_loaded', function() {
    if ( function_exists( 'ps_register_product' ) ) {
        ps_register_product( array(
            'slug'          => 'ps-chat',
            'name'          => 'PS Chat',
            'version'       => '1.0.0',
            'type'          => 'plugin',
            'file'          => __FILE__,
            'github_repo'   => 'cp-psource/my-plugin', // Format: owner/repo
            'docs_url'      => 'https://docs.example.com',
            'support_url'   => 'https://github.com/cp-psource/my-plugin/issues',
            'changelog_url' => 'https://github.com/cp-psource/my-plugin/releases',
            'description'   => 'Eine kurze Beschreibung deines Plugins',
        ) );
    }
}, 5 );

// Optional: Admin Notice wenn Update Manager nicht installiert
add_action( 'admin_notices', function() {
    if ( ! function_exists( 'ps_register_product' ) && current_user_can( 'install_plugins' ) ) {
        $screen = get_current_screen();
        if ( $screen && in_array( $screen->id, array( 'plugins', 'plugins-network' ) ) ) {
            echo '<div class="notice notice-info"><p>';
            echo '<strong>My Plugin:</strong> ';
            echo 'Installiere den <a href="https://github.com/cp-psource/ps-update-manager">PS Update Manager</a> für automatische Updates.';
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
