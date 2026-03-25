<?php
/*
Plugin Name: PS Chat
Plugin URI: https://psource.eimen.net/wiki/ps-chat-dokumentation/
Description: Bietet Dir einen voll ausgestatteten Chat-Bereich entweder in einem Beitrag, einer Seite, einem Widget oder in der unteren Ecke Deiner Webseite. Unterstützt PS Community Group-Chats und private Chats zwischen angemeldeten Benutzern. KEINE EXTERNEN SERVER/DIENSTE!
Author: PSOURCE
Version: 1.1.3
Author URI: https://psource.eimen.net/
Text Domain: psource-chat
Domain Path: /languages
*/


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
