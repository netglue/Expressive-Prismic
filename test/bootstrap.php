<?php

require_once __DIR__ . '/../vendor/autoload.php';


class Bootstrap
{

    public $container;
    public $app;

    public static $instance;

    private function __construct()
    {
        $this->container = require __DIR__ . '/config/container.php';
        $app = $this->app = $container->get(\Zend\Expressive\Application::class);
        self::$instance = $this;
        require_once __DIR__ . '/config/pipeline.php';
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            new self;
        }
        return self::$instance;
    }
}

Bootstrap::getInstance();

