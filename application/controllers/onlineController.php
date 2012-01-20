<?php

class OnlineController extends Zend_Controller_Action
{
    
    public function init()
    {
    }

    public function indexAction()
    {
		echo 'online';
                $this->view->layout()->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
    }

}









