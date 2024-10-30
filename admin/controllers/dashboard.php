<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class CX_Admin_Controller_Dashboard
 * @since 2.8
 */
class CX_Admin_Controller_Dashboard extends CX_Admin_Controller_Abstract {

	/** @var array */
	private static $widgets;

	static function output() {

		wp_enqueue_script( 'chatx-ai-dashboard' );

		self::maybe_set_date_cookie();

		$baseUrl = get_home_url();

		if(substr($baseUrl, -1) == '/') {
		    $baseUrl = substr($baseUrl, 0, -1);
		}

		$queryArray = [
            'shop_home_url' => $baseUrl,
		    'shop_api_url' => rest_url('/chatxai-api/v1', 'json'),
		];

    	$register_url = CHATX_BAESURL . '/woocommerce/install?' . http_build_query($queryArray);

		Chatx_Ai_Admin::get_view( 'page-dashboard', [
			'register_url' => $register_url,
			'is_registered' => CX()->is_registered(),
			'dashboardUrl' =>  CHATX_DASHBOARD . '/pages/autologin/' . CX()->get_api_token()
		]);
	}

	static function maybe_set_date_cookie() {
		if ( cx_request( 'date' ) ) {
			$date = CXLibrary\Clean::string( cx_request( 'date' ) );
			if ( ! headers_sent() ) wc_setcookie( 'chatxai_dashboard_date', $date, time() + MONTH_IN_SECONDS * 2 );
		}
	}

}
