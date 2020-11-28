<?php
/**
 * @package 2eleTheme
 */

namespace eleTheme\Inc\Controllers;


use eleTheme\Inc\Base\BaseController;
use eleTheme\Inc\Api\SettingsApi;
use eleTheme\Inc\Api\Callbacks\AdminCallbacks;

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
                'id' => '2eleThemeNewsletter',
                'title' => 'Theme newsletter in header enable',
                'callback' => 'eleThemeNewsletterSection',
                'fields' => [
                    [
                        'id' => '2eleThemeNewsletterEnable',
                        'title' => 'Enable newsletter?',
                        'fieldType' => 'checkbox',
                        'args' => [
                            'class' => 'uiToggle'
                        ]
                    ],
                    [
                        'id' => '2eleThemeNewsletterTitle',
                        'title' => 'Newsletter text',
                        'fieldType' => 'text'
                    ],
                    [
                        'id' => '2eleThemeNewsletterAction',
                        'title' => 'Newsletter action',
                        'fieldType' => 'text'
                    ],
                    [
                        'id' => '2eleThemeNewsletterEmailLabel',
                        'title' => 'Newsletter email label',
                        'fieldType' => 'text'
                    ],
                    [
                        'id' => '2eleThemeNewsletterNameEnable',
                        'title' => 'Enable name in newsletter?',
                        'fieldType' => 'checkbox',
                        'args' => [
                            'class' => 'uiToggle'
                        ]
                    ],
                    [
                        'id' => '2eleThemeNewsletterNameLabel',
                        'title' => 'Name label',
                        'fieldType' => 'text'
                    ],
                    [
                        'id' => '2eleThemeNewsletterSubmitText',
                        'title' => 'Submit text',
                        'fieldType' => 'text'
                    ]

                ]
            ]
        ];

        $this->settings->setSections($sections, $this->pageSlug);
    }
}