<?php
namespace Platform\Acl;

use Phalcon\Mvc\User\Component;
use Phalcon\Acl\Role as AclRole;
use Phalcon\Acl\Resource as AclResource;

/*
 *  ACL component
 */
class Acl extends Component {
    /**
     * Access Control List object.
     */
    private $acl;
    private $roles;
    private $map = array( //maps out all of the controllers/actions
        'session'=>array( //controller
            'index','start','end','signup' //actions
        ),
        'dashboard'=>array(
            'route','supplierindex','clientindex'
        ),
        'index'=>array(
            'index'
        ),
        'users'=>array(
            'bprofile','profile','dprofile','type','cmedia','list'
        ),
        'media'=>array(
            'add','delete'
        )
    );

    private $privateResources = array(
        'client'=>array( //user type
            'session'=>array( //controller
                'end' //actions
            ),
            'users'=>array(
                'profile','bprofile','dprofile','type','cmedia','list'
            ),
            'dashboard'=>array(
                'route','clientindex'
            )
        )
    );

    private $publicResources = array( //public for users without sessions
        'session'=>array('index','start','signup'),
	    'index'=>array('index')
    );

    function __construct() {
        $this->acl = new \Phalcon\Acl\Adapter\Memory();
        $this->acl->setDefaultAction(\Phalcon\Acl::DENY);

        $this->roles = array( //different roles for the system
            'client' => new AclRole('client'),
            'supplier' => new AclRole('supplier'),
            'admin' => new AclRole('admin'),
            'guest' => new AclRole('guest')
        );

        foreach ($this->roles as $role) { //register the roles
            $this->acl->addRole($role);
        }

        foreach($this->map as $mapName => $resources) { //register the map of the platform
            foreach($resources as $resourcesName => $index) {
                $this->acl->addResource(new AclResource($mapName), $index);
            }
        }

        foreach ($this->privateResources as $roleName => $role) {
            foreach($role as $resourceName => $resources) {
                foreach($resources as $resourcesName => $resource) {
                    $this->acl->allow($roleName, $resourceName, $resource);
                }
            }
        }

        foreach ($this->roles as $role) { //Grant access to **public areas** to both users and guests
            foreach ($this->publicResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $this->acl->allow($role->getName(), $resource, $action);
                }
            }
        }
    }

    public function isAllowed($role, $controller, $action) {
        //$action = strtolower($action);
        //$controller = ucfirst($controller);
        /*  var_dump($this->acl->isAllowed($role, $controller, $action)); */
        return $this->acl->isAllowed($role, $controller, $action);
    }
}
