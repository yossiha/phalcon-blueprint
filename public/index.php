<?php
define("PhalconDebug", true);
define('APP_PATH', realpath('..'));
define('DEBUG',true);
define('PRODEBUG',false); //toolbar


include __DIR__ . '/../app/common/initialize.php';
$application = new \Phalcon\Mvc\Application($di);
    if(DEBUG) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $debug = new \Phalcon\Debug();
        $debug->listen();
        $debug->setShowBackTrace(true);
        $di['debug'] = $debug;
        if(PRODEBUG) {
            $loader = new \Phalcon\Loader();
            $loader->registerNamespaces(array(
                'Fabfuel' => '../app/vendors/Fabfuel/',
                'Psr' => '../app/vendors/Psr/'
            ));
            $loader->register();
            $profiler = new \Fabfuel\Prophiler\Profiler();
            $di->set('profiler', $profiler, true);

            $toolbar = new \Fabfuel\Prophiler\Toolbar($profiler);
            $toolbar->addDataCollector(new \Fabfuel\Prophiler\DataCollector\Request());
            $pluginManager = new \Fabfuel\Prophiler\Plugin\Manager\Phalcon($profiler);
        }
    }

    $application->registerModules(array(
        'frontend' => array(
            'className' => 'Platform\Frontend\Module',
            'path' => '../app/frontend/Module.php'
        ),
        'backend' => array(
            'className' => 'Platform\Backend\Module',
            'path' => '../app/backend/Module.php'
        )
    ));

    echo $application->handle()->getContent();
    if(PRODEBUG)
        echo $toolbar->render();
	