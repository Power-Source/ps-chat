<?php defined( 'ABSPATH' ) || exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex,nofollow">
	<title><?php esc_html_e( 'Private Supportchats', 'psource-chat' ); ?></title>
	<?php wp_head(); ?>
</head>
<body class="ps-chat-support-agent-page">
	<main>
		<h1><?php esc_html_e( 'Private Supportchats', 'psource-chat' ); ?></h1>
		<div class="ps-chat-support ps-chat-support-agent" data-ps-support="staff" data-session="<?php echo esc_attr( $id ); ?>"></div>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
