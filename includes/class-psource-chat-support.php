<?php
/**
 * Private support conversations, deliberately isolated from public chat sessions.
 */
defined( 'ABSPATH' ) || exit;

class PSource_Chat_Support {
	const VERSION = '1';
	const OPTION = 'psource_chat_support';
	private static $embedded = false;

	public static function boot() {
		add_action( 'init', array( __CLASS__, 'install' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'no_cache' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'wp_footer', array( __CLASS__, 'floating_widget' ) );
		add_action( 'template_redirect', array( __CLASS__, 'agent_page' ) );
		add_action( 'ps_chat_support_cleanup', array( __CLASS__, 'cleanup' ) );
		add_shortcode( 'ps_chat_support', array( __CLASS__, 'shortcode' ) );
		add_action( 'widgets_init', array( __CLASS__, 'register_widget' ) );
	}

	private static function table( $suffix ) {
		global $wpdb;
		return $wpdb->base_prefix . 'ps_chat_support_' . $suffix;
	}

	public static function install() {
		global $wpdb;
		if ( get_site_option( 'ps_chat_support_schema' ) !== self::VERSION ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$charset = $wpdb->get_charset_collate();
			$sessions = self::table( 'sessions' );
			$messages = self::table( 'messages' );
			$limits = self::table( 'limits' );
			dbDelta( "CREATE TABLE $sessions (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				public_id varchar(32) NOT NULL,
				network_id bigint(20) unsigned NOT NULL,
				origin_blog_id bigint(20) unsigned NOT NULL,
				support_blog_id bigint(20) unsigned NOT NULL,
				category_id bigint(20) unsigned NOT NULL DEFAULT 0,
				category_name varchar(200) NOT NULL DEFAULT '',
				guest_hash varchar(64) NOT NULL,
				guest_name varchar(100) NOT NULL DEFAULT '',
				guest_email varchar(254) NOT NULL DEFAULT '',
				recipient_ids text NOT NULL,
				state varchar(20) NOT NULL DEFAULT 'waiting',
				assigned_user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				notification_status varchar(20) NOT NULL DEFAULT 'pending',
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				expires_at datetime NOT NULL,
				delete_after datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY public_id (public_id),
				KEY inbox (network_id,state,updated_at),
				KEY cleanup (delete_after)
			) $charset;" );
			dbDelta( "CREATE TABLE $messages (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				session_id bigint(20) unsigned NOT NULL,
				author_kind varchar(10) NOT NULL,
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				body text NOT NULL,
				client_id varchar(64) NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY request_id (session_id,client_id),
				KEY conversation (session_id,id)
			) $charset;" );
			dbDelta( "CREATE TABLE $limits (
				rate_key varchar(64) NOT NULL,
				attempts int unsigned NOT NULL DEFAULT 0,
				expires_at datetime NOT NULL,
				PRIMARY KEY  (rate_key),
				KEY expiry (expires_at)
			) $charset;" );
			if ( $wpdb->last_error ) {
				error_log( 'PS Chat Support: schema installation failed: ' . $wpdb->last_error );
				return;
			}
			foreach ( array( $sessions, $messages, $limits ) as $table ) {
				if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) !== $table ) {
					error_log( 'PS Chat Support: required table is missing: ' . $table );
					return;
				}
			}
			update_site_option( 'ps_chat_support_schema', self::VERSION );
		}
		if ( ( ! is_multisite() || get_current_blog_id() === get_main_site_id() ) && ! wp_next_scheduled( 'ps_chat_support_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'ps_chat_support_cleanup' );
		}
	}

	public static function settings( $network = false ) {
		if ( class_exists( 'PSource_Support_Chat_Settings' ) ) {
			return PSource_Support_Chat_Settings::settings( $network );
		}
		$value = $network ? get_site_option( self::OPTION, array() ) : get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $value ) ? $value : array(), array(
			'mode' => 'disabled', 'placement' => 'floating', 'label' => __( 'Support', 'psource-chat' ),
			'require_name' => false, 'require_email' => false, 'session_hours' => 168,
			'retention_days' => 30, 'fallback_users' => array(), 'intro_text' => '', 'allow_subsite_chat' => false,
		) );
	}

	private static function context() {
		if ( ! function_exists( 'psource_support_get_ticket_categories' ) || ! class_exists( 'PSource_Support_Chat_Settings' ) ) {
			return self::error( 'unavailable', __( 'PS Support mit Supportchat-Integration muss aktiv sein.', 'psource-chat' ), 503 );
		}
		$site = self::settings();
		if ( is_multisite() ) {
			$policy = self::settings( true );
			$central = get_current_blog_id() === PSource_Support_Chat_Settings::central_blog_id();
			if ( $central ) {
				if ( $policy['mode'] !== 'network' ) {
					return self::error( 'disabled', __( 'Der Netzwerk-Supportchat ist deaktiviert.', 'psource-chat' ), 403 );
				}
				return array( 'scope' => 0, 'policy' => $policy, 'site' => $policy );
			}
			if ( ! $policy['allow_subsite_chat'] ) {
				return self::error( 'disabled', __( 'Supportchats auf Subsites sind nicht freigegeben.', 'psource-chat' ), 403 );
			}
			if ( $site['mode'] === 'network' ) {
				return $policy['mode'] === 'network' ? array( 'scope' => 0, 'policy' => $policy, 'site' => $site )
					: self::error( 'disabled', __( 'Der Netzwerk-Supportchat ist deaktiviert.', 'psource-chat' ), 403 );
			}
		}
		if ( $site['mode'] === 'local' ) {
			return array( 'scope' => is_multisite() ? get_current_blog_id() : 0, 'policy' => $site, 'site' => $site );
		}
		return self::error( 'disabled', __( 'Der Supportchat ist deaktiviert.', 'psource-chat' ), 403 );
	}

	private static function error( $code, $message, $status = 400 ) {
		return new WP_Error( 'ps_chat_support_' . $code, $message, array( 'status' => $status ) );
	}

	private static function db_error() {
		global $wpdb;
		error_log( 'PS Chat Support: database operation failed: ' . $wpdb->last_error );
		return self::error( 'database', __( 'Die Unterhaltung konnte nicht gespeichert werden. Bitte erneut versuchen.', 'psource-chat' ), 500 );
	}

	public static function no_cache( $response, $server, $request ) {
		if ( strpos( $request->get_route(), '/psource-chat/v1/support/' ) === 0 ) {
			$response->header( 'Cache-Control', 'private, no-store, max-age=0' );
			$response->header( 'Vary', 'Cookie, Origin' );
			$response->header( 'X-Robots-Tag', 'noindex, nofollow' );
		}
		return $response;
	}

	public static function routes() {
		$routes = array(
			'/config' => array( 'GET', 'config', '__return_true' ),
			'/session' => array( 'GET', 'guest_session', 'guest_permission' ),
			'/start' => array( 'POST', 'start', 'same_origin' ),
			'/message' => array( 'POST', 'guest_message', 'guest_permission' ),
			'/close' => array( 'POST', 'guest_close', 'guest_permission' ),
			'/inbox' => array( 'GET', 'inbox', 'staff_permission' ),
			'/conversation/(?P<id>[a-f0-9]{32})' => array( 'GET', 'conversation', 'staff_permission' ),
			'/claim/(?P<id>[a-f0-9]{32})' => array( 'POST', 'claim', 'staff_permission' ),
			'/reply/(?P<id>[a-f0-9]{32})' => array( 'POST', 'reply', 'staff_permission' ),
			'/resolve/(?P<id>[a-f0-9]{32})' => array( 'POST', 'resolve', 'staff_permission' ),
			'/notify/(?P<id>[a-f0-9]{32})' => array( 'POST', 'renotify', 'staff_permission' ),
		);
		foreach ( $routes as $path => $route ) {
			register_rest_route( 'psource-chat/v1', '/support' . $path, array(
				'methods' => $route[0], 'callback' => array( __CLASS__, $route[1] ),
				'permission_callback' => $route[2] === '__return_true' ? '__return_true' : array( __CLASS__, $route[2] ),
			) );
		}
	}

	public static function same_origin( $request ) {
		$origin = $request->get_header( 'origin' );
		if ( ! $origin ) {
			$origin = $request->get_header( 'referer' );
		}
		$expected = wp_parse_url( home_url( '/' ) );
		$actual = wp_parse_url( $origin );
		if ( ! is_array( $actual ) || empty( $actual['host'] ) || empty( $actual['scheme'] )
			|| strtolower( $actual['host'] ) !== strtolower( $expected['host'] )
			|| $actual['scheme'] !== $expected['scheme']
			|| ( isset( $actual['port'] ) ? $actual['port'] : null ) !== ( isset( $expected['port'] ) ? $expected['port'] : null ) ) {
			return self::error( 'origin', __( 'Diese Anfrage muss von der Support-Website stammen.', 'psource-chat' ), 403 );
		}
		return true;
	}

	private static function cookie_name() {
		return 'ps_chat_support_' . get_current_network_id() . '_' . get_current_blog_id();
	}

	private static function guest() {
		global $wpdb;
		$cookie = isset( $_COOKIE[ self::cookie_name() ] ) ? $_COOKIE[ self::cookie_name() ] : '';
		if ( ! is_string( $cookie ) || ! preg_match( '/^([a-f0-9]{32})\.([a-f0-9]{64})$/D', $cookie, $parts ) ) {
			return false;
		}
		$row = $wpdb->get_row( $wpdb->prepare(
			'SELECT * FROM ' . self::table( 'sessions' ) . ' WHERE public_id=%s AND network_id=%d AND origin_blog_id=%d',
			$parts[1], get_current_network_id(), get_current_blog_id()
		) );
		if ( $wpdb->last_error ) {
			return self::db_error();
		}
		if ( ! $row || ! hash_equals( $row->guest_hash, hash_hmac( 'sha256', $parts[2], wp_salt( 'auth' ) ) )
			|| $row->expires_at <= gmdate( 'Y-m-d H:i:s' ) ) {
			return false;
		}
		return $row;
	}

	public static function guest_permission( $request ) {
		if ( $request->get_method() !== 'GET' || $request->get_header( 'origin' ) ) {
			$origin = self::same_origin( $request );
			if ( is_wp_error( $origin ) ) {
				return $origin;
			}
		}
		$guest = self::guest();
		return is_wp_error( $guest ) ? $guest : ( $guest ? true : self::error( 'expired', __( 'Die Support-Sitzung ist abgelaufen. Bitte eine neue Anfrage starten.', 'psource-chat' ), 401 ) );
	}

	public static function staff_permission() {
		return is_user_logged_in() ? true : self::error(
			'login', __( 'Bitte als Support-Mitarbeiter anmelden.', 'psource-chat' ), 403
		);
	}

	private static function user_allowed( $user_id, $scope ) {
		if ( ! get_userdata( $user_id ) ) {
			return false;
		}
		return ! $scope || ! is_multisite() || is_super_admin( $user_id ) || is_user_member_of_blog( $user_id, $scope );
	}

	private static function recipients( $category, $context ) {
		$ids = array();
		if ( $category ) {
			$ids = function_exists( 'psource_support_get_category_assignee_ids' )
				? psource_support_get_category_assignee_ids( $category ) : array( (int) $category->user_id );
		}
		$scope = $context['scope'];
		$ids = array_values( array_filter( array_unique( array_map( 'absint', $ids ) ), function ( $id ) use ( $scope ) {
			return $id && self::user_allowed( $id, $scope );
		} ) );
		if ( ! $ids ) {
			$eligible = PSource_Support_Chat_Settings::staff_ids( $scope );
			$ids = array_values( array_filter( (array) $context['policy']['fallback_users'], function ( $id ) use ( $scope, $eligible ) {
				return $id && self::user_allowed( $id, $scope ) && in_array( (int) $id, $eligible, true );
			} ) );
		}
		return $ids;
	}

	public static function config() {
		$context = self::context();
		if ( is_wp_error( $context ) ) {
			return $context;
		}
		$categories = psource_support_get_ticket_categories( array( 'blog_id' => $context['scope'] ) );
		return array(
			'categories' => array_map( function ( $category ) {
				return array( 'id' => (int) $category->cat_id, 'name' => $category->cat_name );
			}, $categories ),
			'require_name' => (bool) $context['policy']['require_name'],
			'require_email' => (bool) $context['policy']['require_email'],
			'session_hours' => (int) $context['policy']['session_hours'],
			'retention_days' => (int) $context['policy']['retention_days'],
			'intro_text' => $context['policy']['intro_text'],
			'bot' => class_exists( 'PSource_Support_Bot' ) ? PSource_Support_Bot::config( $context['scope'] ) : null,
		);
	}

	private static function rate_limit( $key, $maximum, $seconds ) {
		global $wpdb;
		$now = time();
		$rate_key = hash_hmac( 'sha256', get_current_network_id() . ':' . $key . ':' . floor( $now / $seconds ), wp_salt( 'nonce' ) );
		$table = self::table( 'limits' );
		$result = $wpdb->query( $wpdb->prepare(
			"INSERT INTO $table (rate_key,attempts,expires_at) VALUES (%s,1,%s) ON DUPLICATE KEY UPDATE attempts=attempts+1",
			$rate_key, gmdate( 'Y-m-d H:i:s', $now + $seconds * 2 )
		) );
		if ( $result === false ) {
			return self::db_error();
		}
		$attempts = (int) $wpdb->get_var( $wpdb->prepare( "SELECT attempts FROM $table WHERE rate_key=%s", $rate_key ) );
		if ( $wpdb->last_error ) {
			return self::db_error();
		}
		return $attempts <= $maximum ? true : self::error( 'rate', __( 'Zu viele Anfragen. Bitte etwas warten.', 'psource-chat' ), 429 );
	}

	private static function message_input( $request ) {
		$body = $request->get_param( 'message' );
		$client_id = $request->get_param( 'client_id' );
		if ( ! is_string( $body ) || ! trim( $body ) || strlen( $body ) > 16000
			|| ! is_string( $client_id ) || ! preg_match( '/^[a-zA-Z0-9_-]{8,64}$/D', $client_id ) ) {
			return self::error( 'message', __( 'Bitte eine Nachricht mit maximal 4000 Zeichen senden.', 'psource-chat' ) );
		}
		$body = sanitize_textarea_field( $body );
		if ( ! trim( $body ) || ( function_exists( 'mb_strlen' ) ? mb_strlen( $body ) : strlen( $body ) ) > 4000 ) {
			return self::error( 'message', __( 'Bitte eine Nachricht mit maximal 4000 Zeichen senden.', 'psource-chat' ) );
		}
		return array( 'body' => $body, 'client_id' => $client_id );
	}

	public static function start( $request ) {
		global $wpdb;
		$context = self::context();
		if ( is_wp_error( $context ) ) {
			return $context;
		}
		$existing = self::guest();
		if ( is_wp_error( $existing ) ) {
			return $existing;
		}
		$input = self::message_input( $request );
		if ( is_wp_error( $input ) ) {
			return $input;
		}
		if ( $existing && $existing->state !== 'closed' ) {
			$result = self::insert_message( $existing, $input, 'guest', 0 );
			return is_wp_error( $result ) ? $result : self::payload( $existing );
		}
		$name = $request->get_param( 'name' );
		$email = $request->get_param( 'email' );
		if ( ! is_string( $name ) || ! is_string( $email ) || strlen( $name ) > 100 || strlen( $email ) > 254 ) {
			return self::error( 'contact', __( 'Bitte gültige Kontaktangaben eingeben.', 'psource-chat' ) );
		}
		$name = sanitize_text_field( $name );
		$email = trim( $email );
		if ( ( $context['policy']['require_name'] && ! $name ) || ( $context['policy']['require_email'] && ! $email )
			|| ( $email && ! is_email( $email ) ) ) {
			return self::error( 'contact', __( 'Bitte die erforderlichen Kontaktangaben ausfüllen und eine gültige E-Mail-Adresse verwenden.', 'psource-chat' ) );
		}
		$category_id = $request->get_param( 'category_id' );
		if ( ! is_numeric( $category_id ) || (int) $category_id < 0 || (string) (int) $category_id !== (string) $category_id ) {
			return self::error( 'category', __( 'Bitte eine gültige Support-Kategorie wählen.', 'psource-chat' ) );
		}
		$category = null;
		foreach ( psource_support_get_ticket_categories( array( 'blog_id' => $context['scope'] ) ) as $candidate ) {
			if ( (int) $candidate->cat_id === (int) $category_id ) {
				$category = $candidate;
				break;
			}
		}
		if ( (int) $category_id && ! $category ) {
			return self::error( 'category', __( 'Diese Kategorie gehört nicht zum gewählten Support.', 'psource-chat' ) );
		}
		$recipients = self::recipients( $category, $context );
		if ( ! $recipients ) {
			return self::error( 'staff', __( 'Es ist kein Support-Ansprechpartner eingerichtet. Bitte später erneut versuchen.', 'psource-chat' ), 503 );
		}
		$rate = self::rate_limit( 'start:' . ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ), 5, HOUR_IN_SECONDS );
		if ( is_wp_error( $rate ) ) {
			return $rate;
		}
		$public_id = bin2hex( random_bytes( 16 ) );
		$secret = bin2hex( random_bytes( 32 ) );
		$now = time();
		$expires = $now + (int) $context['policy']['session_hours'] * HOUR_IN_SECONDS;
		$retention = max( (int) $context['policy']['retention_days'] * DAY_IN_SECONDS, $expires - $now );
		$row = array(
			'public_id' => $public_id, 'network_id' => get_current_network_id(),
			'origin_blog_id' => get_current_blog_id(), 'support_blog_id' => $context['scope'],
			'category_id' => $category ? $category->cat_id : 0,
			'category_name' => $category ? $category->cat_name : __( 'Allgemein', 'psource-chat' ),
			'guest_hash' => hash_hmac( 'sha256', $secret, wp_salt( 'auth' ) ),
			'guest_name' => $name, 'guest_email' => $email, 'recipient_ids' => ',' . implode( ',', $recipients ) . ',',
			'created_at' => gmdate( 'Y-m-d H:i:s', $now ), 'updated_at' => gmdate( 'Y-m-d H:i:s', $now ),
			'expires_at' => gmdate( 'Y-m-d H:i:s', $expires ),
			'delete_after' => gmdate( 'Y-m-d H:i:s', $now + $retention ),
		);
		if ( ! $wpdb->insert( self::table( 'sessions' ), $row ) ) {
			return self::db_error();
		}
		$id = $wpdb->insert_id;
		$row = (object) array_merge( $row, array( 'id' => $id, 'state' => 'waiting', 'assigned_user_id' => 0, 'notification_status' => 'pending' ) );
		$result = self::insert_message( $row, $input, 'guest', 0 );
		if ( is_wp_error( $result ) ) {
			$wpdb->delete( self::table( 'sessions' ), array( 'id' => $id ) );
			return $result;
		}
		$cookie = $public_id . '.' . $secret;
		$cookie_set = setcookie( self::cookie_name(), $cookie, array(
			'expires' => $expires, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Strict',
		) );
		if ( ! $cookie_set ) {
			error_log( 'PS Chat Support: guest cookie could not be set.' );
			return self::error( 'cookie', __( 'Der Browser-Zugang konnte nicht angelegt werden. Bitte erneut versuchen.', 'psource-chat' ), 500 );
		}
		$_COOKIE[ self::cookie_name() ] = $cookie;
		$notification = self::notify( $row );
		$payload = self::payload( self::guest() );
		if ( ! is_wp_error( $payload ) && is_wp_error( $notification ) ) {
			$payload['notification_status'] = 'failed';
		}
		return $payload;
	}

	private static function insert_message( $row, $input, $kind, $user_id ) {
		global $wpdb;
		$table = self::table( 'messages' );
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE session_id=%d AND client_id=%s", $row->id, $input['client_id'] ) );
		if ( $wpdb->last_error ) {
			return self::db_error();
		}
		if ( $existing ) {
			return $existing->author_kind === $kind && (int) $existing->user_id === (int) $user_id && $existing->body === $input['body']
				? true : self::error( 'request', __( 'Diese Nachrichtenkennung ist bereits vergeben.', 'psource-chat' ), 409 );
		}
		$rate = self::rate_limit( 'message:' . $row->id . ':' . $kind . ':' . $user_id, 30, MINUTE_IN_SECONDS );
		if ( is_wp_error( $rate ) ) {
			return $rate;
		}
		$sessions = self::table( 'sessions' );
		$now = gmdate( 'Y-m-d H:i:s' );
		$result = $wpdb->query( $wpdb->prepare(
			"INSERT INTO $table (session_id,author_kind,user_id,body,client_id,created_at)
			SELECT s.id,%s,%d,%s,%s,%s FROM $sessions s
			WHERE s.id=%d AND s.state IN ('waiting','active') AND s.expires_at>%s
			AND (%s='guest' OR (s.state='active' AND s.assigned_user_id=%d))
			ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID($table.id)",
			$kind, $user_id, $input['body'], $input['client_id'], $now, $row->id, $now, $kind, $user_id
		) );
		if ( $result === false ) {
			return self::db_error();
		}
		$saved = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE session_id=%d AND client_id=%s", $row->id, $input['client_id'] ) );
		if ( $wpdb->last_error ) {
			return self::db_error();
		}
		if ( ! $saved || $saved->author_kind !== $kind || (int) $saved->user_id !== (int) $user_id || $saved->body !== $input['body'] ) {
			return self::error( 'conflict', __( 'Die Unterhaltung ist nicht mehr offen oder diese Nachrichtenkennung ist bereits vergeben. Bitte den Chat aktualisieren.', 'psource-chat' ), 409 );
		}
		if ( $wpdb->update( self::table( 'sessions' ), array( 'updated_at' => gmdate( 'Y-m-d H:i:s' ) ), array( 'id' => $row->id ) ) === false ) {
			return self::db_error();
		}
		return true;
	}

	private static function payload( $row, $since = 0, $staff = false ) {
		global $wpdb;
		if ( is_wp_error( $row ) ) {
			return $row;
		}
		if ( ! $row ) {
			return self::error( 'expired', __( 'Die Support-Sitzung ist abgelaufen. Bitte eine neue Anfrage starten.', 'psource-chat' ), 401 );
		}
		$table = self::table( 'messages' );
		$messages = $since ? $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table WHERE session_id=%d AND id>%d ORDER BY id ASC LIMIT 100", $row->id, $since
		) ) : $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table WHERE session_id=%d ORDER BY id DESC LIMIT 100", $row->id
		) );
		if ( $wpdb->last_error ) {
			return self::db_error();
		}
		if ( ! $since ) {
			$messages = array_reverse( $messages );
		}
		$agent = $row->assigned_user_id ? get_userdata( $row->assigned_user_id ) : false;
		$result = array(
			'id' => $row->public_id, 'state' => $row->expires_at <= gmdate( 'Y-m-d H:i:s' ) ? 'expired' : $row->state,
			'category' => $row->category_name, 'name' => $row->guest_name ?: __( 'Gast', 'psource-chat' ),
			'agent' => $agent ? $agent->display_name : '', 'assigned_user_id' => (int) $row->assigned_user_id,
			'expires_at' => str_replace( ' ', 'T', $row->expires_at ) . 'Z', 'notification_status' => $row->notification_status,
			'messages' => array_map( function ( $message ) use ( $row ) {
				$user = $message->user_id ? get_userdata( $message->user_id ) : false;
				return array(
					'id' => (int) $message->id, 'kind' => $message->author_kind, 'body' => $message->body,
					'name' => $user ? $user->display_name : ( $row->guest_name ?: __( 'Gast', 'psource-chat' ) ),
					'created_at' => str_replace( ' ', 'T', $message->created_at ) . 'Z',
				);
			}, $messages ),
		);
		if ( $staff ) {
			$result['email'] = $row->guest_email;
			$result['origin_blog_id'] = (int) $row->origin_blog_id;
			$result['support_blog_id'] = (int) $row->support_blog_id;
		}
		return $result;
	}

	public static function guest_session( $request ) {
		return self::payload( self::guest(), absint( $request->get_param( 'since' ) ) );
	}

	public static function guest_message( $request ) {
		$row = self::guest();
		if ( is_wp_error( $row ) || ! $row ) {
			return self::payload( $row );
		}
		if ( $row->state === 'closed' ) {
			return self::error( 'closed', __( 'Diese Unterhaltung ist geschlossen.', 'psource-chat' ), 409 );
		}
		$input = self::message_input( $request );
		if ( is_wp_error( $input ) ) {
			return $input;
		}
		$result = self::insert_message( $row, $input, 'guest', 0 );
		return is_wp_error( $result ) ? $result : self::payload( self::guest(), absint( $request->get_param( 'since' ) ) );
	}

	public static function guest_close() {
		return self::close_row( self::guest() );
	}

	private static function close_row( $row ) {
		global $wpdb;
		if ( is_wp_error( $row ) || ! $row ) {
			return self::payload( $row );
		}
		$result = $wpdb->update( self::table( 'sessions' ), array( 'state' => 'closed', 'updated_at' => gmdate( 'Y-m-d H:i:s' ) ), array( 'id' => $row->id ) );
		if ( $result === false ) {
			return self::db_error();
		}
		return array( 'closed' => true );
	}

	private static function staff_allowed( $row ) {
		$id = get_current_user_id();
		if ( ! $id || (int) $row->network_id !== get_current_network_id() ) {
			return false;
		}
		if ( is_super_admin( $id ) ) {
			return true;
		}
		$scope = (int) $row->support_blog_id;
		if ( ! self::user_allowed( $id, $scope ) ) {
			return false;
		}
		if ( $scope ) {
			switch_to_blog( $scope );
			$admin = current_user_can( 'manage_options' );
			restore_current_blog();
			if ( $admin ) {
				return true;
			}
		}
		return strpos( $row->recipient_ids, ',' . $id . ',' ) !== false || (int) $row->assigned_user_id === $id;
	}

	private static function staff_row( $request ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare(
			'SELECT * FROM ' . self::table( 'sessions' ) . ' WHERE public_id=%s AND network_id=%d',
			$request->get_param( 'id' ), get_current_network_id()
		) );
		if ( $wpdb->last_error ) {
			return self::db_error();
		}
		return $row && self::staff_allowed( $row ) ? $row : self::error( 'access', __( 'Keine Berechtigung für diese Support-Unterhaltung.', 'psource-chat' ), 403 );
	}

	public static function inbox() {
		global $wpdb;
		$id = get_current_user_id();
		$access = '';
		$args = array( get_current_network_id() );
		if ( ! is_super_admin( $id ) ) {
			$admin_sites = array();
			foreach ( get_blogs_of_user( $id ) as $site ) {
				switch_to_blog( $site->userblog_id );
				if ( current_user_can( 'manage_options' ) ) {
					$admin_sites[] = (int) $site->userblog_id;
				}
				restore_current_blog();
			}
			$access = ' AND (assigned_user_id=%d OR recipient_ids LIKE %s';
			$args[] = $id;
			$args[] = '%,' . $id . ',%';
			if ( $admin_sites ) {
				$access .= ' OR support_blog_id IN (' . implode( ',', $admin_sites ) . ')';
			}
			$access .= ')';
		}
		$rows = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM ' . self::table( 'sessions' ) . ' WHERE network_id=%d' . $access . ' ORDER BY updated_at DESC,id DESC LIMIT 200',
			$args
		) );
		if ( $wpdb->last_error ) {
			return self::db_error();
		}
		$result = array();
		foreach ( $rows as $row ) {
			if ( self::staff_allowed( $row ) ) {
				$result[] = array(
					'id' => $row->public_id, 'name' => $row->guest_name ?: __( 'Gast', 'psource-chat' ),
					'category' => $row->category_name, 'state' => $row->expires_at <= gmdate( 'Y-m-d H:i:s' ) ? 'expired' : $row->state,
					'origin_blog_id' => (int) $row->origin_blog_id, 'support_blog_id' => (int) $row->support_blog_id,
					'notification_status' => $row->notification_status, 'updated_at' => $row->updated_at,
				);
			}
		}
		return $result;
	}

	public static function conversation( $request ) {
		$row = self::staff_row( $request );
		return is_wp_error( $row ) ? $row : self::payload( $row, absint( $request->get_param( 'since' ) ), true );
	}

	public static function claim( $request ) {
		global $wpdb;
		$row = self::staff_row( $request );
		if ( is_wp_error( $row ) ) {
			return $row;
		}
		if ( $row->state === 'closed' || $row->expires_at <= gmdate( 'Y-m-d H:i:s' ) ) {
			return self::error( 'closed', __( 'Diese Unterhaltung ist geschlossen oder abgelaufen.', 'psource-chat' ), 409 );
		}
		$result = $wpdb->query( $wpdb->prepare(
			'UPDATE ' . self::table( 'sessions' ) . " SET assigned_user_id=%d,state='active',updated_at=%s WHERE id=%d AND assigned_user_id=0 AND state='waiting' AND expires_at>%s",
			get_current_user_id(), gmdate( 'Y-m-d H:i:s' ), $row->id, gmdate( 'Y-m-d H:i:s' )
		) );
		if ( $result === false ) {
			return self::db_error();
		}
		$row = self::staff_row( $request );
		if ( is_wp_error( $row ) ) {
			return $row;
		}
		if ( (int) $row->assigned_user_id !== get_current_user_id() ) {
			return self::error( 'claimed', __( 'Ein anderer Mitarbeiter hat diese Unterhaltung bereits übernommen.', 'psource-chat' ), 409 );
		}
		return self::payload( $row, 0, true );
	}

	public static function reply( $request ) {
		$row = self::staff_row( $request );
		if ( is_wp_error( $row ) ) {
			return $row;
		}
		if ( (int) $row->assigned_user_id !== get_current_user_id() || $row->state !== 'active' || $row->expires_at <= gmdate( 'Y-m-d H:i:s' ) ) {
			return self::error( 'claim', __( 'Bitte die offene Unterhaltung zuerst übernehmen. Nur der zuständige Mitarbeiter kann antworten.', 'psource-chat' ), 409 );
		}
		$input = self::message_input( $request );
		if ( is_wp_error( $input ) ) {
			return $input;
		}
		$result = self::insert_message( $row, $input, 'staff', get_current_user_id() );
		return is_wp_error( $result ) ? $result : self::payload( $row, absint( $request->get_param( 'since' ) ), true );
	}

	public static function resolve( $request ) {
		$row = self::staff_row( $request );
		if ( is_wp_error( $row ) ) {
			return $row;
		}
		if ( (int) $row->assigned_user_id !== get_current_user_id() ) {
			return self::error( 'claim', __( 'Nur der zuständige Mitarbeiter kann die Unterhaltung schließen.', 'psource-chat' ), 403 );
		}
		return self::close_row( $row );
	}

	private static function notify( $row ) {
		global $wpdb;
		$ids = array_filter( array_map( 'absint', explode( ',', trim( $row->recipient_ids, ',' ) ) ) );
		$url = add_query_arg( array( 'ps-chat-support-agent' => '1', 'session' => $row->public_id ), get_home_url( $row->origin_blog_id, '/' ) );
		$subject = sprintf( __( 'Neue Support-Chat-Anfrage: %s', 'psource-chat' ), $row->category_name );
		$body = sprintf(
			__( "Eine private Support-Anfrage wartet auf Übernahme.\nKategorie: %1\$s\nUrsprungs-Site: %2\$s\n\nChat öffnen und übernehmen:\n%3\$s\n\nDie Übernahme erfordert eine Anmeldung mit einem berechtigten Mitarbeiterkonto. Ein Linkaufruf allein übernimmt den Chat nicht.", 'psource-chat' ),
			$row->category_name, get_home_url( $row->origin_blog_id, '/' ), $url
		);
		$failed = false;
		foreach ( $ids as $id ) {
			$user = get_userdata( $id );
			if ( ! $user || ! wp_mail( $user->user_email, $subject, $body ) ) {
				$failed = true;
			}
		}
		$status = $failed || ! $ids ? 'failed' : 'sent';
		if ( $wpdb->update( self::table( 'sessions' ), array( 'notification_status' => $status ), array( 'id' => $row->id ) ) === false ) {
			return self::db_error();
		}
		if ( $status === 'failed' ) {
			error_log( 'PS Chat Support: notification delivery failed for conversation ' . $row->public_id );
			return self::error( 'mail', __( 'Die Anfrage ist gespeichert, aber die E-Mail-Benachrichtigung konnte nicht vollständig versendet werden.', 'psource-chat' ), 502 );
		}
		return true;
	}

	public static function renotify( $request ) {
		$row = self::staff_row( $request );
		if ( is_wp_error( $row ) ) {
			return $row;
		}
		if ( $row->state !== 'waiting' || $row->expires_at <= gmdate( 'Y-m-d H:i:s' ) ) {
			return self::error( 'closed', __( 'Nur wartende Anfragen können erneut angekündigt werden.', 'psource-chat' ), 409 );
		}
		$rate = self::rate_limit( 'notify:' . $row->id, 1, 5 * MINUTE_IN_SECONDS );
		if ( is_wp_error( $rate ) ) {
			return $rate;
		}
		$result = self::notify( $row );
		return is_wp_error( $result ) ? $result : array( 'sent' => true );
	}

	public static function cleanup() {
		global $wpdb;
		$sessions = self::table( 'sessions' );
		$messages = self::table( 'messages' );
		$now = gmdate( 'Y-m-d H:i:s' );
		$ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT id FROM $sessions WHERE network_id=%d AND delete_after<%s ORDER BY id LIMIT 500",
			get_current_network_id(), $now
		) );
		if ( $wpdb->last_error ) {
			self::db_error();
			return;
		}
		if ( $ids ) {
			$ids = implode( ',', array_map( 'absint', $ids ) );
			if ( $wpdb->query( "DELETE FROM $messages WHERE session_id IN ($ids)" ) === false
				|| $wpdb->query( "DELETE FROM $sessions WHERE id IN ($ids)" ) === false ) {
				self::db_error();
			}
		}
		if ( $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . self::table( 'limits' ) . ' WHERE expires_at<%s', $now ) ) === false ) {
			self::db_error();
		}
	}

	public static function validate_settings( $input, $network = false ) {
		return class_exists( 'PSource_Support_Chat_Settings' ) ? PSource_Support_Chat_Settings::validate( $input, $network )
			: self::error( 'unavailable', __( 'PS Support ist nicht aktiv.', 'psource-chat' ), 503 );
	}

	private static function assets( $staff = false ) {
		$root = dirname( __DIR__ );
		$url = plugin_dir_url( $root . '/psource-chat.php' );
		wp_enqueue_style( 'ps-chat-support', $url . 'css/psource-chat-support.css', array(), filemtime( $root . '/css/psource-chat-support.css' ) );
		wp_enqueue_script( 'ps-chat-support', $url . 'js/psource-chat-support.js', array(), filemtime( $root . '/js/psource-chat-support.js' ), true );
		wp_localize_script( 'ps-chat-support', 'psChatSupport', array(
			'url' => esc_url_raw( rest_url( 'psource-chat/v1/support/' ) ),
			'nonce' => $staff ? wp_create_nonce( 'wp_rest' ) : '',
			'userId' => $staff ? get_current_user_id() : 0, 'staff' => $staff,
			'strings' => array(
				'category' => __( 'Thema', 'psource-chat' ), 'general' => __( 'Allgemein', 'psource-chat' ),
				'name' => __( 'Name', 'psource-chat' ), 'email' => __( 'E-Mail', 'psource-chat' ),
				'optional' => __( 'optional', 'psource-chat' ), 'message' => __( 'Nachricht', 'psource-chat' ),
				'send' => __( 'Senden', 'psource-chat' ), 'start' => __( 'Support anfragen', 'psource-chat' ),
				'close' => __( 'Unterhaltung beenden', 'psource-chat' ), 'hide' => __( 'Fenster minimieren', 'psource-chat' ),
				'newChat' => __( 'Neue Anfrage', 'psource-chat' ),
				'waiting' => __( 'Anfrage gesendet – wir warten auf eine Antwort.', 'psource-chat' ),
				'active' => __( 'Dein Ansprechpartner:', 'psource-chat' ), 'closed' => __( 'Diese Unterhaltung ist beendet.', 'psource-chat' ),
				'expired' => __( 'Der Browser-Zugang ist abgelaufen. Bitte eine neue Anfrage starten.', 'psource-chat' ),
				'mailFailed' => __( 'Die Anfrage ist gespeichert, aber die E-Mail-Benachrichtigung ist fehlgeschlagen. Der Support sieht sie weiterhin im Posteingang.', 'psource-chat' ),
				'error' => __( 'Die Anfrage ist fehlgeschlagen. Bitte erneut versuchen.', 'psource-chat' ),
				'empty' => __( 'Noch keine Support-Anfragen.', 'psource-chat' ), 'choose' => __( 'Eine Unterhaltung auswählen.', 'psource-chat' ),
				'claim' => __( 'Chat übernehmen', 'psource-chat' ), 'notify' => __( 'Benachrichtigung erneut senden', 'psource-chat' ),
				'resolve' => __( 'Chat abschließen', 'psource-chat' ), 'site' => __( 'Ursprungs-Site', 'psource-chat' ),
				'claimedOther' => __( 'Diese Unterhaltung wird von einem anderen Mitarbeiter betreut.', 'psource-chat' ),
				'refresh' => __( 'Aktualisieren', 'psource-chat' ), 'notified' => __( 'Benachrichtigung versendet.', 'psource-chat' ),
				'botHelp' => __( 'Hat das geholfen?', 'psource-chat' ),
				'botSolved' => __( 'Ja, erledigt', 'psource-chat' ),
				'botDone' => __( 'Prima! Es wurde keine Support-Anfrage erstellt.', 'psource-chat' ),
				'botHuman' => __( 'Mit dem Support sprechen', 'psource-chat' ),
				'botBack' => __( 'Anderes Thema wählen', 'psource-chat' ),
				'botSummary' => __( 'Bisherige Selbsthilfe', 'psource-chat' ),
				'botDetails' => __( 'Mein Anliegen:', 'psource-chat' ),
				'botHandoff' => __( 'Bitte ergänze dein Anliegen und sende die Anfrage an unser Team.', 'psource-chat' ),
				'messageTooLong' => __( 'Bitte kürze die Nachricht auf höchstens 4000 Zeichen.', 'psource-chat' ),
			),
		) );
	}

	public static function frontend_assets() {
		if ( isset( $_GET['ps-chat-support-agent'] ) ) {
			return;
		}

		$context = self::context();
		if ( ! is_wp_error( $context ) ) {
			self::assets();
		}
	}

	public static function admin_assets() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'ps-chat-support-inbox' ) {
			self::assets( true );
		}
	}

	public static function deactivate( $network_wide ) {
		if ( $network_wide && is_multisite() ) {
			switch_to_blog( get_main_site_id() );
			wp_clear_scheduled_hook( 'ps_chat_support_cleanup' );
			restore_current_blog();
		} else {
			wp_clear_scheduled_hook( 'ps_chat_support_cleanup' );
		}
	}

	public static function shortcode() {
		$context = self::context();
		if ( is_wp_error( $context ) ) {
			return '';
		}
		if ( self::$embedded ) {
			return '';
		}
		self::$embedded = true;
		self::assets();
		return '<div class="ps-chat-support" data-ps-support="guest" data-label="' . esc_attr( $context['site']['label'] ) . '"></div>';
	}

	public static function floating_widget() {
		if ( isset( $_GET['ps-chat-support-agent'] ) ) {
			return;
		}
		$context = self::context();
		if ( ! self::$embedded && ! is_wp_error( $context ) && $context['site']['placement'] === 'floating' ) {
			echo '<div class="ps-chat-support ps-chat-support-floating" data-ps-support="guest" data-label="' . esc_attr( $context['site']['label'] ) . '"></div>';
		}
	}

	public static function inbox_page() {
		if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'Bitte anmelden.', 'psource-chat' ) );
		}
		self::assets( true );
		echo '<div class="wrap"><h1>' . esc_html__( 'Private Supportchats', 'psource-chat' ) . '</h1><div class="ps-chat-support ps-chat-support-agent" data-ps-support="staff"></div></div>';
	}

	public static function register_widget() {
		require_once __DIR__ . '/class-psource-chat-support-widget.php';
		register_widget( 'PSource_Chat_Support_Widget' );
	}

	public static function agent_page() {
		if ( ! isset( $_GET['ps-chat-support-agent'] ) ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			auth_redirect();
		}
		$id = isset( $_GET['session'] ) && is_string( $_GET['session'] ) ? sanitize_text_field( wp_unslash( $_GET['session'] ) ) : '';
		$request = new WP_REST_Request();
		$request->set_param( 'id', $id );
		$row = self::staff_row( $request );
		if ( is_wp_error( $row ) ) {
			wp_die( esc_html( $row->get_error_message() ), '', array( 'response' => 403 ) );
		}
		nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow' );
		self::assets( true );
		require dirname( __DIR__ ) . '/templates/psource-chat-support-agent.php';
		exit;
	}
}
