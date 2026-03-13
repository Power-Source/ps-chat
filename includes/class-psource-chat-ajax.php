<?php
/**
 * PSource Chat Modern AJAX Handler
 * 
 * Modern, secure and performant AJAX endpoint for chat operations
 * Uses WordPress REST API and optimized admin-ajax.php handlers
 * 
 * @package PSource_Chat
 * @subpackage AJAX
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PSource_Chat_AJAX {

	/**
	 * Cache for frequently accessed data
	 * @var array
	 */
	private static $cache = array();

	/**
	 * Initialize AJAX handlers
	 */
	public static function init() {
		// Modern REST API endpoints (preferred)
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
		
		// Legacy admin-ajax.php handlers (fallback)
		add_action( 'wp_ajax_psource_chat_action', array( __CLASS__, 'handle_ajax_request' ) );
		add_action( 'wp_ajax_nopriv_psource_chat_action', array( __CLASS__, 'handle_ajax_request' ) );
		
		// Optimized message polling (lightweight)
		add_action( 'wp_ajax_psource_chat_poll', array( __CLASS__, 'handle_poll_request' ) );
		add_action( 'wp_ajax_nopriv_psource_chat_poll', array( __CLASS__, 'handle_poll_request' ) );
	}

	/**
	 * Register REST API routes
	 */
	public static function register_rest_routes() {
		register_rest_route( 'psource-chat/v1', '/messages', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'rest_get_messages' ),
			'permission_callback' => array( __CLASS__, 'rest_permission_check' ),
			'args'                => array(
				'session_id' => array(
					'required'          => true,
					'validate_callback' => function( $param ) {
						return is_string( $param ) && ! empty( $param );
					}
				),
				'last_id' => array(
					'default'           => 0,
					'validate_callback' => function( $param ) {
						return is_numeric( $param );
					}
				)
			)
		) );

		register_rest_route( 'psource-chat/v1', '/messages', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'rest_send_message' ),
			'permission_callback' => array( __CLASS__, 'rest_permission_check' ),
			'args'                => array(
				'session_id' => array(
					'required'          => true,
					'validate_callback' => function( $param ) {
						return is_string( $param ) && ! empty( $param );
					}
				),
				'message' => array(
					'required'          => true,
					'validate_callback' => function( $param ) {
						return is_string( $param ) && ! empty( trim( $param ) );
					},
					'sanitize_callback' => 'sanitize_text_field'
				)
			)
		) );

		register_rest_route( 'psource-chat/v1', '/users', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'rest_get_users' ),
			'permission_callback' => array( __CLASS__, 'rest_permission_check' ),
			'args'                => array(
				'session_id' => array(
					'required'          => true,
					'validate_callback' => function( $param ) {
						return is_string( $param ) && ! empty( $param );
					}
				)
			)
		) );
	}

	/**
	 * REST API permission check
	 */
	public static function rest_permission_check( $request ) {
		if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
			return new WP_Error( 'forbidden', __( 'Authentication required.', 'psource-chat' ), array( 'status' => 401 ) );
		}

		$rest_nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $rest_nonce ) || ! wp_verify_nonce( $rest_nonce, 'wp_rest' ) ) {
			return new WP_Error( 'invalid_nonce', __( 'Invalid REST nonce.', 'psource-chat' ), array( 'status' => 403 ) );
		}

		$session_id = sanitize_text_field( $request->get_param( 'session_id' ) );
		if ( empty( $session_id ) || ! self::validate_session( $session_id ) ) {
			return new WP_Error( 'invalid_session', __( 'Invalid chat session.', 'psource-chat' ), array( 'status' => 400 ) );
		}

		return true;
	}

	/**
	 * REST API: Get messages
	 */
	public static function rest_get_messages( $request ) {
		$session_id = $request->get_param( 'session_id' );
		$last_id = intval( $request->get_param( 'last_id' ) );

		global $psource_chat;
		
		// Use optimized message retrieval
		$messages = self::get_messages_optimized( $session_id, $last_id );
		
		if ( is_wp_error( $messages ) ) {
			return $messages;
		}

		return rest_ensure_response( array(
			'success' => true,
			'data'    => $messages,
			'meta'    => array(
				'timestamp' => time(),
				'count'     => count( $messages )
			)
		) );
	}

	/**
	 * REST API: Send message
	 */
	public static function rest_send_message( $request ) {
		$session_id = $request->get_param( 'session_id' );
		$message = $request->get_param( 'message' );

		global $psource_chat;

		// Validate session
		if ( ! self::validate_session( $session_id ) ) {
			return new WP_Error( 'invalid_session', 'Invalid chat session', array( 'status' => 400 ) );
		}

		// Send message using existing chat logic
		$result = self::send_message_compat( $session_id, $message );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( array(
			'success' => true,
			'data'    => $result,
			'meta'    => array(
				'timestamp' => time()
			)
		) );
	}

	/**
	 * REST API: Get users
	 */
	public static function rest_get_users( $request ) {
		$session_id = $request->get_param( 'session_id' );

		global $psource_chat;
		
		$users = self::get_users_optimized( $session_id );
		
		if ( is_wp_error( $users ) ) {
			return $users;
		}

		return rest_ensure_response( array(
			'success' => true,
			'data'    => $users,
			'meta'    => array(
				'timestamp' => time(),
				'count'     => count( $users )
			)
		) );
	}

	/**
	 * Handle legacy admin-ajax.php requests
	 */
	public static function handle_ajax_request() {
		// Verify nonce for security
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_REQUEST['nonce'] ), 'psource_chat_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$action = sanitize_text_field( $_REQUEST['chat_action'] ?? '' );

		switch ( $action ) {
			case 'get_messages':
				self::ajax_get_messages();
				break;
			case 'send_message':
				self::ajax_send_message();
				break;
			case 'get_users':
				self::ajax_get_users();
				break;
			default:
				wp_send_json_error( 'Invalid action' );
		}

		wp_die();
	}

	/**
	 * Handle optimized polling requests (lightweight)
	 */
	public static function handle_poll_request() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_REQUEST['nonce'] ), 'psource_chat_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		// Ultra-lightweight polling for message updates
		$session_id = sanitize_text_field( $_REQUEST['session_id'] ?? '' );
		$last_check = intval( $_REQUEST['last_check'] ?? 0 );

		if ( empty( $session_id ) ) {
			wp_send_json_error( 'Missing session ID' );
		}

		if ( ! self::validate_session( $session_id ) ) {
			wp_send_json_error( 'Invalid session ID' );
		}

		if ( ! self::check_poll_rate_limit( $session_id ) ) {
			wp_send_json_error( 'Rate limit exceeded' );
		}

		// Quick check for new activity
		$has_updates = self::has_new_activity( $session_id, $last_check );

		wp_send_json_success( array(
			'has_updates' => $has_updates,
			'timestamp'   => time()
		) );
	}

	/**
	 * Optimized message retrieval
	 */
	private static function get_messages_optimized( $session_id, $last_id = 0 ) {
		global $wpdb, $psource_chat;

		$cache_key = 'chat_messages_' . md5( $session_id . '|' . $last_id );
		$cached    = self::get_cached( $cache_key );
		if ( null !== $cached ) {
			return $cached;
		}

		// Get messages from database with optimized query
		$sql = $wpdb->prepare( "
			SELECT cm.*, u.display_name
			FROM {$psource_chat->tablename_sessions_messages} cm 
			LEFT JOIN {$wpdb->users} u ON cm.from_user_id = u.ID 
			WHERE cm.chat_session_id = %s 
			AND cm.id > %d 
			ORDER BY cm.message_date_stamp ASC 
			LIMIT 50
		", $session_id, $last_id );

		$messages = $wpdb->get_results( $sql );

		if ( $wpdb->last_error ) {
			return new WP_Error( 'database_error', $wpdb->last_error );
		}

		// Process messages (add avatars, format, etc.)
		foreach ( $messages as &$message ) {
			$message->avatar = PSource_Chat_Avatar::get_avatar( $message->from_user_id, 32, false );
			$message->message_text = wp_kses_post( $message->message_text );
			$message->formatted_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $message->message_date_stamp ) );
		}

		self::set_cached( $cache_key, $messages, 10 );

		return $messages;
	}

	/**
	 * Optimized user retrieval
	 */
	private static function get_users_optimized( $session_id ) {
		global $wpdb, $psource_chat;

		$cache_key = 'chat_users_' . md5( $session_id );
		$cached    = self::get_cached( $cache_key );
		if ( null !== $cached ) {
			return $cached;
		}

		// Get active users for session
		$sql = $wpdb->prepare( "
			SELECT DISTINCT cu.user_id, u.display_name, cu.user_last_seen
			FROM {$psource_chat->tablename_sessions_users} cu 
			LEFT JOIN {$wpdb->users} u ON cu.user_id = u.ID 
			WHERE cu.chat_session_id = %s 
			AND cu.user_last_seen > %s
			ORDER BY u.display_name ASC
		", $session_id, date( 'Y-m-d H:i:s', time() - 300 ) ); // Active within last 5 minutes

		$users = $wpdb->get_results( $sql );

		if ( $wpdb->last_error ) {
			return new WP_Error( 'database_error', $wpdb->last_error );
		}

		// Add avatars to users
		foreach ( $users as &$user ) {
			$user->avatar = PSource_Chat_Avatar::get_avatar( $user->user_id, 32, false );
		}

		self::set_cached( $cache_key, $users, 30 );

		return $users;
	}

	/**
	 * Quick check for new activity (ultra-lightweight)
	 */
	private static function has_new_activity( $session_id, $last_check ) {
		global $wpdb, $psource_chat;

		$last_check_mysql = gmdate( 'Y-m-d H:i:s', max( 0, $last_check ) );

		// Quick timestamp check - very efficient
		$sql = $wpdb->prepare( "
			SELECT COUNT(*) 
			FROM {$psource_chat->tablename_sessions_messages} 
			WHERE chat_session_id = %s 
			AND message_date_stamp > %s
		", $session_id, $last_check_mysql );

		$count = $wpdb->get_var( $sql );

		return $count > 0;
	}

	/**
	 * Validate session
	 */
	private static function validate_session( $session_id ) {
		global $wpdb, $psource_chat;

		if ( empty( $session_id ) ) {
			return false;
		}

		if ( ! empty( $psource_chat->chat_sessions ) && isset( $psource_chat->chat_sessions[ $session_id ] ) ) {
			return true;
		}

		if ( ! isset( $psource_chat->tablename_sessions ) || empty( $psource_chat->tablename_sessions ) ) {
			return false;
		}

		$sql = $wpdb->prepare( "
			SELECT COUNT(*) 
			FROM {$psource_chat->tablename_sessions} 
			WHERE session_key = %s 
			AND session_status = 'active'
		", $session_id );

		$count = $wpdb->get_var( $sql );

		return $count > 0;
	}

	/**
	 * Legacy AJAX handlers for backward compatibility
	 */
	private static function ajax_get_messages() {
		$session_id = sanitize_text_field( $_REQUEST['session_id'] ?? '' );
		$last_id = intval( $_REQUEST['last_id'] ?? 0 );

		$messages = self::get_messages_optimized( $session_id, $last_id );

		if ( is_wp_error( $messages ) ) {
			wp_send_json_error( $messages->get_error_message() );
		}

		wp_send_json_success( $messages );
	}

	private static function ajax_send_message() {
		$session_id = sanitize_text_field( $_REQUEST['session_id'] ?? '' );
		$message = sanitize_text_field( $_REQUEST['message'] ?? '' );

		if ( ! self::validate_session( $session_id ) ) {
			wp_send_json_error( 'Invalid session' );
		}

		$result = self::send_message_compat( $session_id, $message );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( $result );
	}

	private static function ajax_get_users() {
		$session_id = sanitize_text_field( $_REQUEST['session_id'] ?? '' );

		$users = self::get_users_optimized( $session_id );

		if ( is_wp_error( $users ) ) {
			wp_send_json_error( $users->get_error_message() );
		}

		wp_send_json_success( $users );
	}

	/**
	 * Clear cache callback
	 */
	public static function clear_cache_item( $cache_key ) {
		unset( self::$cache[ $cache_key ] );
		delete_transient( 'psource_chat_' . md5( $cache_key ) );
	}

	/**
	 * Return cached value or null when missing.
	 */
	private static function get_cached( $cache_key ) {
		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		$cached = get_transient( 'psource_chat_' . md5( $cache_key ) );
		if ( false === $cached ) {
			return null;
		}

		self::$cache[ $cache_key ] = $cached;

		return $cached;
	}

	/**
	 * Cache value for short-lived polling windows.
	 */
	private static function set_cached( $cache_key, $value, $ttl ) {
		self::$cache[ $cache_key ] = $value;
		set_transient( 'psource_chat_' . md5( $cache_key ), $value, absint( $ttl ) );
	}

	/**
	 * Send message using available method in the legacy chat core.
	 */
	private static function send_message_compat( $session_id, $message ) {
		global $psource_chat;

		if ( method_exists( $psource_chat, 'send_message' ) ) {
			return $psource_chat->send_message( $session_id, $message );
		}

		if ( ! method_exists( $psource_chat, 'chat_session_send_message' ) ) {
			return new WP_Error( 'method_missing', __( 'Message endpoint unavailable.', 'psource-chat' ) );
		}

		$chat_session = self::resolve_chat_session( $session_id );
		if ( empty( $chat_session ) ) {
			return new WP_Error( 'invalid_session', __( 'Invalid chat session.', 'psource-chat' ) );
		}

		return $psource_chat->chat_session_send_message( wp_kses_post( $message ), $chat_session );
	}

	/**
	 * Resolve chat session from current runtime session map.
	 */
	private static function resolve_chat_session( $session_id ) {
		global $psource_chat;

		if ( ! empty( $psource_chat->chat_sessions ) && isset( $psource_chat->chat_sessions[ $session_id ] ) ) {
			return $psource_chat->chat_sessions[ $session_id ];
		}

		return false;
	}

	/**
	 * Basic anti-flood guard for high-frequency poll endpoints.
	 */
	private static function check_poll_rate_limit( $session_id ) {
		$ip      = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
		$key     = 'psource_chat_poll_' . md5( $ip . '|' . $session_id );
		$current = get_transient( $key );

		if ( false !== $current ) {
			return false;
		}

		set_transient( $key, 1, 1 );

		return true;
	}

	/**
	 * Get JavaScript configuration for modern AJAX
	 */
	public static function get_js_config() {
		return array(
			'rest_url'    => rest_url( 'psource-chat/v1/' ),
			'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'ajax_nonce'  => wp_create_nonce( 'psource_chat_nonce' ),
			'poll_url'    => admin_url( 'admin-ajax.php?action=psource_chat_poll' ),
			'use_rest'    => true, // Can be made configurable
			'poll_interval' => 2000, // 2 seconds for polling
			'cache_timeout' => 10000 // 10 seconds cache timeout
		);
	}
}

// Initialize on plugin load
add_action( 'plugins_loaded', array( 'PSource_Chat_AJAX', 'init' ) );

// Clear cache hook
add_action( 'psource_chat_clear_cache', array( 'PSource_Chat_AJAX', 'clear_cache_item' ) );
