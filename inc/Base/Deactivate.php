<?php
/**
 * @package 2eleTheme
 */

namespace eleTheme\Inc\Base;

class Deactivate {
    public static function deactivate() {
        flush_rewrite_rules();
    }
}