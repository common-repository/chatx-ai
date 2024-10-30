<?php

namespace CXLibrary;

class Format {

	const MYSQL = 'Y-m-d H:i:s';


	/**
	 * @param integer $day - 1 (for Monday) through 7 (for Sunday)
	 * @return string|false
	 */
	static function weekday( $day ) {

		global $wp_locale;

		$days = [
			1 => $wp_locale->get_weekday(1),
			2 => $wp_locale->get_weekday(2),
			3 => $wp_locale->get_weekday(3),
			4 => $wp_locale->get_weekday(4),
			5 => $wp_locale->get_weekday(5),
			6 => $wp_locale->get_weekday(6),
			7 => $wp_locale->get_weekday(0),
		];

		if ( ! isset( $days[ $day ] ) ) {
			return false;
		}

		return $days[ $day ];
	}


	/**
	 * @param $time
	 * @return string
	 */
	static function time_of_day( $time ) {

		$parts = explode( ':', $time );

		if ( count( $parts ) !== 2 ) {
			return '-';
		}

		return absint( $parts[0] ) . ':' . zeroise( $parts[1], 2 );
	}

}
