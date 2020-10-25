<?php

/*
Plugin Name: BuddyBoss VideoChat
Plugin URI: http://github.com/owenl131
description: Video Chat using Twilio API within BuddyBoss Platform
Version: 1.0
Author: Owen Leong
Author URI: http://github.com/owenl131
License: GPL2
*/

function bbvideo_settings_init() {
	register_setting('bbvideo', 'bbvideo_options');
	add_settings_section(
		'bbvideo_credentials',
		'Some text',
		'bbvideo_credentials_callback',
		'bbvideo'
	);
	add_settings_field(
		'bbvideo_account_sid',
		'Twilio Account SID',
		'bbvideo_account_sid_callback',
		'bbvideo',
		'bbvideo_credentials',
		array(
            'label_for'         => 'bbvideo_account_sid',
            'class'             => 'bbvideo_row',
		)
	);
	add_settings_field(
		'bbvideo_api_key',
		'API Key',
		'bbvideo_api_key_callback',
		'bbvideo',
		'bbvideo_credentials',
		array(
            'label_for'         => 'bbvideo_api_key',
            'class'             => 'bbvideo_row',
		)
	);
	add_settings_field(
		'bbvideo_api_secret',
		'API Secret',
		'bbvideo_api_secret_callback',
		'bbvideo',
		'bbvideo_credentials',
		array(
            'label_for'         => 'bbvideo_api_secret',
            'class'             => 'bbvideo_row',
		)
	);
}
add_action('admin_init', 'bbvideo_settings_init');

function bbvideo_credentials_callback( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">
		<?php echo 'API settings for Twilio' ?>
	</p>
    <?php
}

function bbvideo_api_key_callback( $args ) {
	$options = get_option('bbvideo_options');
	?>
	<input id="<?php echo esc_attr($args['label_for']); ?>"
		name="bbvideo_options[<?php echo esc_attr($args['label_for']); ?>]"
		type='text' value='<?php echo esc_attr( $options['bbvideo_api_key'] ); ?>'
		>
	<?php 
}

function bbvideo_api_secret_callback( $args ) {
	$options = get_option('bbvideo_options');
	?>
	<input id="<?php echo esc_attr($args['label_for']); ?>"
		name="bbvideo_options[<?php echo esc_attr($args['label_for']); ?>]"
		type='text' value='<?php echo esc_attr( $options['bbvideo_api_secret'] ); ?>'
		>
	<?php 
}

function bbvideo_account_sid_callback( $args ) {
	$options = get_option('bbvideo_options');
	?>
	<input id="<?php echo esc_attr($args['label_for']); ?>"
		name="bbvideo_options[<?php echo esc_attr($args['label_for']); ?>]"
		type='text' value='<?php echo esc_attr( $options['bbvideo_account_sid'] ); ?>'
		>
	<?php 
}

function bbvideo_options_page() {
	add_menu_page(
		'bbvideo',
		'BBVideo Options',
		'manage_options',
		'bbvideo',
		'bbvideo_options_page_html'
	);
}

add_action('admin_menu', 'bbvideo_options_page');

function bbvideo_options_page_html() {
	if (!current_user_can('manage_options')) {
		return;
	}
	if (isset($_GET['settings-updated'])) {
		add_settings_error('bbvideo_messages', 'bbvideo_message',
			'Settings Saved', 'updated');
	}
	settings_errors('bbvideo_messages');
	?>
	<div class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields('bbvideo');
			do_settings_sections('bbvideo');
			submit_button('Save Settings');
			?>
		</form>
	</div>
	<?php 
}


use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;


function videochat_enqueue() {
	if (bp_loggedin_user_id() != 0 && is_page('video-chat')) {
		wp_enqueue_script(
			'twilio-video-api', 
			'https://media.twiliocdn.com/sdk/js/video/releases/2.7.2/twilio-video.min.js',
			array( 'jquery' ));
         wp_enqueue_script(
			'twilio-video-client', 
			plugins_url( 'bundle.js' , __FILE__ ),
			array( 'twilio-video-api' ));
        wp_enqueue_style(
            'twilio-video-styles', 
			plugins_url( 'bundle.css' , __FILE__ ));
	}
}

add_action('wp_enqueue_scripts', 'videochat_enqueue');


function videochat_shortcode() {
	$identity = bp_get_loggedin_user_username(); 
	$userid = bp_loggedin_user_id(); // get userid
	if ($userid == 0) {
		echo "You need to be logged in to use this.";
		return;
	}
	if (!isset($_GET['id'])) {
		echo "Room ID must be provided";
		return;
	}
	$roomname = $_GET['id'];
	$options = get_option('bbvideo_options');
	// check that current user is part of this group
	$thread = new BP_Messages_Thread((int) $roomname);
	if (!BP_Messages_Thread::is_thread_recipient($thread->thread_id, $userid)) {
		echo "Invalid room";
		return;
	}
	// generate access token
	$twilioAccountSid = $options['bbvideo_account_sid'];
    $twilioApiKey = $options['bbvideo_api_key'];
    $twilioApiSecret = $options['bbvideo_api_secret'];
	$token = new AccessToken(
		$twilioAccountSid,
		$twilioApiKey,
		$twilioApiSecret, 
		3600,
		$identity);
	$videoGrant = new VideoGrant();
	$videoGrant->setRoom($roomname);
	$token->addGrant($videoGrant);
	?>
	<input name="room" id="video-room" type="hidden" value="<?php echo $roomname; ?>">
	<input name="token" id="video-user-token" type="hidden" value="<?php echo $token->toJWT(); ?>">
	<div id="local-media-div"></div>
	<div id="remote-media-div"></div>
    <div id="videochat-area"></div>
	<?php 
}

add_shortcode('videochat', 'videochat_shortcode');

?>