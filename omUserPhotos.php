<?php
/*
Plugin Name: omUserPhotos
Plugin URI: Prida moznost prodat autorum fotografii
Description: Fotografie autoru
Version: 1.0
Author: Roman Ozana
Author URI:
*/

// Define paths and variables
define('WP_USER_AVATAR_ABSPATH', trailingslashit(str_replace('\\', '/', WP_PLUGIN_DIR . '/' . basename(__DIR__))));
define('WP_USER_AVATAR_URLPATH', trailingslashit(plugins_url(__DIR__)));

class omUserPhotos {

	const META = 'user_photo';

	public static function init() {
		return new self;
	}

	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'media_upload_scripts'));
		add_action('show_user_profile', array($this, 'user_profile'));
		add_action('edit_user_profile', array($this, 'user_profile'));
		add_action('personal_options_update', array($this, 'user_update'));
		add_action('edit_user_profile_update', array($this, 'user_update'));
	}

	/**
	 * Init upload scripts
	 */
	public function media_upload_scripts() {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		if (function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		}
	}

	/**
	 * User profile edit
	 *
	 * @param $user
	 */
	public function user_profile($user) {
		if (current_user_can('upload_files') === false) return;
		require __DIR__ . '/user_profile.phtml';
	}

	/**
	 * Save changes
	 *
	 * @param $user_id
	 */
	public function user_update($user_id) {
		if (current_user_can('upload_files') === false) return;
		update_user_meta($user_id, self::META, (isset($_POST['omUserPhoto']) ? $_POST['omUserPhoto'] : ''));
	}

	/**
	 * @param $user_id
	 * @return mixed
	 */
	public static function hasUserImage($user_id) {
		return self::getUserPhotoMetaValue($user_id);
	}

	/**
	 * @param $user_id
	 * @param string $size thumbnail, medium, large or full
	 * @param bool $icon
	 * @param string $attr
	 * @return string
	 *
	 */
	public static function getUserPhotoImg($user_id, $size = 'thumbnail', $icon = false, $attr = '') {
		$image_id = get_user_meta($user_id, self::META, true);
		return wp_get_attachment_image($image_id, $size, $icon, $attr);
	}

	public static function getUserPhotoAvatar($author_id, $alt = null) {
		if (omUserPhotos::hasUserImage($author_id)) {
			return omUserPhotos::getUserPhotoImg(
				$author_id, '32x32', false, array('class' => 'avatar avatar-32 photo', 'alt' => esc_attr($alt))
			);
		} else {
			return get_avatar($author_id, 32, '', $alt);
		}
	}

	/**
	 * Get user photo meta value (return ID of image)
	 *
	 * @param $user_id
	 * @return mixed
	 */
	public static function getUserPhotoMetaValue($user_id) {
		return get_user_meta($user_id, self::META, true);
	}

	/**
	 * Return image of post author
	 *
	 * @param string $size
	 * @param bool $icon
	 * @param string $attr
	 * @return string
	 */
	public static function getAuthorImg($size = 'thumbnail', $icon = false, $attr = '') {
		$user_id = get_the_author_meta('ID');
		return self::getUserPhotoImg($user_id, $size, $icon, $attr);
	}

	/**
	 * Check if current author has image
	 *
	 * @return mixed
	 */
	public static function hasAuthorImage() {
		$user_id = get_the_author_meta('ID');
		return self::hasUserImage($user_id);
	}

}

omUserPhotos::init();