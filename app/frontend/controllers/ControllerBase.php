<?php
namespace Platform\Frontend\Controllers;

use Phalcon\Session as Session;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Flash\Direct as Flash;

class ControllerBase extends Controller {
    public $logger;
    private $model; //Respected model name for the controller.
    protected $redirect = array();

    protected function initialize() {
        $this->view->setTemplateAfter('main');
        $this->view->setVar('status', 200);
        \Phalcon\Tag::setTitle('Application');

        $this->model = ucfirst($this->router->getControllerName());
        $this->redirect['view'] = $this->router->getControllerName() .'/view/';
        $this->redirect['index'] = $this->router->getControllerName() .'/index';
        $this->view->user_role = $this->session->auth['role'];

        $this->view->privateMediaUri = $this->config['application']['privateMediaUri'];

        /* Get latest debug information! */
        if(DEBUG) {
            $this->logger = new \Phalcon\Logger\Adapter\Firephp("");

            $this->sqlDebugConnection = $this->di->getShared("db");
            $eventsManager = new \Phalcon\Events\Manager();
            $eventsManager->attach('db', function ($event, $connection) {
                if ($event->getType() == 'afterQuery') {
                    $connection->getSQLStatement();
                }
            });
            $this->sqlDebugConnection->setEventsManager($eventsManager);

            $this->sql = function () {
                return $this->sqlDebugConnection->getSQLStatement();
            };
        }

        $this->view->session_user_id = $this->session->auth['id'];
    }
    /**
     * Execute before the router so we can determine if this is a provate controller, and must be authenticated, or a
     * public controller that is open to all.
     *
     * @param Dispatcher $dispatcher
     * @return boolean
     */
    public function beforeExecuteRoute(Dispatcher $dispatcher) {
        $this->auth = $this->session->get('auth');
        $this->controller = $dispatcher->getControllerName();
        $this->action = $dispatcher->getActionName();

        if(empty($this->auth)) {
            $this->auth['role'] = 'guest';
        }


        $allowed = $this->acl->isAllowed($this->auth['role'], $this->controller, $this->action);

        if ($allowed != \Phalcon\Acl::ALLOW) {
            $this->flash->error('Please login to the system');
            $this->response->redirect($this->config->routes->aclNotAllowedRedirect); //that way no redirection
            return false;
        }

    }

    /**
     * This function sets the error messages from a model object and injects it into the view
     */
    protected function setMessages($object, $addition=null) {
        $formMessages = array();
        if(!empty($object->getMessages()))
            foreach ($object->getMessages() as $message)
                $formMessages[$message->getField()] = $message->getMessage();

        if(!empty($addition))
            $formMessages = array_merge($formMessages, $addition);

        if(!empty($formMessages)) {
            $this->view->formMessages = $formMessages;
        }
    }
}
