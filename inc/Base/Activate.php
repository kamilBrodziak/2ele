<?php
/**
 * @package starterWordpressTheme
 */

namespace Inc\Base;


class Activate {
    public static function activate() {
        flush_rewrite_rules();
	    $default = [];

	    //DB adding
//        if(!get_option('')) { //get_option('themeNameGeneralDB')
//	        update_option('kBPlugin', $default);
//        }

//	    if(!get_option('kBPluginPopUp')) { ////get_option('themeNameSubSettingsDB')
//		    update_option('kBPPopUp', $default);
//	    }

    }
}