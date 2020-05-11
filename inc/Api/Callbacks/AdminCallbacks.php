<?php
/**
 * @package kBPlug
 */

namespace Inc\Api\Callbacks;


use Inc\Base\BaseController;

class AdminCallbacks extends BaseController {
	public function adminDashboard() {
		return require_once("$this->pluginPath/templates/adminDashboard.php");
	}

	public function adminPopUp() {
		return require_once("$this->pluginPath/templates/adminPopUp.php");
	}

    public function adminCustomCSS() {
        return require_once("$this->pluginPath/templates/adminCustomCSS.php");
    }
}
