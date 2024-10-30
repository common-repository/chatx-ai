<?php

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 * @deprecated
 * @param string|array $var
 * @return string|array
 */
function cx_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'cx_clean', $var );
	}
	else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}


/**
 * @param $param
 * @return mixed
 */
function cx_request( $param ) {
	if ( isset( $_REQUEST[$param] ) )
		return $_REQUEST[$param];

	return false;
}

function cx_generate_key($length = 25)
{
    $chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $password .= substr($chars, rand(0, strlen($chars) - 1), 1);
    }
    return $password;
}

function cx_display_time($timestamp, $max_diff = false, $convert_from_gmt = true)
{

    if (!is_numeric($timestamp)) {
        if (is_string($timestamp)) {
            $timestamp = strtotime($timestamp);
        } else {
            return false;
        }
    }

    if ($timestamp < 0) {
        return false;
    }

    if ($convert_from_gmt) {
        $timestamp = strtotime(get_date_from_gmt(date('Y-m-d H:i:s', $timestamp), 'Y-m-d H:i:s'));
    }

    $now = current_time('timestamp');

    if ($max_diff === false) {
        $max_diff = DAY_IN_SECONDS;
    }
    // set default

    $diff = $timestamp - $now;

    if (abs($diff) >= $max_diff) {
        return $date_to_display = date_i18n('Y/m/d ' . wc_time_format(), $timestamp);
    }

    if ($diff > 0) {
        return sprintf(__('%s from now'), human_time_diff($now, $timestamp));
    } else {
        return sprintf(__('%s ago'), human_time_diff($now, $timestamp));
    }
}

function chatx_active_campaign_sync( $tags ) {
    if (defined('ACTIVE_CAMPAIGN_KEY')) {
        $email = base64_decode(strtr(ACTIVE_CAMPAIGN_KEY, '-_', '+/'));

        // By default, this sample code is designed to get the result from your ActiveCampaign installation and print out the result
        $url = 'https://chatxai.api-us1.com';

        $params = array(

            // the API Key can be found on the "Your Settings" page under the "API" tab.
            // replace this with your API Key
            'api_key'      => '79aaa03ca31c3ab496f68adb8d6dff2d7d5dbd89d50c7573e87e6e4ea2c63178e4aee45d',

            // this is the action that adds a contact
            'api_action'   => 'contact_sync',

            // define the type of output you wish to get back
            // possible values:
            // - 'xml'  :      you have to write your own XML parser
            // - 'json' :      data is returned in JSON format and can be decoded with
            //                 json_decode() function (included in PHP since 5.2.0)
            // - 'serialize' : data is returned in a serialized format and can be decoded with
            //                 a native unserialize() function
            'api_output'   => 'serialize',
        );

        // here we define the data we are posting in order to perform an update
        $post = array(
            'email'                    => $email,
            'tags'                     => $tags,
            'field[%SHOP_URL%,0]'      => get_home_url(),
        );

        // This section takes the input fields and converts them to the proper format
        $query = "";
        foreach( $params as $key => $value ) $query .= urlencode($key) . '=' . urlencode($value) . '&';
        $query = rtrim($query, '& ');

        // This section takes the input data and converts it to the proper format
        $data = "";
        foreach( $post as $key => $value ) $data .= urlencode($key) . '=' . urlencode($value) . '&';
        $data = rtrim($data, '& ');

        // clean up the url
        $url = rtrim($url, '/ ');

        // This sample code uses the CURL library for php to establish a connection,
        // submit your request, and show (print out) the response.
        if ( !function_exists('curl_init') ) die('CURL not supported. (introduced in PHP 4.0.2)');

        // If JSON is used, check if json_decode is present (PHP 5.2.0+)
        if ( $params['api_output'] == 'json' && !function_exists('json_decode') ) {
            die('JSON not supported. (introduced in PHP 5.2.0)');
        }

        // define a final API request - GET
        $api = $url . '/admin/api.php?' . $query;

        $request = curl_init($api); // initiate curl object
        curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        curl_setopt($request, CURLOPT_POSTFIELDS, $data); // use HTTP POST to send form data
        //curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment if you get no gateway response and are using HTTPS
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

        $response = (string)curl_exec($request); // execute curl post and store results in $response

        // additional options may be required depending upon your server configuration
        // you can find documentation on curl options at http://www.php.net/curl_setopt
        curl_close($request); // close curl object

        if ( !$response ) {
            // die('Nothing was returned. Do you have a connection to Email Marketing server?');
        }
    }
}

function cx_get_crons_data(){
    $cronsData = [];
    $cx_crons_names = ['chatxai_send_carts_worker', 'chatxai_daily_worker'];

    foreach($cx_crons_names as $cronName){
        $cronsData[$cronName] = [
            'was_active' => false,
            'next_run' => null
        ];

        if (wp_next_scheduled($cronName)) {
            $cronsData[$cronName]['was_active'] = true;
            $cronsData[$cronName]['next_run'] = cx_get_next_cron_time($cronName);
        }
    }

    return $cronsData;
}

function cx_get_next_cron_time( $cron_name ){

    foreach( _get_cron_array() as $timestamp => $crons ){

        if( in_array( $cron_name, array_keys( $crons ) ) ){
            return $timestamp - time();
        }

    }

    return false;
}
