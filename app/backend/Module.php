<?php
namespace Platform\Backend;

use Phalcon\DiInterface;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;

class Module {
    public function registerAutoloaders() {
        $loader = new \Phalcon\Loader();

        $loader->registerNamespaces(array(
            'Platform\Backend\Controllers' => __DIR__ . '/controllers/',
            'Platform\Backend\Models' => __DIR__ . '/models/',
            'Platform\Backend\Views' => __DIR__ . '/views/',
            'Platform\Backend' => __DIR__
        ));

        $loader->register();
    }

    public function registerServices(DiInterface $di) {
        $di['dispatcher'] = function() {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace("Platform\Backend\Controllers");
            return $dispatcher;
        };

        //removed DB service.
        $di->set('view', function () {
            $view = new View();
            $view->setViewsDir(__DIR__ . '/views/');
            $view->registerEngines(array(
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
            ));
            return $view;
        }, true);
    }
}
