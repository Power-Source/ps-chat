<?php
defined( 'ABSPATH' ) || exit;

class PSource_Chat_Support_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'ps_chat_support', __( 'PS Chat: Privater Support', 'psource-chat' ), array(
			'description' => __( 'Private Gast-Unterhaltung mit dem in PS Support zuständigen Bearbeiter oder Team.', 'psource-chat' ),
		) );
	}

	public function widget( $args, $instance ) {
		$content = PSource_Chat_Support::shortcode( array(
			'position' => isset( $instance['position'] ) ? $instance['position'] : '',
		) );
		if ( $content ) {
			echo $args['before_widget'] . $content . $args['after_widget'];
		}
	}

	public function form( $instance ) {
		$position = isset( $instance['position'] ) ? $instance['position'] : '';
		$positions = array(
			'' => __( 'Einstellung des Supportchats verwenden', 'psource-chat' ),
			'bottom-right' => __( 'Unten rechts', 'psource-chat' ),
			'bottom-left' => __( 'Unten links', 'psource-chat' ),
			'top-right' => __( 'Oben rechts', 'psource-chat' ),
			'top-left' => __( 'Oben links', 'psource-chat' ),
		);
		echo '<p>' . esc_html__( 'Konfiguration unter Chat → Supportchat. Pro Seite wird ein Supportchat angezeigt; weitere Instanzen und das schwebende Icon werden auf dieser Seite unterdrückt.', 'psource-chat' ) . '</p>';
		echo '<p><label for="' . esc_attr( $this->get_field_id( 'position' ) ) . '">' . esc_html__( 'Position des Chatbuttons', 'psource-chat' ) . '</label><select class="widefat" id="' . esc_attr( $this->get_field_id( 'position' ) ) . '" name="' . esc_attr( $this->get_field_name( 'position' ) ) . '">';
		foreach ( $positions as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '" ' . selected( $position, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select></p>';
	}

	public function update( $new_instance, $old_instance ) {
		$positions = array( '', 'bottom-right', 'bottom-left', 'top-right', 'top-left' );
		$position = isset( $new_instance['position'] ) ? sanitize_key( $new_instance['position'] ) : '';
		return array( 'position' => in_array( $position, $positions, true ) ? $position : '' );
	}
}
