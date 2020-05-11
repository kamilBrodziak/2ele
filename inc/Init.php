<?php
/**
 * @package starterWordpressTheme
 */

namespace Inc;

final class Init {
    public static function registerServices() {
        foreach(self::getServices() as $class) {
            $service = self::instantiate($class);
            if(method_exists($service, 'register')) {
                $service->register();
            }
        }
    }

    public static function getServices() {
        return [
            Base\WooCommerceSettings::class,
	        Controllers\DashboardController::class,
	        Base\Enqueue::class,
            Base\SettingLinks::class,
        ];
    }

    private static function instantiate($class) {
        return new $class();
    }
}


