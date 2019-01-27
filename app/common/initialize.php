<?php
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Crypt; //?

$config = new \Phalcon\Config(array(
    'database' => array(
        'adapter'     => 'Mysql',
        'host'        => 'localhost',
        'username'    => 'root',
        'password'    => '', //console access: mysql -u root -h localhost -p
        'dbname'      => 'blueprint',
        'charset'      => 'utf8'
    ),
    'application' => array(
        'pluginsDir'     => __DIR__ . '/../../app/plugins/',
        'cacheDir'       => __DIR__ . '/../../app/cache/',
        'privateMediaDir'   => APP_PATH .'/public/pmedia/',
        'baseUri'        => '/',
        'privateMediaUri'        => '/pmedia/',
        'cryptSalt' => 'rq+4f`S6<<Sy<h|1S3j2qiU%9a3+d$+dxKC4|2NFMy2YHd(ZTN<{21ck5I9ajw5',
        'mediaSalt' => 'Q2q9"K6,>N4savE9#y{;Ea~;^s3VO8&6T2Jx1&q`%6EV`6Grr)to/,U)p+%Nxme',
        'debug' => true //@debug
    ),
    'routes' => array(
        'authLogoutRedirect' => '',
        'aclNotAllowedRedirect' => 'login/index',
        'dashboard' => 'dashboard/route',
        'loginSuccessfulClient' => 'cdashboard/',
        'loginSuccessfulSupplier' => 'sdashboard/'
    ),
    'mail' => array(
        'fromName' => 'AppName support',
        'fromEmail' => 'no-reply@DOMAIN-NAME.co.il',
        'domainId' => 'DOMAIN-NAME.com',
        'smtp' => array(
            'server'	=> 'mail.mailserver.com',
            'port' 		=> '25',
            //'security' => 'ssl', //if server supports SSL
            'username' => 'no-reply@DOMAIN-NAME.co.il',
            'password' => 'xxxxxxxxx',
        )
    )
));

$router = new Phalcon\Mvc\Router(false); //don't setup default routes, security enabled - good++
$router->setDefaultModule("frontend");
$router->setDefaultAction('index');

$router->add( //backend STUB controller
    "/admin/:controller/:action/:params",
    array(
        'module'     => 'backend',
        "controller" => 1,
        "action"     => 2,
        "params"     => 3
    )
);

//Ajax request, or non-ajax request? - check that the request is AJAX
/*$router->add('/login', array(
    'module'     => 'admin',
    'controller' => 'session'
))->beforeMatch(function ($uri, $route) {
    // Check if the request was made with Ajax
    if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'xmlhttprequest') {
        return false;
    }
    return true;
});*/

$router->add('/cdashboard/', array(
    'controller' => 'dashboard',
    'action'=>'clientindex'
));

$router->add('/sdashboard/', array(
    'controller' => 'dashboard',
    'action'=>'supplierindex'
));

$router->add('/dashboard/:action', array(
    'controller' => 'dashboard',
    'action'=>1
));
$router->add('/dashboard/', array(
    'controller' => 'dashboard',
    'action'=>'route'
));

$router->add('/login/', array(
    'controller' => 'session',
    'action'=>'index'
));
$router->add('/login/:action', array(
    'controller' => 'session',
    'action'=>1
));
$router->add('/users/:action/:params', array(
    'controller' => 'users',
    'action'=>1,
    'id'=>2
));

/*
//example for route only for debugging
if(defined(DEBUG) && !empty(DEBUG)) { 
    $router->add('/X/:action', array(
        'controller' => 'media',
        'action' => 1
    ));
}
*/

$di = new FactoryDefault();

$di->set('router', $router);

$di->set('modelsMetadata', function() {
    return new MetaDataAdapter(array(
        "lifetime" => 600,
        "prefix"   => "schema"
    ));
});
$di->set('config', $config);
$di->set('security', function(){
    $security = new Phalcon\Security();

    $security->setWorkFactor(12);
    $security->setDefaultHash(\Phalcon\Security::CRYPT_BLOWFISH_Y);

    return $security;
}, true);

$di->set('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
}, true);


/**
 * Database connection is created based in the parameters defined in the configuration file
 */
//@debug using the query logger..
$di->set('db', function () use ($config) {
    //$eventsManager = new \Phalcon\Events\Manager();
    //$queryLogger = new \Phalcon\Db\Profiler\QueryLogger();
    //$eventsManager->attach('db', $queryLogger);

    $adapter =  new DbAdapter(array(
        'host' => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname' => $config->database->dbname,
        'charset'   => $config->database->charset
    ));

    //if ($config->debug) {
    //$adapter->setEventsManager($eventsManager);
    //}

    return $adapter;
});

$di->set('flash', function(){
    $flash = new \Phalcon\Flash\Direct(array(
        'error' => 'alert alert-danger',
        'success' => 'alert alert-success',
        'info' => 'alert alert-info',
        'warning' => 'alert alert-warning'
    ));
    return $flash;
});

$di->set('session', function () {
    $session = new SessionAdapter();
    $session->start();

    return $session;
});

$di->set('crypt', function () use ($config) {
    $crypt = new Crypt();
    $crypt->setKey($config->cryptSalt); // Use your own key!

    return $crypt;
});

$di->set('security', function(){
    $security = new Phalcon\Security();

    $security->setWorkFactor(12);
    $security->setDefaultHash(\Phalcon\Security::CRYPT_BLOWFISH_Y);

    return $security;
}, true);

/**
 *
 * Example Usage:
 * Model:       Phalcon\DI::getDefault()->get('errorHandler', array('object'=>this));
 * Controller:  $this->di->get('errorHandler', array('object'=>'error msg to log'));
 * @param $object
 * @param string $type = debug, error, info, notice, warning, critical, alert, emergency, log
 */
/*$di->set('errorHandler', function($object, $msg=null, $type = 'error') {
    $logger = new \Phalcon\Logger\Adapter\File("../app/logs/main.log");

    if(!empty($msg))
        $logger->log(':   ' .$msg);

    if(is_object($object))
        foreach ($object->getMessages() as $message)
            $logger->$type(':   ' .$message);
    elseif(is_string($object) || is_numeric($object))
        $logger->$type(':   ' .$object);

    $logger->close();
});*/


$di->set('mail', function(){
    return new Platform\Library\Mail();
});
