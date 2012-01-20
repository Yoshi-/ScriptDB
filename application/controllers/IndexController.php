<?php

class IndexController extends Zend_Controller_Action
{
    public $scripts = null;

    public function init()
    {
        $this -> scripts = new Application_Model_Scripts();
    }

    public function indexAction()
    {
		$this->_forward('index', 'scripts');
        #$content = $this -> scripts -> getAllScripts();
        #$this->view->scripts = $content;
    }


}



