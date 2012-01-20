<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    private $front;
       
    protected function _initDoctype() 
    {
        $this -> bootstrap('view');
        $view = $this -> getResource('view');
		$view->setEncoding('UTF-8');
		$view->doctype('XHTML1_TRANSITIONAL');
    }

    protected function _initDb ()
    {
        $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/database.ini', 'production');
        Zend_Registry::set('config', $config);
        
        $db = Zend_Db::factory($config->database->adapter, $config->database->toArray());  
        Zend_Registry::set('db', $db);
    }
    
    protected function _initRoute() {
        $this -> front  = Zend_Controller_Front::getInstance();
        $router = $this -> front ->getRouter();
        
        $route = new Zend_Controller_Router_Route(
            'show/:id',
            array(
                'controller' => 'scripts',
                'action'     => 'show'
            ),
			array('id' => '\d+')
        );

        $router->addRoute('showScripts', $route);
		
        $route = new Zend_Controller_Router_Route_Regex(
			'show/(\d+)-(.+)',
			array(
                'controller' => 'scripts',
                'action'     => 'show'
			),
			array(
				1 => 'id',
				2 => 'description'
			),
			'show/%d-%s'
		);
		
		$router->addRoute('showScripts1', $route);
		
        $route = new Zend_Controller_Router_Route(
            'download/:id/:version',
            array(
                'controller' => 'scripts',
                'action'     => 'download',
				'version'	 => 'lastest'
            )
        );
        $router->addRoute('downloadScripts', $route);
        
        $route = new Zend_Controller_Router_Route(
            'versions/new/:id',
            array(
                'controller' => 'versions',
                'action'     => 'new'
            )
        );
        $router->addRoute('addVersion', $route);
        
         $route = new Zend_Controller_Router_Route(
            'versions/delete/:id',
            array(
                'controller' => 'versions',
                'action'     => 'delete'
            )
        );
        $router->addRoute('delete', $route);
        
        $route = new Zend_Controller_Router_Route(
            'scripts/edit/:id',
            array(
                'controller' => 'scripts',
                'action'     => 'edit'
            )
        );
        $router->addRoute('edit', $route);
        
        $route = new Zend_Controller_Router_Route(
            'scripts/delete/:id',
            array(
                'controller' => 'scripts',
                'action'     => 'delete'
            )
        );
        $router->addRoute('deleteScript', $route);
        
        $route = new Zend_Controller_Router_Route(
            'scripts/search/:bot/:category/s/:search/:page',
            array(
                'controller' => 'scripts',
                'action'     => 'index',
                'bot'		 => 'All',
                'category'	 => 'All',
                'search'	 => '',
				'page'		 => 1
            ),
			array('page' =>'\d+')
        );

        $router->addRoute('search', $route);
		
        $route = new Zend_Controller_Router_Route(
            'scripts/search/:bot/:category/:page',
            array(
                'controller' => 'scripts',
                'action'     => 'index',
                'bot'		 => 'All',
                'category'	 => 'All',
				'page'		 => 1
            ),
			array('page' =>'\d+')
        );

        $router->addRoute('search2', $route);
		
		$route = new Zend_Controller_Router_Route(
            'versions/edit/:id',
            array(
                'controller' => 'versions',
                'action'     => 'edit'
            )
        );
        $router->addRoute('editVersion', $route);
		
		$route = new Zend_Controller_Router_Route(
            'auth/delete/:id',
            array(
                'controller' => 'auth',
                'action'     => 'delete'
            )
        );
        $router->addRoute('deleteAuth', $route);
    }
    
    protected function _initAcl() {
        $acl = new Zend_Acl();

        
        $roleGuest = new Zend_Acl_Role('guest');
        $acl->addRole($roleGuest);
        
        $acl->addRole(new Zend_Acl_Role('registered'), $roleGuest);
        $acl->addRole(new Zend_Acl_Role('subscriber'), 'registered');
        $acl->addRole(new Zend_Acl_Role('cracker'), 'subscriber');
        
        $acl->addRole(new Zend_Acl_Role('administrator'));

        $acl->allow('registered', null, array('downloadold'));

        $acl->allow('subscriber', null, array('downloadold', 'downloadlastest'));
        
        $acl->allow('cracker', null, array('downloadold', 'downloadlastest', 'manage'));
                  

        $acl->allow('administrator');
        
        Zend_Registry::set('acl', $acl);
    }
    
    protected function _initIP() {
        return;
        
        $db =Zend_Registry::get('db');
        $role = USER_RIGHT == 'registered'?0:1;
        try {
            $db->insert('susers',array('userID'=> _UserID,
                        'subscriber' => $role,
            			'ip' => $_SERVER['REMOTE_ADDR']));
        }
        catch(exception $e) {
           $db->update('susers',array('userID'=> _UserID,
                        'subscriber' => $role,
            			'ip' => $_SERVER['REMOTE_ADDR']),
                         $db->quoteInto('userID= ?', _UserID));
        }
    }

}

