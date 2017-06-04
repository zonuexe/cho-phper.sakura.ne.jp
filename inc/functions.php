<?php

/**
 * Helper functions for RenSaba
 *
 * @author    USAMI Kenta <tadsan@zonu.me>
 * @copyright 2017 Baguette HQ
 * @license   http://www.wtfpl.net/ WTFPL
 */

use Monolog\Logger;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\NoopHandler;
use Monolog\Handler\StreamHandler;
use Teto\Routing\Router;

/**
 * @param string $tpl_name_or_closure
 * @param array $data
 * @return Closure
 */
function view($tpl_name_or_closure, array $data = [])
{
    if ($tpl_name_or_closure instanceof \Closure) {
        return $tpl_name_or_closure;
    }

    return function () use ($tpl_name_or_closure, $data) {
        echo twig()->render("{$tpl_name_or_closure}.tpl.html", $data);
    };
}

/**
 * Twig Service Locator
 *
 * @param  \Twig_Environment $twig
 * @return \Twig_Environment
 */
function twig(\Twig_Environment $twig = null)
{
    /** @var \Twig_Environment */
    static $cache;

    if ($twig !== null) {
        $functions = [
            'url_to' => function ($name, array $param = []) {
                return router()->makePath($name, $param, true);
            },
        ];

        foreach ($functions as $name => $closure) {
            $twig->addFunction(new \Twig_SimpleFunction($name, $closure));
        }

        $cache = $twig;
    }

    return $cache;
}

/**
 * Router Service Locator
 *
 * @param  Router $router
 * @return Router
 */
function router(Router $router = null)
{
    /** @var Router $cache */
    static $cache;

    if ($router !== null) {
        $cache = $router;
    }

    return $cache;
}


/**
 * @param array  $flash
 * @return array
 */
function last_flash(array $flash = null)
{
    /** @var array $last_flash */
    static $last_flash = [];

    if ($flash !== null) {
        $last_flash = $flash;
    }

    return $last_flash;
}

/**
 * @param  array $input
 * @return void
 */
function set_flash(array $input)
{
    $has_flash = !isset($_SESSION['_flash']) || !is_array($_SESSION['_flash']);
    $flash = $has_flash ? $_SESSION['_flash'] : [];

    foreach ($input as $key => $item) {
        $flash[$key] = filter_var($item, FILTER_DEFAULT);
    }

    $_SESSION['_flash'] = $flash;
}

/**
 * @return \Monolog\Logger
 */
function chrome_log()
{
    /** @var \Monolog\Logger */
    static $logger;

    if ($logger === null) {
        $logger  = new \Monolog\Logger('');
        $handler = is_production() ? new NoopHandler : new ChromePHPHandler(Logger::INFO);
        $logger->pushHandler($handler);
    }

    return $logger;
}
