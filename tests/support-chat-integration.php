<?php
/**
 * Run on a disposable WordPress/ClassicPress installation using:
 * PS_CHAT_SUPPORT_TEST=1 wp eval-file tests/support-chat-integration.php
 * Both plugins must be active. All fixtures and changed options are restored.
 */
if ( ! defined( 'ABSPATH' ) || getenv( 'PS_CHAT_SUPPORT_TEST' ) !== '1' ) {
	throw new RuntimeException( 'Explicit PS_CHAT_SUPPORT_TEST=1 and WordPress bootstrap required.' );
}
if ( ! class_exists( 'PSource_Chat_Support' ) || ! function_exists( 'psource_support_get_ticket_categories' ) ) {
	throw new RuntimeException( 'Activate PS Chat and PS Support before running this test.' );
}

global $wpdb;
$original_site = get_option( PSource_Chat_Support::OPTION, null );
$original_network = get_site_option( PSource_Chat_Support::OPTION, null );
$users = array();
$session_ids = array();
$database_ids = array();
$cookies = $_COOKIE;
$original_user = get_current_user_id();
$original_address = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null;
$passed = array();
$mail = array();
$mail_fail = false;
$capture = function ( $result, $attributes ) use ( &$mail, &$mail_fail ) {
	$mail[] = $attributes;
	return ! $mail_fail;
};
add_filter( 'pre_wp_mail', $capture, 999, 2 );
$check = function ( $condition, $message ) use ( &$passed ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
	$passed[] = $message;
};
$call = function ( $method, $path, $body = array(), $user = 0, $origin = null ) {
	wp_set_current_user( $user );
	$request = new WP_REST_Request( $method, '/psource-chat/v1/support/' . $path );
	$request->set_header( 'origin', $origin === null ? home_url() : $origin );
	foreach ( $body as $key => $value ) {
		$request->set_param( $key, $value );
	}
	return rest_do_request( $request );
};
$sessions_table = $wpdb->base_prefix . 'ps_chat_support_sessions';
$messages_table = $wpdb->base_prefix . 'ps_chat_support_messages';
$limits_table = $wpdb->base_prefix . 'ps_chat_support_limits';
$test_address = 'ps-chat-test-' . wp_generate_password( 12, false );
$_SERVER['REMOTE_ADDR'] = $test_address;
$failure = null;
try {
	foreach ( array( 'first', 'second', 'outsider' ) as $suffix ) {
		$id = wp_insert_user( array(
			'user_login' => 'pschat_test_' . $suffix . '_' . wp_generate_password( 8, false ),
			'user_pass' => wp_generate_password( 40 ), 'user_email' => $suffix . '@example.invalid',
			'role' => $suffix === 'outsider' ? 'subscriber' : 'editor',
		) );
		if ( is_wp_error( $id ) ) {
			throw new RuntimeException( $id->get_error_message() );
		}
		$users[] = $id;
	}
	$settings = array_merge( PSource_Chat_Support::settings(), array(
		'mode' => is_multisite() ? 'network' : 'local', 'fallback_users' => array( $users[0], $users[1] ),
		'require_name' => false, 'require_email' => false, 'intro_text' => '',
	) );
	update_option( PSource_Chat_Support::OPTION, $settings );
	if ( is_multisite() ) {
		update_site_option( PSource_Chat_Support::OPTION, $settings );
	}
	$input_settings = array_merge( $settings, array( 'fallback_users' => implode( ',', $settings['fallback_users'] ) ) );
	$check( ! is_wp_error( PSource_Chat_Support::validate_settings( $input_settings, is_multisite() ) ), 'Valid team and policy settings accepted' );
	$check( is_wp_error( PSource_Chat_Support::validate_settings( array_merge( $input_settings, array( 'fallback_users' => array( $users[2] ) ) ), is_multisite() ) ), 'Frontend-only user cannot be selected as fallback staff' );
	$pool = PSource_Support_Chat_Settings::staff_ids();
	$check( in_array( $users[0], $pool, true ) && ! in_array( $users[2], $pool, true ), 'Support roles included and ordinary frontend users excluded from staff pool' );
	$prefix = 'pschat_search_' . wp_generate_password( 8, false );
	for ( $i = 0; $i < 23; $i++ ) {
		$id = wp_insert_user( array( 'user_login' => $prefix . '_' . $i, 'user_pass' => wp_generate_password( 40 ), 'role' => 'editor' ) );
		if ( is_wp_error( $id ) ) {
			throw new RuntimeException( $id->get_error_message() );
		}
		$users[] = $id;
	}
	$results = PSource_Support_Chat_Settings::staff_search( 0, $prefix );
	$check( count( $results['users'] ) === 20 && $results['more'], 'Staff search returns at most 20 results and signals additional matches' );
	$results = PSource_Support_Chat_Settings::staff_search( 0, get_userdata( $users[3] )->user_login );
	$check( count( $results['users'] ) === 1 && (int) $results['users'][0]['id'] === $users[3], 'Targeted staff search finds a named support member' );
	$results = PSource_Support_Chat_Settings::staff_search( 0, get_userdata( $users[2] )->user_login );
	$check( ! $results['users'], 'Even exact searches never offer frontend-only users' );
	$selected_staff = PSource_Chat_Support::validate_settings( array_merge( $input_settings, array( 'fallback_users' => array( (string) $users[0], (string) $users[1], (string) $users[0] ) ) ), is_multisite() );
	$check( ! is_wp_error( $selected_staff ) && $selected_staff['fallback_users'] === array( $users[0], $users[1] ), 'Named staff checkbox selection saves multiple users and removes duplicates' );
	$check( is_wp_error( PSource_Chat_Support::validate_settings( array_merge( $input_settings, array( 'fallback_users' => array( array( 'invalid' ) ) ) ), is_multisite() ) ), 'Malformed staff checkbox input rejected explicitly' );
	foreach ( array(
		array( 'session_hours' => '0' ), array( 'session_hours' => '2161' ),
		array( 'retention_days' => '366' ), array( 'retention_days' => '1' ),
		array( 'fallback_users' => '1,invalid' ), array( 'require_email' => array( 1 ) ),
		array( 'label' => '<b></b>' ), array( 'mode' => 'public' ),
		array( 'intro_text' => array( 'invalid' ) ), array( 'intro_text' => str_repeat( 'a', 2001 ) ),
		array( 'allow_subsite_chat' => array( 'invalid' ) ),
	) as $invalid ) {
		$check( is_wp_error( PSource_Chat_Support::validate_settings( array_merge( $input_settings, $invalid ), is_multisite() ) ), 'Invalid policy rejected: ' . key( $invalid ) );
	}
	$cookie_name = 'ps_chat_support_' . get_current_network_id() . '_' . get_current_blog_id();
	unset( $_COOKIE[ $cookie_name ] );
	$check( $call( 'GET', 'config' )->get_status() === 200, 'Public policy available without account' );
	if ( class_exists( 'PSource_Support_Bot' ) ) {
		$bot = $call( 'GET', 'config' )->get_data()['bot'];
		$check( is_array( $bot ) && isset( $bot['enabled_chat'], $bot['enabled_tickets'], $bot['topics'] ), 'Chat policy exposes shared support-bot configuration' );
	}
	$check( $call( 'GET', 'config' )->get_data()['intro_text'] === '', 'Visitor introduction is empty by default' );
	$with_intro = PSource_Chat_Support::validate_settings( array_merge( $input_settings, array( 'intro_text' => "Eigener Ablauf\n<b>Antwortzeiten</b>" ) ), is_multisite() );
	$check( ! is_wp_error( $with_intro ) && $with_intro['intro_text'] === "Eigener Ablauf\nAntwortzeiten", 'Custom introduction preserves lines and strips HTML' );
	$check( $call( 'GET', 'session' )->get_status() === 401, 'No cookie cannot read any session' );
	$first = array( 'message' => 'Private fixture message', 'client_id' => 'fixture_message_1', 'name' => '', 'email' => '', 'category_id' => 0 );
	$check( $call( 'POST', 'start', $first, 0, 'https://other.invalid' )->get_status() === 403, 'Cross-origin creation rejected' );
	$check( $call( 'POST', 'start', array_merge( $first, array( 'category_id' => -1 ) ) )->get_status() === 400, 'Invalid category rejected' );
	$stale_settings = array_merge( $settings, array( 'fallback_users' => array( $users[2] ) ) );
	is_multisite() ? update_site_option( PSource_Chat_Support::OPTION, $stale_settings ) : update_option( PSource_Chat_Support::OPTION, $stale_settings );
	$check( $call( 'POST', 'start', $first )->get_status() === 503, 'Legacy frontend-only fallback selection cannot receive new private chats' );
	is_multisite() ? update_site_option( PSource_Chat_Support::OPTION, $settings ) : update_option( PSource_Chat_Support::OPTION, $settings );
	$response = $call( 'POST', 'start', $first );
	$check( $response->get_status() === 200, 'Anonymous private session created' . ( $response->get_status() === 200 ? '' : ': ' . wp_json_encode( $response->get_data() ) ) );
	$data = $response->get_data();
	$session_ids[] = $data['id'];
	$database_ids[] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $sessions_table WHERE public_id=%s", $data['id'] ) );
	$guest_a = $_COOKIE[ $cookie_name ];
	$check( $call( 'GET', 'session', array(), 0, 'https://other.invalid' )->get_status() === 403, 'Cross-origin credentialed reads rejected even with valid guest cookie' );
	$check( $data['state'] === 'waiting' && count( $data['messages'] ) === 1, 'First message stored and waiting, not auto-claimed' );
	$check( count( $mail ) === 2, 'Exactly one notification per configured team member' );
	$check( strpos( $mail[0]['message'], 'Private fixture message' ) === false && strpos( $mail[0]['message'], 'ps-chat-support-agent' ) !== false, 'Mail contains staff link, not confidential message' );
	$check( ! isset( $data['guest_hash'], $data['email'], $data['recipient_ids'] ), 'Guest response does not expose authentication or routing secrets' );
	$retry = $call( 'POST', 'start', $first )->get_data();
	$check( $retry['id'] === $data['id'] && count( $retry['messages'] ) === 1 && count( $mail ) === 2, 'Retry deduplicates first message and notification' );
	$check( $call( 'GET', 'conversation/' . $data['id'], array(), $users[2] )->get_status() === 403, 'Unrelated logged-in user cannot read conversation' );
	$check( count( $call( 'GET', 'inbox', array(), $users[2] )->get_data() ) === 0, 'Unrelated user has no inbox leakage' );
	$check( $call( 'GET', 'conversation/' . $data['id'], array(), $users[0] )->get_data()['state'] === 'waiting', 'Reading email target does not claim chat' );
	$check( $call( 'POST', 'reply/' . $data['id'], $first, $users[0] )->get_status() === 409, 'Unclaimed staff cannot reply' );
	$check( $call( 'POST', 'claim/' . $data['id'], array(), $users[0] )->get_status() === 200, 'First team member explicitly claims session' );
	$check( $call( 'POST', 'claim/' . $data['id'], array(), $users[1] )->get_status() === 409, 'Second team member cannot steal claimed session' );
	$reply = array( 'message' => 'Private staff reply', 'client_id' => 'fixture_staff_reply' );
	$check( $call( 'POST', 'reply/' . $data['id'], $reply, $users[1] )->get_status() === 409, 'Non-owner team member cannot reply' );
	$check( $call( 'POST', 'reply/' . $data['id'], $reply, $users[0] )->get_status() === 200, 'Assigned staff can reply' );
	$check( count( $call( 'GET', 'session' )->get_data()['messages'] ) === 2, 'Guest receives staff reply' );
	$check( $call( 'POST', 'message', array_merge( $reply, array( 'message' => 'Collision' ) ) )->get_status() === 409, 'Client ID cannot overwrite another author message' );
	$_COOKIE[ $cookie_name ] = $data['id'] . '.' . str_repeat( '0', 64 );
	$check( $call( 'GET', 'session' )->get_status() === 401, 'Known session ID with forged token rejected' );
	unset( $_COOKIE[ $cookie_name ] );
	$mail_fail = true;
	$other = $call( 'POST', 'start', array_merge( $first, array( 'client_id' => 'fixture_message_2' ) ) )->get_data();
	$session_ids[] = $other['id'];
	$database_ids[] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $sessions_table WHERE public_id=%s", $other['id'] ) );
	$guest_b = $_COOKIE[ $cookie_name ];
	$check( $other['id'] !== $data['id'] && count( $other['messages'] ) === 1, 'Second browser has a separate private history' );
	$check( $other['notification_status'] === 'failed', 'Mail failure explicitly visible while preserving session' );
	$mail_fail = false;
	$check( $call( 'POST', 'notify/' . $other['id'], array(), $users[1] )->get_status() === 200, 'Authorized team can retry failed notification' );
	$check( $call( 'POST', 'notify/' . $other['id'], array(), $users[1] )->get_status() === 429, 'Notification retry is rate-limited' );
	$_COOKIE[ $cookie_name ] = $guest_a;
	$check( $call( 'POST', 'close' )->get_status() === 200, 'Guest can close own session' );
	$check( $call( 'POST', 'message', array( 'message' => 'Late', 'client_id' => 'fixture_after_close' ) )->get_status() === 409, 'Closed sessions cannot receive new messages' );
	$_COOKIE[ $cookie_name ] = $guest_b;
	$wpdb->update( $sessions_table, array( 'expires_at' => gmdate( 'Y-m-d H:i:s', time() - 10 ) ), array( 'public_id' => $other['id'] ) );
	$check( $call( 'GET', 'session' )->get_status() === 401, 'Expired guest access rejected' );
	$check( $call( 'POST', 'claim/' . $other['id'], array(), $users[1] )->get_status() === 409, 'Expired session cannot be claimed' );
	$db_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $sessions_table WHERE public_id=%s", $other['id'] ) );
	$wpdb->update( $sessions_table, array( 'delete_after' => gmdate( 'Y-m-d H:i:s', time() - 10 ) ), array( 'id' => $db_id ) );
	PSource_Chat_Support::cleanup();
	$check( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $sessions_table WHERE id=%d", $db_id ) ) &&
		! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $messages_table WHERE session_id=%d", $db_id ) ), 'Retention removes session and messages together' );
	$check( count( $call( 'GET', 'session' )->get_data() ) > 0, 'Expired session returns explicit error payload' );
	if ( is_multisite() ) {
		$settings['mode'] = 'disabled';
		update_site_option( PSource_Chat_Support::OPTION, $settings );
		$check( $call( 'GET', 'config' )->get_status() === 403, 'Network switch disables new public access centrally' );
		$settings['mode'] = 'network';
		$settings['allow_subsite_chat'] = false;
		update_site_option( PSource_Chat_Support::OPTION, $settings );
		$other_sites = get_sites( array( 'network_id' => get_current_network_id(), 'site__not_in' => array( get_current_blog_id() ), 'number' => 1 ) );
		if ( $other_sites ) {
			$other_id = (int) $other_sites[0]->blog_id;
			$other_settings = get_blog_option( $other_id, PSource_Chat_Support::OPTION, null );
			switch_to_blog( $other_id );
			try {
				update_option( PSource_Chat_Support::OPTION, array_merge( $settings, array( 'mode' => 'network' ) ) );
				$check( $call( 'GET', 'config' )->get_status() === 403, 'Network permission blocks subsite even when locally enabled' );
				$settings['allow_subsite_chat'] = true;
				update_site_option( PSource_Chat_Support::OPTION, $settings );
				$check( $call( 'GET', 'config' )->get_status() === 200, 'Network permission and local opt-in enable subsite chat' );
				update_option( PSource_Chat_Support::OPTION, array_merge( $settings, array( 'mode' => 'disabled' ) ) );
				$check( $call( 'GET', 'config' )->get_status() === 403, 'Local opt-out hides chat despite network permission' );
				update_option( PSource_Chat_Support::OPTION, array_merge( $settings, array( 'mode' => 'local' ) ) );
				$check( $call( 'GET', 'config' )->get_status() === 200, 'Own-site chat does not depend on ticket activation' );
			} finally {
				$other_settings === null ? delete_option( PSource_Chat_Support::OPTION ) : update_option( PSource_Chat_Support::OPTION, $other_settings );
				restore_current_blog();
			}
		}
		$site_settings = $settings;
		$site_settings['mode'] = 'disabled';
		update_option( PSource_Chat_Support::OPTION, $site_settings );
		if ( get_current_blog_id() === PSource_Support_Chat_Settings::central_blog_id() ) {
			$check( $call( 'GET', 'config' )->get_status() === 200, 'Configured central support site automatically uses enabled network chat' );
		}
	}
} catch ( Throwable $error ) {
	$failure = $error;
} finally {
	remove_filter( 'pre_wp_mail', $capture, 999 );
	foreach ( $session_ids as $public_id ) {
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $sessions_table WHERE public_id=%s", $public_id ) );
		if ( $id ) {
			$wpdb->delete( $messages_table, array( 'session_id' => $id ) );
			$wpdb->delete( $sessions_table, array( 'id' => $id ) );
		}
	}
	require_once ABSPATH . 'wp-admin/includes/user.php';
	foreach ( $database_ids as $id ) {
		foreach ( array( 'guest:0', 'staff:' . $users[0], 'staff:' . $users[1] ) as $author ) {
			foreach ( array( floor( time() / 60 ), floor( time() / 60 ) - 1 ) as $window ) {
				$wpdb->delete( $limits_table, array( 'rate_key' => hash_hmac( 'sha256', get_current_network_id() . ':message:' . $id . ':' . $author . ':' . $window, wp_salt( 'nonce' ) ) ) );
			}
		}
		foreach ( array( floor( time() / 300 ), floor( time() / 300 ) - 1 ) as $window ) {
			$wpdb->delete( $limits_table, array( 'rate_key' => hash_hmac( 'sha256', get_current_network_id() . ':notify:' . $id . ':' . $window, wp_salt( 'nonce' ) ) ) );
		}
	}
	foreach ( array( floor( time() / HOUR_IN_SECONDS ), floor( time() / HOUR_IN_SECONDS ) - 1 ) as $window ) {
		$wpdb->delete( $limits_table, array( 'rate_key' => hash_hmac( 'sha256', get_current_network_id() . ':start:' . $test_address . ':' . $window, wp_salt( 'nonce' ) ) ) );
	}
	foreach ( $users as $id ) {
		if ( is_multisite() ) {
			wpmu_delete_user( $id );
		} else {
			wp_delete_user( $id );
		}
	}
	$original_site === null ? delete_option( PSource_Chat_Support::OPTION ) : update_option( PSource_Chat_Support::OPTION, $original_site );
	$original_network === null ? delete_site_option( PSource_Chat_Support::OPTION ) : update_site_option( PSource_Chat_Support::OPTION, $original_network );
	$_COOKIE = $cookies;
	if ( $original_address === null ) {
		unset( $_SERVER['REMOTE_ADDR'] );
	} else {
		$_SERVER['REMOTE_ADDR'] = $original_address;
	}
	wp_set_current_user( $original_user );
}
foreach ( $passed as $message ) {
	WP_CLI::log( 'PASS ' . $message );
}
if ( $failure ) {
	WP_CLI::error( $failure->getMessage() );
}
WP_CLI::success( count( $passed ) . ' supportchat integration checks passed; fixtures restored.' );
