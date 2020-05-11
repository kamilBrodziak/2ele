<?php
/**
 * @package kBPlug
 */

namespace Inc\Api;

use Inc\Api\Callbacks\SettingsCallbacks;

class SettingsApi {
	public $adminPages = [];
	public $adminSubpages = [];
	public $sections = [];
	public $settingsCallbacks;

	public function register() {
		if(!empty($this->adminPages) || !empty($this->adminSubpages)) {
			add_action('admin_menu', array($this, 'addAdminMenu'));
		}

		if(!empty($this->sections)) {
			add_action('admin_init', array($this, 'registerCustomFields'));
		}
	}

	public function AddPages(array $pages) {
		$this->adminPages = $pages;
		return $this;
	}

	public function withSubPage( $title = null) {
		if(empty($this->adminPages)) {
			return $this;
		}

		$adminPage = $this->adminPages[0];
		$subpages = [ [
				'parentSlug' => $adminPage['menuSlug'],
				'pageTitle' => $adminPage['pageTitle'],
				'menuTitle' => ($title) ? $title : $adminPage['menuTitle'],
				'capability' => $adminPage['capability'],
				'menuSlug' => $adminPage['menuSlug'],
				'callback' => $adminPage['callback']
			]
		];
		$this->adminSubpages = $subpages;
		return $this;
	}

	public function addSubPages($pages) {
		$this->adminSubpages = array_merge($this->adminSubpages, $pages);
		return $this;
	}

	public function addAdminMenu() {
		foreach($this->adminPages as $page) {
			add_menu_page($page['pageTitle'], $page['menuTitle'], $page['capability'],
			              $page['menuSlug'], $page['callback'], $page['iconUrl'], $page['position']);
		}

		foreach($this->adminSubpages as $subpage) {
			add_submenu_page($subpage['parentSlug'],$subpage['pageTitle'], $subpage['menuTitle'],
			                 $subpage['capability'], $subpage['menuSlug'], $subpage['callback']);
		}
	}

	public function setSections(array $sections, $pageSlug) {
//		$this->settingsCallbacks = new SettingsCallbacks($pageSlug);
        $this->settingsCallbacks = new SettingsCallbacks($sections);
        $sectionsOutput = [];

		foreach ($sections as $section) {
			$sectionTemp = [
				'group' => $pageSlug,
				'id' => $section['id'],
				'title' => $section['title'],
				'callback' => array($this->settingsCallbacks, $section['id']),
				'pageSlug' => $pageSlug,
				'fields' => []
			];

			foreach ($section['fields'] as $field) {
                $sectionTemp['fields'][] = [
					'id' => $field['id'],
					'title' => $field['title'],
					'callback' => array($this->settingsCallbacks, $field['fieldType'] . 'Field'),
					'settingCallback' => array($this->settingsCallbacks, 'sanitizeInput'),
					'args' => [
						'optionName' => $pageSlug,
						'labelFor' => $field['id'],
						'class' => isset($field['args']['class']) ? $field['args']['class'] : '',
                        'label' => isset($field['args']['label']) ? $field['args']['label'] : ''
					]
				];
            }
			$sectionsOutput[] = $sectionTemp;
		}
		$this->sections = $sectionsOutput;
	}

	function registerCustomFields() {
		foreach ($this->sections as $section) {
			add_settings_section($section['id'], $section['title'], $section['callback'], $section['pageSlug']);
			foreach ($section['fields'] as $field) {
				register_setting($section['group'], $field['args']['optionName'], $field['settingCallback']);
				add_settings_field($field['id'], $field['title'], $field['callback'], $section['pageSlug'], $section['id'], $field['args']);
			}
		}

	}
}