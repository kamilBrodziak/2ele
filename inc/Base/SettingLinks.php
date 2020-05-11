<?php
/**
 * @package starterWordpressTheme
 */

namespace Inc\Base;


class SettingLinks extends BaseController {
    public function register() {
//        add_filter("plugin_action_links_" . $this->plugin , array( $this, 'settingsThemeLink'));
    }

    function settingsThemeLink($links) {
//        $settingsLink = '<a href="admin.php?page=...">Settings</a>';
//        array_push($links, $settingsLink);
        return $links;
    }

}