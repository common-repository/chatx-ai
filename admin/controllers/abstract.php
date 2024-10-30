<?php

/**
 * @class CX_Admin_Controller_Abstract
 * @since 2.4.5
 */
abstract class CX_Admin_Controller_Abstract {

	/** @var array */
	static $messages = [];

	/** @var array  */
	static $errors = [];


	/**
	 * Notices & stuff
	 */
	static function output_messages() {

		if ( sizeof( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div class="error"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		}
		elseif ( sizeof( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message ) {
				echo '<div class="updated"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}


	/**
	 * @since 2.7.8
	 * @return string
	 */
	static function get_messages() {
		ob_start();
		self::output_messages();
		return ob_get_clean();
	}

}
