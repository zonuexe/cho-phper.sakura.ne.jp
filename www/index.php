<?php

/**
 * ヾ(〃＞＜)ﾉﾞ☆ RenSaba
 *
 * @author    USAMI Kenta <tadsan@zonu.me>
 * @copyright 2017 Baguette HQ
 * @license   http://www.wtfpl.net/ WTFPL
 */

require __DIR__ . '/../inc/bootstrap.php';

use Teto\Routing\Action;

if (php_sapi_name() === 'cli-server') {
    if (strpos($_SERVER['REQUEST_URI'], '..') !== false) {
        http_response_code(404);
        return true;
    }
    $path = __DIR__ . implode(DIRECTORY_SEPARATOR, explode('/', $_SERVER['REQUEST_URI']));
    if (is_file($path)) {
        return false;
    }
}

$routes = [];

$routes['index'] = ['GET', '/', function (Action $action) {
    return [200, [], view('index')];
}];

$routes['phpinfo'] = ['GET', '/phpinfo', function (Action $action) {
    return [200, [], view(\Closure::fromCallable('phpinfo'))];
}, '?ext' => ['', 'php']];

$routes['license'] = ['GET', '/license', function (Action $action) {
    return [200, [], view('license')];
}, '?ext' => ['', 'html']];

$routes['composer'] = ['GET', '/composer', function (Action $action) {
    return [200, ['Content-Type' => 'application/json'], function () use ($action) {
        readfile(__DIR__ . "/../composer.{$action->extension}");
    }];
}, '?ext' => ['json', 'lock']];

$routes['#404'] = function (Action $action) {
    return [404, [], view('404')];
};

$router = router(new \Teto\Routing\Router($routes));
$action = $router->match($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

/**
 * @var int   $status
 * @var array $headers
 * @var Closure|false $content
 */
list($status, $headers, $content) = call_user_func($action->value, $action);

$headers += [
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-Content-Type-Options' => 'nosniff',
];

http_response_code($status);
foreach ($headers as $name => $header) {
    header("{$name}:{$header}");
}

if ($content !== false) {
    $content();
}
