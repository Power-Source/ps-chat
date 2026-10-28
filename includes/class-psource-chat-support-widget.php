<?php
defined( 'ABSPATH' ) || exit;

class PSource_Chat_Support_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'ps_chat_support', __( 'PS Chat: Privater Support', 'psource-chat' ), array(
			'description' => __( 'Private Gast-Unterhaltung mit dem in PS Support zuständigen Bearbeiter oder Team.', 'psource-chat' ),
		) );
	}

	public function widget( $args, $instance ) {
		$content = PSource_Chat_Support::shortcode();
		if ( $content ) {
			echo $args['before_widget'] . $content . $args['after_widget'];
		}
	}

	public function form( $instance ) {
		echo '<p>' . esc_html__( 'Konfiguration unter Chat → Supportchat. Pro Seite wird ein Supportchat angezeigt; weitere Instanzen und das schwebende Icon werden auf dieser Seite unterdrückt.', 'psource-chat' ) . '</p>';
	}
}
