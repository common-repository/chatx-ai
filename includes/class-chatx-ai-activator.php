<?php

/**
 * Fired during plugin activation
 *
 * @link       https://chatx.ai
 * @since      1.0.0
 *
 * @package    Chatx_Ai
 * @subpackage Chatx_Ai/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Chatx_Ai
 * @subpackage Chatx_Ai/includes
 * @author     Chatx.ai <contact@chatx.ai>
 */
class Chatx_Ai_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Include tracking system on plugin activate to create the db

		// Do the ChatX login here
	    $data = [
	    	'old_api_key' => Chatx_Ai_Options::read('chatxai_api_key'),
            'shop_home_url' => get_home_url(),
		    'shop_api_url' => rest_url('/chatxai-api/v1', 'json'),
		];

    	$api_url = CHATX_BAESURL . '/woocommerce/install';

	    $options = array(
	        'http' => array(
	            'method'  => 'POST',
	            'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query($data)
	        )
	    );

	    $result = null;

	    try {
	        $context  = stream_context_create($options);
	        $result = file_get_contents($api_url, false, $context);
	    } catch (Exception $e) {

	    }

	    if(!$result){
        	echo 'Invalid ChatX searver';
            exit;
        }

        $resultDecoded = json_decode($result);

        if(!isset($resultDecoded->success) || !$resultDecoded->success){
        	echo 'You can\' login to the ChatX account';
            exit;
        }

        if(isset($resultDecoded->api_key) && $resultDecoded->api_key && isset($resultDecoded->api_token) && $resultDecoded->api_token){
            Chatx_Ai_Options::update('chatxai_api_key', $resultDecoded->api_key, 'yes');
            Chatx_Ai_Options::update('chatxai_api_token', $resultDecoded->api_token, 'yes');
        }
        else{
            echo 'Invalid ChatX searver';
            exit;
        }
	}
}
