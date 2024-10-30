<?php
class Lickstats {
	const API_PATH = 'https://api.lickstats.com/v1';
	protected static $initiated = false;
	protected static $is_active = null;
	public static function init() {
		if (!self::$initiated) {
			self::init_hooks();
		}
	}

	/*
     * Is the plugin active?
	 */
	protected static function is_active(){
		if (!isset(self::$is_active)) {
			self::$is_active = get_option('lickstats_active', false);
		}
		return self::$is_active;
	}

	/*
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;
		if (!self::is_active()) { //Plugin is inactive
			return;
		}
		add_action('wp_footer', array('Lickstats', 'wp_footer_hook'), 100);
	}

	/*
     * Log in and load user data. Throws an exception if no results come back.
	 */
	public static function log_in($email, $password) {
        $response = wp_remote_post(self::API_PATH.'/login', array(
                'method' => 'POST',
                'timeout' => 15,
                'redirection' => 0,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => array('email' => $email, 'password' => stripcslashes($password)),
                'cookies' => array()
            )
        );
        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        } else {
            $response_code = $response['response']['code'];
            $response_body = $response['body'];
            if($response_code != 200){
                switch ($response_code){
                    case 400:
                        throw new \Exception('Invalid email or password');
                        break;
                    case 401:
                        throw new \Exception('Invalid credentials');
                        break;
                    default:
                        throw new \Exception('Could not log in');
                }
            } else {
                return json_decode($response_body);
            }
        }
	}

	/*
	 * Footer hook (enqueues the script only of the plugin is active and there is an account type/id associated to it)
	 */
	public static function wp_footer_hook() {
		if (!self::is_active()) {
			return;
		}
        $domains      = get_option('lickstats_crossdomains', array());
		$account_type = strtolower(get_option('lickstats_account_type', null));
		if (empty($account_type)) {
			return; //No account type? Most likely misconfigured, not loading.
		}
		$script  = '<script>';
		$script .= '(function(){var n,e;window.ls={accountId:'.json_encode(get_option('lickstats_account_id', null)).',crossdomains:'.json_encode($domains).',pending:[],push:function(){return this.pending.push(arguments)}},e=document.getElementsByTagName("script")[0],n=document.createElement("script"),n.async=!0,n.src="//cdn.lickstats.com/plugin-v1.js",e.parentNode.insertBefore(n,e)}).call(this);';
		$script .= '</script>';
		echo $script;
	}
}
