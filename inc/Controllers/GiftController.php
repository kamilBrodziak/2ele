<?php


namespace eleTheme\Inc\Controllers;


use eleTheme\Inc\Api\Callbacks\AdminCallbacks;
use eleTheme\Inc\Api\SettingsApi;
use eleTheme\Inc\Base\BaseController;

class GiftController extends BaseController {
    public SettingsApi $settings;
    public array $pages;
    public AdminCallbacks $callbacks;
    public string $pageSlug = '';
    public function register() {
        $this->pageSlug = '2eleThemeGift'; // admin page of theme
        $this->settings = new SettingsApi();
        $this->callbacks = new AdminCallbacks();
        $this->setSubPages();
        $this->setSections();
        $this->settings->addSubPages($this->pages)->register();
    }

    public function setSubPages() {
        // dashboard theme page
        $this->pages = [
            [
                'parentSlug' => '2eleTheme',
                'pageTitle' => '2ele Theme Gifts',
                'menuTitle' => 'Gifts',
                'capability' => 'manage_options',
                'menuSlug' => $this->pageSlug,
                'callback' => array($this->callbacks, 'gifts'),
            ]
        ];
    }

    public function setSections() {
        // features section
        $sections = [
            [
                'id' => '2eleThemeGifts',
                'title' => 'Woocommerce gifts',
                'callback' => 'eleThemeGiftsSection',
                'fields' => [
                    [
                        'id' => '2eleThemeGiftsEnable',
                        'title' => 'Enable gift?',
                        'fieldType' => 'checkbox',
                        'args' => [
                            'class' => 'uiToggle'
                        ]
                    ],
                    [
                        'id' => '2eleThemeGiftsID',
                        'title' => 'ID of product gift',
                        'fieldType' => 'number'
                    ],
                    [
                        'id' => '2eleThemeGiftsCartTotal',
                        'title' => 'Minimum cart total',
                        'fieldType' => 'currency',
                        'args' => [
                            'label' => 'Must be in format: xxx,yy'
                        ]
                    ],
                    [
                        'id' => '2eleThemeGiftsExcludedCategories',
                        'title' => 'Disallowed categories names',
                        'fieldType' => 'list',
                        'args' => [
                            'label' => 'Separate names of categories by ;, for example Phones;Tablets;Notebooks'
                        ]
                    ],
                    [
                        'id' => '2eleThemeGiftsAddedText',
                        'title' => 'Successful add of gift text showed:',
                        'fieldType' => 'text',
                        'args' => [
                            'label' => 'For example: Gift was successful added to cart,
                             or Gift %s was successful added to cart, where %s is gift name, so if you want
                             include product name just add %s where you want.'
                        ]
                    ],
                    [
                        'id' => '2eleThemeGiftsMinimumCartTotalText',
                        'title' => 'Minimum cart total text:',
                        'fieldType' => 'text',
                        'args' => [
                            'label' => 'For example: Minimum cart total to get that gift is not fulfilled,
                             or Cart total must be %1$s in order to get gift and products from cart must not be in 
                             categories: %2$s, where %1$s is minimum cart total and %2$s is excluded categories,
                              so if you want include minimum cart total just add %1$s where you want, same with categories %2$s.'
                        ]
                    ],
                    [
                        'id' => '2eleThemeGiftsNotLoggedInText',
                        'title' => 'Not logged in text:',
                        'fieldType' => 'text',
                        'args' => [
                            'label' => 'You must log in to acquire that gift.'
                        ]
                    ],
                    [
                        'id' => '2eleThemeGiftsAlreadyAcquiredText',
                        'title' => 'Already acquired text:',
                        'fieldType' => 'text',
                        'args' => [
                            'label' => 'For example: You already acquired that gift or,
                                You already acquired gift - %s, where %s is a gift product name.'
                        ]
                    ],
                ]
            ]
        ];

        $this->settings->setSections($sections, $this->pageSlug);
    }
}