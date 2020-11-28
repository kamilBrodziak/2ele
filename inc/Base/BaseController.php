<?php
/**
 * @package 2eleTheme
 */

namespace eleTheme\Inc\Base;


class BaseController {
    public $themePath;
    public $themeUrl;

    public function __construct() {
//        $this->themePath = plugin_dir_path(dirname(__FILE__, 2));
        $this->themePath = get_template_directory();
        $this->themeUrl = get_template_directory_uri();
//        $this->pluginUrl = plugin_dir_url(dirname(__FILE__, 2));
//        $this->plugin = plugin_basename(dirname(__FILE__, 3)) . '/' . plugin_basename(dirname(__FILE__, 3)) . '.php';
    }

    // is feature activated
    public function isActivated(string $feature) {
//        $option = get_option('themeNameDashboard');
//        return (isset($option[$feature])) ? ($option[$feature] ? true : false) : false;
    }
}