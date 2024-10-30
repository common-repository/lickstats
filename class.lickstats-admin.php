<?php

class Lickstats_Admin {
	const NONCE = 'lickstats-update-key';

	private static $initiated = false;

	public static function init() {
		if (!self::$initiated) {
			self::init_hooks();
		}
	}

	public static function init_hooks() {
		self::$initiated = true;
        add_action('admin_notices', array('Lickstats_Admin', 'admin_login_notice'));
		add_action('admin_menu', array('Lickstats_Admin', 'admin_menu'), 5); # Priority 5, so it’s called before Jetpack’s admin_menu.
	}

	public static function admin_menu() {
		if (class_exists('Jetpack')) {
			add_action('jetpack_admin_menu', array('Lickstats_Admin', 'load_menu'));
        } else {
			self::load_menu();
        }
	}

    public static function admin_login_notice(){
        global $pagenow;
        $url = admin_url('options-general.php?page=lickstats-config');
        $account_id = get_option('lickstats_account_id', null);
        $account_label = get_option('lickstats_account_label', null);
        if ($pagenow == 'plugins.php' and ($account_id == null or $account_label == null)) {
            update_option('lickstats_active', false, true);
            update_option('lickstats_account_id', null, true);
            update_option('lickstats_account_label', null, true);
            update_option('lickstats_account_type', null, true);
            update_option('lickstats_crossdomains', array(), true);
            echo '<div class="notice notice-warning">
                <p>Please <a href="' . $url . '">login</a> to enable the Lickstats plugin.</p>
            </div>';
        }
    }

	public static function admin_head() {
		if (!current_user_can('manage_options')) {
			return;
        }
	}

	public static function load_menu() {
		if (class_exists('Jetpack')) {
			$hook = add_submenu_page('jetpack', __('Lickstats', 'lickstats'), __('Lickstats', 'lickstats'), 'manage_options', 'lickstats-config', array('Lickstats_Admin', 'display_page'));
        } else {
			$hook = add_options_page(__('Lickstats', 'lickstats'), __('Lickstats', 'lickstats'), 'manage_options', 'lickstats-config', array('Lickstats_Admin', 'display_page'));
        }
	}

	public static function display_page(){
		require LICKSTATS_PLUGIN_DIR.'/views/index.php';
	}
}
