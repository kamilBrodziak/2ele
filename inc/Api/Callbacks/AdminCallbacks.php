<?php
/**
 * @package kBPlug
 */

namespace Inc\Api\Callbacks;


use Inc\Base\BaseController;

class AdminCallbacks extends BaseController {
	public function dashboard() {
		return require_once("$this->pluginPath/templates/backend/dashboard.php");
	}
}
