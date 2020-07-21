<?php
/**
 * @package starterWordpressTheme
 */

namespace Inc\Controllers;


use Inc\Base\BaseController;
use Inc\Api\SettingsApi;
use Inc\Api\Callbacks\AdminCallbacks;

class DashboardController extends BaseController {
	public SettingsApi $settings;
	public array $pages;
	public AdminCallbacks $callbacks;
	public string $pageSlug = '';
	public function register() {
		$this->pageSlug = '2eleTheme'; // admin page of theme
		$this->settings = new SettingsApi();
		$this->callbacks = new AdminCallbacks();
		$this->setPages();
		$this->setSections();
		$this->settings->addPages($this->pages)->withSubPage('Dashboard')->register();
    }

    public function setPages() {
	    // dashboard theme page
	    $this->pages = [
		    [
			    'pageTitle' => '2ele Theme Dashboard',
			    'menuTitle' => '2eleTheme',
			    'capability' => 'manage_options',
			    'menuSlug' => $this->pageSlug,
			    'callback' => array($this->callbacks, 'dashboard'),
			    'iconUrl' => 'dashicons-art',
			    'position' => 110
		    ]
	    ];
    }

    public function setSections() {
	    // features section
	    $sections = [
            [
                'id' => '',
                'title' => 'Plugin features enable',
                'fields' => [
                    [
                        'id' => 'feature1',
                        'title' => 'Enable .. feature?',
                        'fieldType' => 'checkbox',
                        'args' => [
                            'class' => 'uiToggle'
                        ]
                    ]
                ]
            ]
        ];

        $this->settings->setSections($sections, $this->pageSlug);
    }
}