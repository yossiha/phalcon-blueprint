<?php
namespace Platform\Frontend\Controllers;

use Platform\Frontend\Models\AreasOfLaw;

class DashboardController extends ControllerBase {
    protected $object=NULL;

    public function initialize() {
        $this->redirect['profiles_basic'] = 'users/bprofile'; //can be used as a redirection table.. if needed
        return parent::initialize();
    }

    public function routeAction() {
        switch($this->session->auth['role']) {
            case 'client': {
                return $this->response->redirect($this->config->routes->loginSuccessfulClient);
            }
            case 'supplier': {
                return $this->response->redirect($this->config->routes->loginSuccessfulSupplier);
            }
        }
        return $this->response->redirect('/');
    }

    public function clientIndexAction() {
        $this->view->pick('dashboard/client_index');
    }

    public function supplierIndexAction() {
        $this->view->pick('dashboard/supplier_index');
    }
}

