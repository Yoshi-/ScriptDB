<?php

class Zend_Validate_Url extends Zend_Validate_Abstract
{
    const INVALID_URL = 'invalidUrl';

    protected $_messageTemplates = array(
        self::INVALID_URL   => "'%value%' is not a valid URL.",
    );

    function __construct(){

    }

    public function isValid($value)
    {
        $valueString = (string) $value;
        $this->_setValue($valueString);

        if (!Zend_Uri::check($value)) {
            $this->_error(self::INVALID_URL);
            return false;
        }
        return true;
    }
}


class AuthController extends Zend_Controller_Action
{

    public $auths = null;

    public function init()
    {
        $this -> auths = new Application_Model_Auths();
    }

    public function createForm($data = array ())
    {
             
        $form = new Zend_Form();
        
        $form->setDisableLoadDefaultDecorators(true);
 
        $form->addDecorator('FormElements')
             ->addDecorator('HtmlTag', array('tag' => 'table'))
             ->addDecorator('Form');
             
        $form->setAction('auth/save')
             ->setMethod('post');
				 
		$scriptName=$form->CreateElement('text','scriptName')
						->setLabel('Script Name:')
						->setRequired(true);
        $scriptName->setDecorators(array(
			'ViewHelper',
			'Description',
			'Errors',
			array(array('data'=>'HtmlTag'), array('tag' => 'td')),
			array('Label', array('tag' => 'td')),
			array(array('row'=>'HtmlTag'),array('tag'=>'tr'))

		));
		

		
		
		$username=$form->CreateElement('text','username')
						->setLabel('Auth Username:')
						->setRequired(true);
        $username->setDecorators(array(
			'ViewHelper',
			'Description',
			'Errors',
			array(array('data'=>'HtmlTag'), array('tag' => 'td')),
			array('Label', array('tag' => 'td')),
			array(array('row'=>'HtmlTag'),array('tag'=>'tr','openOnly'=>true))

		));
		
		$password=$form->CreateElement('text','password')
						->setLabel('Auth Password:')
						->setRequired(true);
        $password->setDecorators(array(
			'ViewHelper',
			'Description',
			'Errors',
			array(array('data'=>'HtmlTag'), array('tag' => 'td')),
			array('Label', array('tag' => 'td')),
			array(array('row'=>'HtmlTag'),array('tag'=>'tr'))

		));
		
		$loader=$form->CreateElement('text','loader')
						->setLabel('Loader Download:')
						->setRequired(true)
						->addValidator(new Zend_Validate_Url);
        $loader->setDecorators(array(
			'ViewHelper',
			'Description',
			'Errors',
			array(array('data'=>'HtmlTag'), array('tag' => 'td')),
			array('Label', array('tag' => 'td')),
			array(array('row'=>'HtmlTag'),array('tag'=>'tr'))

		));
		
		$submit=$form->CreateElement('submit', 'sstring')
					 ->setIgnore(true)
					 ->setLabel('Submit Auth');
        $submit->setDecorators(array(
			'ViewHelper',
			'Description',
			'Errors',
			array(array('data'=>'HtmlTag'), array('tag' => 'td','align'=>'center','colspan'=>2)),
			array(array('row'=>'HtmlTag'),array('tag'=>'tr'))

		));
		
		$hash=$form->CreateElement('hash', 'csrf')
					 ->setIgnore(true);
        $hash->setDecorators(array(
			'ViewHelper',
			'Description',
			'Errors',
			array(array('data'=>'HtmlTag'), array('tag' => 'td')),
			array(array('row'=>'HtmlTag'),array('tag'=>'tr'))

		));
        $form->addElements(array(
			$scriptName,
			$username,
			$password,
			$loader,
			$submit,
			$hash
		));
		
        
		return $form;
    }
	
    public function deleteAction()
    {
		$acl = Zend_Registry::get('acl');
        if(!$acl->isAllowed(USER_RIGHT, null, 'manage')) {
            $this->view->noaccess = true;
            $this->_forward('error','Error');
            return;
        }
		
		$this -> auths -> delete($this->_request->getParam('id'));
		
		$this -> view -> content = 'Auth deleted';
    }
	
    public function indexAction()
    {
		$acl = Zend_Registry::get('acl');
        $form = $this -> createForm();
         if(!$acl->isAllowed(USER_RIGHT, null, 'downloadold')) {
            $this->view->noaccess = true;
            $this->_forward('error','Error');
            return;
        }
		
		$this -> view -> form =  $form;
	}
	
	public function saveAction() {
	    $acl = Zend_Registry::get('acl');
        if(!$acl->isAllowed(USER_RIGHT, null, 'downloadold')) {
            $this->view->noaccess = true;
            $this->_forward('error','Error');
            return;
        }
       
        $form = $this -> createForm();;

        $request = $this->getRequest();
		$data = $request->getPost();
		
		if ($this->getRequest()->isPost() AND $form->isValid($request->getPost())) 
        {
                $insert['scriptName']  = $data['scriptName'];
                $insert['username'] = $data['username'];
				$insert['password'] = $data['password'];
				$insert['loader'] = $data['loader'];
				$insert['submitter'] = _UserID;
				$this -> auths -> addAuth($insert);
				$this -> view -> content = 'Auth Added';
        }
        else $errors[]='Please fill all forms';  
        
        if(isset($errors))
        {
            $this -> view -> errors = $errors;
            $this->indexAction(); 
            $this->render('index'); 
        }
	}
	
	public function showAction() {
		$acl = Zend_Registry::get('acl');
        if(!$acl->isAllowed(USER_RIGHT, null, 'manage')) {
            $this->view->noaccess = true;
            $this->_forward('error','Error');
            return;
        }
		$this -> view -> auths = $this -> auths -> getAllAuths();
	}
}
		

