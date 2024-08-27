<?php
namespace OpenCartImporter\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class DBConnection
{
    private static $capsule = null;

    public static function getCapsule()
    {
        if (self::$capsule === null) {
            self::initialize();
        }
        return self::$capsule;
    }

    public static function initialize(array $db)
    {
        $capsule = new Capsule;
        // Configure database connection from OpenCart config

        $capsule->addConnection([
            'driver'    => $db['driver'],
            'host'      => $db['host'],
            'database'  => $db['database'],
            'username'  => $db['username'],
            'password'  => $db['password'],
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => $db['prefix'],
        ]);

        // Make this Capsule instance available globally via static methods
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $capsule->getConnection()->enableQueryLog();

        // Optionally set up Laravel Facade
        Facade::setFacadeApplication(Container::getInstance());

        self::$capsule = $capsule;
    }
}