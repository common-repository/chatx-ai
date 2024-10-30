<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://chatx.ai
 * @since      1.0.0
 *
 * @package    Chatx_Ai
 * @subpackage Chatx_Ai/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Chatx_Ai
 * @subpackage Chatx_Ai/includes
 * @author     Chatx.ai <contact@chatx.ai>
 */
class Chatx_Ai_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */

	public static function deactivate() {
		// Include tracking system on plugin activate to create the db
		$api_url = CHATX_BAESURL . '/woocommerce/uninstall';

	    $options = array(
	        'http' => array(
	            'method'  => 'POST',
	            'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query([
					'api_key' => Chatx_Ai_Options::read('chatxai_api_key'),
					'api_token' => Chatx_Ai_Options::read('chatxai_api_token'),
					'shop_url' => get_home_url()
				])
	        )
	    );

	    try {
	        $context  = stream_context_create($options);
	        $result = file_get_contents($api_url, false, $context);
	    } catch (Exception $e) {

	    }
	}
}
