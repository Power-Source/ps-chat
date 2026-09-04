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
	 * Per-request REST auth context.
	 *
	 * @var array
	 */
	private static $request_auth = array();

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
		register_rest_route( 'psource-chat/v1', '/auth/token', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'rest_issue_token' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'session_id' => array(
					'required'          => true,
					'validate_callback' => function( $param ) {
						return is_string( $param ) && ! empty( $param );
					},
				),
				'name' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'email' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_email',
				),
			),
		) );

		register_rest_route( 'psource-chat/v1', '/auth/revoke', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'rest_revoke_token' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'session_id' => array(
					'required'          => false,
					'validate_callback' => function( $param ) {
						return is_string( $param );
					},
				),
			),
		) );

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
		$auth_context = self::resolve_rest_auth( $request );
		if ( is_wp_error( $auth_context ) ) {
			return $auth_context;
		}

		$request_key                        = spl_object_hash( $request );
		self::$request_auth[ $request_key ] = $auth_context;

		return true;
	}

	/**
	 * REST API: Get messages
	 */
	public static function rest_get_messages( $request ) {
		$session_id = $request->get_param( 'session_id' );
		$last_id = intval( $request->get_param( 'last_id' ) );

		global $psource_chat;
		$auth_context = self::get_request_auth_context( $request );
		if ( is_wp_error( $auth_context ) ) {
			return $auth_context;
		}

		if ( isset( $auth_context['chat_auth'] ) && is_array( $auth_context['chat_auth'] ) ) {
			$psource_chat->chat_auth = $auth_context['chat_auth'];
		}
		
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
		$auth_context = self::get_request_auth_context( $request );
		if ( is_wp_error( $auth_context ) ) {
			return $auth_context;
		}

		if ( isset( $auth_context['chat_auth'] ) && is_array( $auth_context['chat_auth'] ) ) {
			$psource_chat->chat_auth = $auth_context['chat_auth'];
		}

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
		$auth_context = self::get_request_auth_context( $request );
		if ( is_wp_error( $auth_context ) ) {
			return $auth_context;
		}
		
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
	 * REST API: Issue an anonymous session-bound token for public_user chats.
	 */
	public static function rest_issue_token( $request ) {
		$session_id = sanitize_text_field( $request->get_param( 'session_id' ) );
		$name       = sanitize_text_field( $request->get_param( 'name' ) );
		$email      = sanitize_email( $request->get_param( 'email' ) );

		if ( empty( $session_id ) || empty( $name ) || empty( $email ) || ! is_email( $email ) ) {
			return new WP_Error( 'invalid_request', __( 'Missing or invalid token request fields.', 'psource-chat' ), array( 'status' => 400 ) );
		}

		if ( ! self::check_token_rate_limit( $session_id ) ) {
			return new WP_Error( 'rate_limited', __( 'Too many token requests. Please retry shortly.', 'psource-chat' ), array( 'status' => 429 ) );
		}

		$chat_session = self::resolve_chat_session( $session_id );
		if ( empty( $chat_session ) || ! self::session_allows_public_rest( $chat_session ) ) {
			return new WP_Error( 'forbidden', __( 'Anonymous REST access is not enabled for this chat session.', 'psource-chat' ), array( 'status' => 403 ) );
		}

		$token_data = self::create_public_rest_token( $chat_session, $name, $email );
		if ( is_wp_error( $token_data ) ) {
			return $token_data;
		}

		return rest_ensure_response( array(
			'success' => true,
			'data'    => array(
				'token'      => $token_data['token'],
				'expires_at' => $token_data['expires_at'],
				'session_id' => $session_id,
				'token_type' => 'Bearer',
			),
		) );
	}

	/**
	 * REST API: Revoke an anonymous REST token.
	 */
	public static function rest_revoke_token( $request ) {
		$raw_token = self::get_rest_token_from_request( $request );
		if ( empty( $raw_token ) ) {
			return new WP_Error( 'missing_token', __( 'Missing REST token.', 'psource-chat' ), array( 'status' => 400 ) );
		}

		$payload = self::parse_rest_token( $raw_token );
		if ( is_wp_error( $payload ) ) {
			return $payload;
		}

		delete_transient( self::get_public_rest_token_store_key( $payload['jti'] ) );

		return rest_ensure_response( array(
			'success' => true,
			'data'    => array(
				'revoked' => true,
			),
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
		global $psource_chat;

		$cache_key = 'chat_messages_' . md5( $session_id . '|' . $last_id );
		$cached    = self::get_cached( $cache_key );
		if ( null !== $cached ) {
			return $cached;
		}

		$chat_session = self::resolve_chat_session( $session_id );
		if ( empty( $chat_session ) ) {
			return new WP_Error( 'invalid_session', __( 'Invalid chat session.', 'psource-chat' ) );
		}

		$chat_session['last_row_id'] = max( 0, intval( $last_id ) );
		$chat_session['last_row_compare'] = ( $chat_session['last_row_id'] > 0 ) ? '>' : '>=';

		if ( method_exists( $psource_chat, 'chat_session_users_update_polltime' ) ) {
			$chat_session['ip_address'] = self::get_request_ip();
			$psource_chat->chat_session_users_update_polltime( $chat_session );
		}

		if ( ! method_exists( $psource_chat, 'chat_session_get_message_new' ) || ! method_exists( $psource_chat, 'chat_session_build_row' ) ) {
			return new WP_Error( 'method_missing', __( 'Chat session methods unavailable.', 'psource-chat' ) );
		}

		$rows      = $psource_chat->chat_session_get_message_new( $chat_session );
		$response  = array(
			'rows'        => array(),
			'last_row_id' => $last_id,
		);

		if ( ! empty( $rows ) ) {
			foreach ( $rows as $row ) {
				$row_key                         = strtotime( $row->timestamp ) . '-' . $row->id;
				$response['rows'][ $row_key ]    = $psource_chat->chat_session_build_row( $row, $chat_session );
				$response['last_row_id']         = max( intval( $response['last_row_id'] ), intval( $row->id ) );
			}
		}

		self::set_cached( $cache_key, $response, 5 );

		return $response;
	}

	/**
	 * Optimized user retrieval
	 */
	private static function get_users_optimized( $session_id ) {
		global $psource_chat;

		$cache_key = 'chat_users_' . md5( $session_id );
		$cached    = self::get_cached( $cache_key );
		if ( null !== $cached ) {
			return $cached;
		}

		$chat_session = self::resolve_chat_session( $session_id );
		if ( empty( $chat_session ) ) {
			return new WP_Error( 'invalid_session', __( 'Invalid chat session.', 'psource-chat' ) );
		}

		if ( ! method_exists( $psource_chat, 'chat_session_get_active_users' ) ) {
			return new WP_Error( 'method_missing', __( 'Chat user methods unavailable.', 'psource-chat' ) );
		}

		$users = $psource_chat->chat_session_get_active_users( $chat_session );

		self::set_cached( $cache_key, $users, 30 );

		return $users;
	}

	/**
	 * Quick check for new activity (ultra-lightweight)
	 */
	private static function has_new_activity( $session_id, $last_check ) {
		$messages = self::get_messages_optimized( $session_id, $last_check );
		if ( is_wp_error( $messages ) ) {
			return false;
		}

		return ! empty( $messages['rows'] );
	}

	/**
	 * Validate session
	 */
	private static function validate_session( $session_id ) {
		global $wpdb, $psource_chat;

		if ( empty( $session_id ) ) {
			return false;
		}

		if ( ! empty( self::resolve_chat_session( $session_id ) ) ) {
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

		$message = wp_kses_post( $message );
		if ( method_exists( $psource_chat, 'format_message_markup' ) ) {
			$message = $psource_chat->format_message_markup( $message, $chat_session );
		}

		return $psource_chat->chat_session_send_message( $message, $chat_session );
	}

	/**
	 * Resolve chat session from current runtime session map.
	 */
	private static function resolve_chat_session( $session_id ) {
		global $psource_chat;

		if ( ! empty( $psource_chat->chat_sessions ) && isset( $psource_chat->chat_sessions[ $session_id ] ) ) {
			return $psource_chat->chat_sessions[ $session_id ];
		}

		if ( 'bottom_corner' === $session_id && ! empty( $psource_chat->_chat_options['site'] ) ) {
			$fallback_session                 = $psource_chat->_chat_options['site'];
			$fallback_session['id']           = $session_id;
			$fallback_session['session_type'] = 'site';
			$fallback_session['blog_id']      = get_current_blog_id();

			return $fallback_session;
		}

		return false;
	}

	/**
	 * Resolve auth for REST requests.
	 */
	private static function resolve_rest_auth( $request ) {
		global $psource_chat;

		$session_id = sanitize_text_field( $request->get_param( 'session_id' ) );
		if ( empty( $session_id ) || ! self::validate_session( $session_id ) ) {
			return new WP_Error( 'invalid_session', __( 'Invalid chat session.', 'psource-chat' ), array( 'status' => 400 ) );
		}

		if ( is_user_logged_in() && current_user_can( 'read' ) ) {
			$rest_nonce = $request->get_header( 'X-WP-Nonce' );
			if ( ! empty( $rest_nonce ) && wp_verify_nonce( $rest_nonce, 'wp_rest' ) ) {
				return array(
					'mode'      => 'wordpress',
					'session_id' => $session_id,
					'chat_auth' => isset( $psource_chat->chat_auth ) ? $psource_chat->chat_auth : array(),
				);
			}
		}

		$token = self::get_rest_token_from_request( $request );
		if ( empty( $token ) ) {
			return new WP_Error( 'forbidden', __( 'Authentication required.', 'psource-chat' ), array( 'status' => 401 ) );
		}

		$token_auth = self::validate_public_rest_token( $token, $session_id );
		if ( is_wp_error( $token_auth ) ) {
			return $token_auth;
		}

		return array(
			'mode'       => 'public_user',
			'session_id' => $session_id,
			'chat_auth'  => $token_auth,
		);
	}

	/**
	 * Get the resolved auth context for a request.
	 */
	private static function get_request_auth_context( $request ) {
		$request_key = spl_object_hash( $request );
		if ( isset( self::$request_auth[ $request_key ] ) ) {
			return self::$request_auth[ $request_key ];
		}

		$context = self::resolve_rest_auth( $request );
		if ( ! is_wp_error( $context ) ) {
			self::$request_auth[ $request_key ] = $context;
		}

		return $context;
	}

	/**
	 * Whether a session allows anonymous public_user REST auth.
	 */
	private static function session_allows_public_rest( $chat_session ) {
		if ( empty( $chat_session['login_options'] ) || ! is_array( $chat_session['login_options'] ) ) {
			return false;
		}

		return in_array( 'public_user', $chat_session['login_options'], true );
	}

	/**
	 * Create a signed public-user REST token and persist it for revocation.
	 */
	private static function create_public_rest_token( $chat_session, $name, $email ) {
		$ip         = self::get_request_ip();
		$auth_hash  = md5( $name . $email . $ip );
		$issued_at  = time();
		$ttl        = self::get_public_rest_token_ttl();
		$expires_at = $issued_at + $ttl;
		$jti        = wp_generate_uuid4();

		$payload = array(
			'v'   => 1,
			'jti' => $jti,
			'sid' => $chat_session['id'],
			'ah'  => $auth_hash,
			'typ' => 'public_user',
			'iat' => $issued_at,
			'exp' => $expires_at,
		);

		$token = self::encode_public_rest_token( $payload );

		$chat_auth = array(
			'type'        => 'public_user',
			'name'        => $name,
			'email'       => $email,
			'auth_hash'   => $auth_hash,
			'ip_address'  => $ip,
			'profile_link'=> '',
			'avatar'      => self::get_public_rest_avatar( $email, $name ),
		);

		set_transient(
			self::get_public_rest_token_store_key( $jti ),
			array(
				'session_id' => $chat_session['id'],
				'chat_auth'  => $chat_auth,
				'expires_at' => $expires_at,
			),
			$ttl
		);

		return array(
			'token'      => $token,
			'expires_at' => gmdate( 'c', $expires_at ),
			'chat_auth'  => $chat_auth,
		);
	}

	/**
	 * Validate a signed public-user REST token.
	 */
	private static function validate_public_rest_token( $token, $expected_session_id ) {
		$payload = self::parse_rest_token( $token );
		if ( is_wp_error( $payload ) ) {
			return $payload;
		}

		if ( ! empty( $expected_session_id ) && $payload['sid'] !== $expected_session_id ) {
			return new WP_Error( 'invalid_token', __( 'REST token does not match the requested session.', 'psource-chat' ), array( 'status' => 403 ) );
		}

		$stored = get_transient( self::get_public_rest_token_store_key( $payload['jti'] ) );
		if ( empty( $stored ) || empty( $stored['chat_auth'] ) ) {
			return new WP_Error( 'revoked_token', __( 'REST token expired or was revoked.', 'psource-chat' ), array( 'status' => 401 ) );
		}

		if ( empty( $stored['session_id'] ) || $stored['session_id'] !== $payload['sid'] ) {
			return new WP_Error( 'invalid_token', __( 'REST token session mismatch.', 'psource-chat' ), array( 'status' => 403 ) );
		}

		$chat_session = self::resolve_chat_session( $payload['sid'] );
		if ( empty( $chat_session ) || ! self::session_allows_public_rest( $chat_session ) ) {
			return new WP_Error( 'forbidden', __( 'Anonymous REST access is not enabled for this chat session.', 'psource-chat' ), array( 'status' => 403 ) );
		}

		return $stored['chat_auth'];
	}

	/**
	 * Parse and verify a signed REST token.
	 */
	private static function parse_rest_token( $token ) {
		$parts = explode( '.', $token );
		if ( 2 !== count( $parts ) ) {
			return new WP_Error( 'invalid_token', __( 'Malformed REST token.', 'psource-chat' ), array( 'status' => 400 ) );
		}

		$payload_json = self::base64_url_decode( $parts[0] );
		$signature    = self::base64_url_decode( $parts[1] );
		$expected_sig = hash_hmac( 'sha256', $payload_json, wp_salt( 'auth' ), true );

		if ( ! hash_equals( $expected_sig, $signature ) ) {
			return new WP_Error( 'invalid_token', __( 'Invalid REST token signature.', 'psource-chat' ), array( 'status' => 403 ) );
		}

		$payload = json_decode( $payload_json, true );
		if ( ! is_array( $payload ) || empty( $payload['jti'] ) || empty( $payload['sid'] ) || empty( $payload['typ'] ) ) {
			return new WP_Error( 'invalid_token', __( 'Incomplete REST token payload.', 'psource-chat' ), array( 'status' => 400 ) );
		}

		if ( empty( $payload['exp'] ) || time() >= intval( $payload['exp'] ) ) {
			return new WP_Error( 'expired_token', __( 'REST token expired.', 'psource-chat' ), array( 'status' => 401 ) );
		}

		return $payload;
	}

	/**
	 * Encode payload as signed token.
	 */
	private static function encode_public_rest_token( $payload ) {
		$payload_json = wp_json_encode( $payload );
		$signature    = hash_hmac( 'sha256', $payload_json, wp_salt( 'auth' ), true );

		return self::base64_url_encode( $payload_json ) . '.' . self::base64_url_encode( $signature );
	}

	/**
	 * Get bearer token from request.
	 */
	private static function get_rest_token_from_request( $request ) {
		$auth_header = $request->get_header( 'Authorization' );
		if ( ! empty( $auth_header ) && preg_match( '/Bearer\s+(.*)$/i', $auth_header, $matches ) ) {
			return trim( $matches[1] );
		}

		$custom_header = $request->get_header( 'X-PSource-Chat-Token' );
		if ( ! empty( $custom_header ) ) {
			return trim( $custom_header );
		}

		$param_token = $request->get_param( 'token' );
		if ( is_string( $param_token ) && '' !== $param_token ) {
			return trim( $param_token );
		}

		return '';
	}

	/**
	 * Rate limit token issuance.
	 */
	private static function check_token_rate_limit( $session_id ) {
		$ip      = self::get_request_ip();
		$key     = 'psource_chat_rest_issue_' . md5( $ip . '|' . $session_id );
		$current = get_transient( $key );

		if ( false !== $current ) {
			return false;
		}

		set_transient( $key, 1, 5 );

		return true;
	}

	/**
	 * TTL for anonymous REST tokens.
	 */
	private static function get_public_rest_token_ttl() {
		return (int) apply_filters( 'psource_chat_public_rest_token_ttl', 15 * MINUTE_IN_SECONDS );
	}

	/**
	 * Storage key for revocable REST tokens.
	 */
	private static function get_public_rest_token_store_key( $jti ) {
		return 'psource_chat_rest_token_' . md5( $jti );
	}

	/**
	 * Resolve avatar for anonymous REST users.
	 */
	private static function get_public_rest_avatar( $email, $name ) {
		if ( class_exists( 'PSource_Chat_Avatar' ) && method_exists( 'PSource_Chat_Avatar', 'get_chat_avatar' ) ) {
			return PSource_Chat_Avatar::get_chat_avatar( 0, $email, $name );
		}

		return get_avatar_url( $email );
	}

	/**
	 * Request IP helper.
	 */
	private static function get_request_ip() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	}

	/**
	 * URL-safe base64 helpers.
	 */
	private static function base64_url_encode( $value ) {
		return rtrim( strtr( base64_encode( $value ), '+/', '-_' ), '=' );
	}

	private static function base64_url_decode( $value ) {
		$padding = strlen( $value ) % 4;
		if ( $padding > 0 ) {
			$value .= str_repeat( '=', 4 - $padding );
		}

		return base64_decode( strtr( $value, '-_', '+/' ) );
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
			'rest_auth_url' => rest_url( 'psource-chat/v1/auth/token' ),
			'rest_revoke_url' => rest_url( 'psource-chat/v1/auth/revoke' ),
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
