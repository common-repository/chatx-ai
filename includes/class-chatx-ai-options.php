<?php
/**
 * A class for the CRUD functions of the WP Options API.
 *
 * @link       https://chatx.ai
 * @since      1.0.0
 *
 * @package    Chatx_Ai
 * @subpackage Chatx_Ai/includes
 */
abstract class Chatx_Ai_Options {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * An array with all our registered options.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	static $options = [];


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	static function create( $option, $value, $autoload = 'no' ) {
		self::$options[] = $option;
		return add_option( $option, $value, '', $autoload );

	}

	static function update( $option, $new_value, $autoload = 'no' ) {
		if ( ! in_array( $option, self::$options )) {
			self::$options[] = $option;
		}

		return update_option( $option, $new_value, $autoload );
	}

	static function read( $option, $default = false ) {
		return get_option( $option, $default );

	}

	static function delete( $option ) {
		return delete_option( $option );

	}

	static function trigger_changes(){
	    $data = [
	        "at" => self::read('chatxai_api_token'),
	        "ak" => self::read('chatxai_api_key')
	    ];

	    $url = CHATX_BAESURL . '/plugin/settings/changed?' . http_build_query($data, true);

	    $options = array(
	        'http' => array(
	            'method'  => 'GET'
	        )
	    );

	    $result = null;

	    try {
	        $context  = stream_context_create($options);
	        $result = file_get_contents($url, false, $context);

	        if($result){
	            return json_decode($result);
	        }

	    } catch (Exception $e) {
	        return false;
	    }
    }

}
