<?php
namespace Platform\Frontend\Controllers;

use Phalcon\Session as Session;
use Platform\Frontend\Models\Users as Users;

class SessionController extends ControllerBase {
    private $model = 'Users';
    /**
     * Register authenticated user into session data
     *
     * @param Users $user
     */
    protected function registerSession($user) {
        $this->session->set('auth', array(
            'id' => $user->id,
            'role' => $user->role,
            'email' => $user->email
        ));
    }

    /**
     * Save a record to the database.
     * @return bool
     */
    private function saveRecord() {
        if(!isset($this->object) || $this->object==NULL) {
            $this->object = 'Platform\Frontend\Models\\' .$this->model;
            $this->object = new $this->object();
        }

        $this->object->email = $this->request->getPost("email", null, null, true);
        $this->object->password = $this->request->getPost("password", null, null, true);
        $this->object->password_confirm = $this->request->getPost("password_confirm", null, null, true);
        $this->object->role = 'client';
        $this->object->status = 'active';

        if($this->object->validation()) {
            $this->object->password = $this->security->hash($this->object->password);
            $this->object->password_confirm = $this->object->password;
            if ($this->object->create()) {

                //We're sending an Email out here
                /*$this->getDI()->getMail()->send(
                    array(
                        $this->object->email=>''
                    ),
                    "Welcome to Our System!",
                    'welcome',
                    array(
                        'param1'=>'data1'
                    )
                );*/

                $this->flash->success('User registered successfully');
                return true;
            }
            else {
                $this->flash->error("System error, please contact support");
                return false;
            }
        }
        else {
            $this->setMessages($this->object);
            $this->flash->error("There is an error with your form");
            return false;
        }
    }

    /**
     * Just display the form
     */
    public function indexAction() {
        \Phalcon\Tag::setTitle('Login');
    }

    /**
     * This function only registers a username/email to the system (no edit, that's in the Users section)
     */
    public function signupAction() {
        \Phalcon\Tag::setTitle('Sign Up');

        if ($this->request->isPost()) {

            if($this->saveRecord()) {
                $this->flash->clear();
                $this->flash->success('Thanks for joining us!'); //this->flashSession
                $this->registerSession($this->object);

                return $this->response->redirect('users/bprofile');
            }
            else {
                //Do nothing, operation wasn't successful. A flash message was sent stating the reason.
                $this->view->object = $this->object;
            }
        }
    }

    /**
     * User login form and session start.
     */
    public function startAction() {
        if ($this->request->isPost()) {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            if(!empty($email) && !empty($password)) {
                $conditions = "email = :email:";// AND password = :password:";
                $parameters = array(
                    "email" => $email
                );
                $user = Users::findFirst(array(
                    $conditions,
                    "bind" => $parameters
                ));

                if ($user != false && $this->security->checkHash($password, $user->password)) {
                    switch($user->status) {
                        case 'active': {
                            $this->registerSession($user);
                            switch($this->session->get('auth')['role']) {
                                case 'client':
                                    return $this->response->redirect($this->config->routes->loginSuccessfulClient);
                                case 'supplier':
                                    return $this->response->redirect($this->config->routes->loginSuccessfulSupplier);
                                case 'admin':
                                    return $this->response->redirect($this->config->routes->loginSuccessfulAdmin);
                            }
                            break;
                        }
                        case 'pending': {
                            $this->flash->error('Please verify your Email account.');
                            break;
                        }
                        case 'suspended': {
                            $this->flash->error('You are suspended from your system, please contact us');
                            break;
                        }
                    }
                }
                else { //user not fetched from DB or pw wrong
                    $this->security->hash(rand()); //against timing attacks
                    $this->flash->error('User or password not found');
                }
            }
            else $this->flash->error('Please enter your username and password');
        }
    }

    /**
     * Finishes the active session
     *
     * @return unknown
     */
    public function endAction() {
        $this->session->destroy(); //destroy everything
        return $this->response->redirect($this->config->routes->authLogoutRedirect);
    }
}
