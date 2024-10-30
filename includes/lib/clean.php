<?php

namespace CXLibrary;

/**
 * Sanitizer
 * @class Clean
 * @since 2.9
 */
class Clean {

	/**
	 * @param $string
	 * @return string
	 */
	static function string( $string ) {
		return sanitize_text_field( $string );
	}


	/**
	 * @param $email
	 * @return string
	 */
	static function email( $email ) {
		return strtolower( sanitize_email( $email ) );
	}


	/**
	 * @param $text
	 * @return string
	 */
	static function textarea( $text ) {
		return wp_strip_all_tags( wp_check_invalid_utf8( stripslashes( $text ) ) );
	}


	/**
	 * @param array $var
	 * @return array
	 */
	static function ids( $var ) {
		if ( is_array( $var ) ) {
			return array_filter( array_map( 'absint', $var ) );
		}
		else {
			return [ absint( $var ) ];
		}
	}


	/**
	 * @param $var
	 * @return array|string
	 */
	static function recursive( $var ) {
		if ( is_array( $var ) ) {
			return array_map( [ 'Clean', 'recursive' ], $var );
		}
		else {
			return is_scalar( $var ) ? self::string( $var ) : $var;
		}
	}


}
