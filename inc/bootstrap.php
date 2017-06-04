<?php

require __DIR__ . '/../vendor/autoload.php';

call_user_func(function () {
    error_reporting(-1);

    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();

    $basedir = dirname(__DIR__);
    $loader = new \Twig_Loader_Filesystem("{$basedir}/view");
    twig(new \Twig_Environment($loader, []));
});
