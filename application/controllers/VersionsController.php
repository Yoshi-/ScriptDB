<?php

class VersionsController extends Zend_Controller_Action
{

    private $form;
    private $request;
    private $data;
    
    public function init()
    {
        $this -> request = $this->getRequest();
		$this -> data = $this -> request->getPost();
    }

    public function indexAction()
    {
        // action body
    }

    public function createForm($data = Array()) {
        $form = new Zend_Form();
        
        $form->setDisableLoadDefaultDecorators(true);
 
        $form->addDecorator('FormElements')
             ->addDecorator('HtmlTag', array('tag' => 'ul'))
             ->addDecorator('Form');
             
        $form->setAction('versions/save')
             ->setMethod('post')
             ->setAttrib('enctype', 'multipart/form-data');
     
        $form->addElement('text', 'versionID', array(
            'label'      => 'VersionID',
			'value'		 => (isset($data['version'])?$data['version']:''),
            'required'	 => true
        ));
        
        $form->addElement('file', 'version', array(
            'label'      => 'File:',
            'required'	 => (!(isset($data)))
        ));
        
        $form->addElement('text', 'mainclass', array(
            'label'      => 'Main Class:',
			'value'		 => (isset($data['mainclass'])?$data['mainclass']:''),
            'required'	 => true
        ));

        $checkbox = new Zend_Form_Element_Checkbox("lastest");

		$checkbox -> setValue($data['lastest']==1?1:0)
				  -> setLabel("Subscriber only?");
		
		$form->addElement($checkbox);
						

        
        $form->addElement('submit', 'sstring', array(
            'ignore'   => true,
            'label'    => 'Upload Version',
        ));
        
        if($this->_request->getParam('id') != null) $scriptID = $this->_request->getParam('id');
        else $scriptID = intval($this -> data['scriptID']);
        
        $form->addElement('hidden', 'scriptID', array(
            'required'	 => true,
            'value'		 => intval($scriptID)
        ));
        
		if(isset($data['versionID'])) {
			$form->addElement('hidden', 'version_id', array(
				'required'	 => true,
				'value'		 => intval($data['versionID'])
			));
		}
		
        $form->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));

        $this -> form = $form;
        
    }
    public function newAction()
    {
		$this -> createForm();
        $acl = Zend_Registry::get('acl');
        if(!$acl->isAllowed(USER_RIGHT, null, 'manage')) {
            $this->view->noaccess = true;
            $this->_forward('error','Error');
        }
        $this->view->form = $this -> form;
    }

    public function editAction()
    {		
	    $ScriptsModel = new Application_Model_Scripts();
		$data = $ScriptsModel->getVersion($this->_request->getParam('id'));
		
		$this -> createForm($data);
		
        $acl = Zend_Registry::get('acl');
        if(!$acl->isAllowed(USER_RIGHT, null, 'manage')) {
            $this->view->noaccess = true;
            $this->_forward('error','Error');
        }
        $this->view->form = $this -> form;
    }

    public function deleteAction()
    {        
        $acl = Zend_Registry::get('acl');
        if(!$acl->isAllowed(USER_RIGHT, null, 'manage')) {
           $this->view->noaccess = true;
           $this->_forward('error','Error');
           return;
       }
       if($this->_request->getParam('id') == null)
       {
           $this->view->msg = 'Please submit an id';
       }
       else
       {
           $this->view->msg = 'Version deleted';
           $ScriptsModel = new Application_Model_Scripts();
           $file = $ScriptsModel->getVersion($this->_request->getParam('id'));
           
           unlink(APPLICATION_PATH.'/../scriptsfolder/'.$file['serverName']);
           $version = new Application_Model_Versions(); 
           $version->delete($this->_request->getParam('id'));
       }
    }

    public function saveAction()
    {
        $acl = Zend_Registry::get('acl');
        if(!$acl->isAllowed(USER_RIGHT, null, 'manage')) {
            $this->view->noaccess = true;
            $this->_forward('error','Error');
            return;
        }
		$data = $this -> data;
		if(isset($data['version_id']))
			$this -> createForm($data);
		else $this -> createForm();
		
        $form = $this -> form;

        $request = $this -> request;

		if ($this->getRequest()->isPost() AND $form->isValid($request->getPost())) 
        {
            $version = new Application_Model_Versions(); 
            if(($version->versionExists($data['scriptID'],
                                     $data['versionID']) == 0 OR isset($data['version_id'])) AND $data['scriptID'] != 0) {

                if(isset($data['lastest'])) $insert['lastest'] = $data['lastest'];  
                else $insert['lastest'] = 0;
                
                $insert['scriptID']  = intval($data['scriptID']);
                $insert['version'] = $data['versionID'];
                
                $insert['mainclass'] = $data['mainclass'];
				if(isset($data['version_id'])) {
					$insert['versionID'] = $data['version_id'];
					unset($insert['scriptID']);
				}
                
                $upload = new Zend_File_Transfer();
                
                $upload->addValidator('Extension', false, 'jar,class,zip,rar');
                
                if(!$upload->isValid('version') AND $upload->isUploaded()) 
                {
                    $errors[]='You can only upload jar/class files.';
                }
                else
                {
					if($upload->isUploaded()) {
						$insert['serverName'] = md5($upload->getFileName('version').uniqid());
						$insert['realName'] = $upload->getFileName('version', null);
						$insert['cracker'] = _UserID;	
						
						$upload -> addFilter('rename', APPLICATION_PATH.'/../scriptsfolder/'.$insert['serverName']);
						$upload -> receive();
					}
					
                    if(isset($data['version_id'])) $version -> editVersion($insert);
                    else $version -> addVersion($insert);
					
                    $this -> view -> content = 'Version added';
                }
            }
            else $errors[]='Version does already exists';  
        }
        else $errors[]='Please fill all forms';  
        
        if(isset($errors))
        {
            $this -> view -> errors = $errors;
            $this->newAction(); //execute the same code as actionTwoAction()
            $this->render('new'); 
        }
    }


}









