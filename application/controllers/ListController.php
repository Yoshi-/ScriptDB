<?php

class ListController extends Zend_Controller_Action
{

	private $scripts;

    public function init()
    {
		$this -> scripts = new Application_Model_List();
    }

    public function indexAction()
    {
		$this->view->layout()->disableLayout();
		
		$db = Zend_Registry::get('db');
		
		$select = $db -> select()
                      -> from(array('v' => 'scriptversions'))
                      -> joinLeft(array('s' => 'scripts'),'s.scriptID = v.scriptID')
                      -> where("v.versionID = ?", $id)
                      -> limit("1");
		
		$select = $db -> select()
                      -> from('susers')
                      -> where("ip = ?", $_SERVER['REMOTE_ADDR'])
                      -> limit("1");

        $stmt = $db->query($select);
        $result = $stmt->fetch();
		
		$scripts = $this -> scripts -> scriptList($result['userID'], $result['subscriber']);
		
		$this -> view -> scripts = $scripts;
    }

}