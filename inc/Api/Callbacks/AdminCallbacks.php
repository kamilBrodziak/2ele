<?php
/**
 * @package kBPlug
 */

namespace Inc\Api\Callbacks;


use Inc\Base\BaseController;

class AdminCallbacks extends BaseController {
	public function dashboard() {
		return require_once("$this->themePath/static/backend/dashboard.php");
	}

	public function userExperience() {
	    return require_once("$this->themePath/templates/backend/dashboard.php");
    }
}
