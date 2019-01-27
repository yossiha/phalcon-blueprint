<?php
namespace Platform\Frontend;

use Platform\Acl\Acl as Acl;
use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;

class Module {
    public function registerAutoloaders() {
        $loader = new \Phalcon\Loader();
        $loader->registerNamespaces(array(
            'Platform\Frontend\Controllers' => __DIR__ . '/controllers/',
            'Platform\Frontend\Models' => __DIR__ . '/models/',
            'Platform\Frontend\Views' => __DIR__ . '/views/',
            'Platform\Frontend' => __DIR__,
            'Platform' => __DIR__ . '/../common/'
        ));
        $loader->register();
    }

    public function registerServices(DiInterface $di) {
        $di['dispatcher'] = function() {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace('Platform\Frontend\Controllers\\');

            return $dispatcher;
        };

        /**
         * Access Control List
         */
        $di['acl'] = function() {
            return new Acl();
        };

        $di->set('view', function () use ($di) {
            $view = new View();

            $view->setViewsDir(__DIR__ . '/views/');
            $view->registerEngines(array(
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
            ));
            return $view;
        }, true);
    }
}
