<?php
/**
 * @package 2eleTheme
 */

namespace eleTheme\Inc\Api\Callbacks;


use eleTheme\Inc\Base\BaseController;

class AdminCallbacks extends BaseController {
	public function dashboard() {
		return require_once("$this->themePath/static/backend/dashboard.php");
	}

	public function userExperience() {
	    return require_once("$this->themePath/static/backend/dashboard.php");
    }

    public function gifts() {
	    return require_once("$this->themePath/static/backend/gifts.php");
    }
}
