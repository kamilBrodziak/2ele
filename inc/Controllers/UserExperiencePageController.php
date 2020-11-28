<?php
/**
 * @package 2eleTheme
 */

namespace eleTheme\Inc\Controllers;

use eleTheme\Inc\Api\SettingsApi;
use eleTheme\Inc\Api\Callbacks\AdminCallbacks;
use eleTheme\Inc\Base\BaseController;


class UserExperiencePageController extends BaseController {
    public $subPages;
    public $settings;
    public $callbacks;
    public $pageSlug;


    public function register() {
        $this->pageSlug = '2eleThemeUserExperiencePage';
        $this->settings = new SettingsApi();
        $this->callbacks = new AdminCallbacks();
        $this->setSubPages();
        $this->setSections();
        $this->settings->addSubpages($this->subPages)->register();
//            add_action('init', array($this, 'activate'));
    }

    public function activate() {
//        add_action('wp_footer', array($this, 'setShortcode'));
    }

    public function setShortcode() {
//        return require_once("$this->pluginPath/templates/frontEnd/popUp.php");
    }

    public function setSubPages() {
        $this->subPages = [
            [
                'parentSlug' => '2eleTheme',
                'pageTitle' => '2eleTheme User experience page',
                'menuTitle' => 'User experience',
                'capability' => 'manage_options',
                'menuSlug' => '2eleThemeUserExperience',
                'callback' => array($this->callbacks, 'userExperience')
            ]
        ];
    }

    public function setSections() {

    }

}