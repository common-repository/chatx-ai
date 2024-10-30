<?php

namespace CXLibrary;

/**
 * Admin Notices
 * @class Notices
 * @since 2.9
 */
class Notices {

	/**
	 * @param
	 * @return array
	 */
	static function getAll() {
		$chatx_notices = get_transient('chatx-admin-notices');
        return is_array($chatx_notices) ? $chatx_notices : [];
	}

	/**
	 * @param $type string, $message string
	 * @return null
	 */
	static function add($type, $title, $message, $dismissable = false, $icon = false, $onlyonce = false) {
		$notices = Notices::getAll();

		$notices[]  = [
			'id' => cx_generate_key(10),
			'type' => $type,
			'title' => $title,
			'message' => $message,
			'dismissable' => $dismissable,
			'icon' => $icon,
			'onlyonce' => $onlyonce
		];

		set_transient('chatx-admin-notices', $notices, 60 * 60 * 24);
	}

	/**
	 * @param $id number
	 * @return null
	 */
	static function dismiss($id = null) {
		if(!$id) return;

		$notices = Notices::getAll();

		$notices  =  array_filter($notices, function($notice) use ($id){
			return isset($notice['id']) && $notice['id'] !== $id;
		});

		set_transient('chatx-admin-notices', $notices, 60 * 60 * 24);
	}

	/**
	 * @param $id number
	 * @return null
	 */
	static function removeAll($type = null) {
		$notices = Notices::getAll();

		$notices = array_filter($notices, function($notice) use ($type){
			if($type){

				return isset($notice['type']) && $notice['type'] !== $type;
			}

			return false;
		});

		set_transient('chatx-admin-notices', $notices, 60 * 60 * 24);
	}
}
