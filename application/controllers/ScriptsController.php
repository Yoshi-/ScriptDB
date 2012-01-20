<?php

class ScriptsController extends Zend_Controller_Action
{

    public $scripts = null;

    private $form = null;

    public function init()
    {
        $this -> scripts = new Application_Model_Scripts();
    }

    public function indexAction()
    {
        
        $request = $this->getRequest();
		$data = $request->getPost();
		
        $form = $this -> createSearchFrom($data);
        $this->view->form = $form;

		if ($this->getRequest()->isPost() AND $form->isValid($request->getPost())) {
		    header("Location: /scriptdb/scripts/search/".$data['bot']."/".$data['category']."/s/".$data['search']);
		    exit;
		}
		Zend_Registry::set('_request', $this->_request);
		$content = $this -> scripts -> getAllScripts(array('bot' => $this->_request->getParam('bot'),'category' => $this->_request->getParam('category'),'search' => $this->_request->getParam('search'),'page' => $this->_request->getParam('page')));
        $this->view->scripts = $content;
    }
    
    public function createSearchFrom($data=array()) {
        $form = new Zend_Form();
        
        $form->setMethod('post');

  
      
       $username=$form->CreateElement('select','category')
                       ->setMultiOptions(array('All' => 'All'))
					   ->addMultiOptions($this -> scripts -> getCategories())
                       ->setValue($this->_request->getParam('category'))
                       ->setLabel(' Category: ');

  

       $username->setDecorators(array(
               'ViewHelper',
               'Description',
               'Errors',
               array(array('data'=>'HtmlTag'), array('tag' => 'td')),
               array('Label', array('tag' => 'td')),
               array(array('row'=>'HtmlTag'),array('tag'=>'tr', 'openOnly'=>true))
       ));


       $password=$form->CreateElement('select','bot')
                       ->setMultiOptions(array('All' => 'All'))
					   ->addMultiOptions($this -> scripts -> getBots())
                       ->setValue($this->_request->getParam('bot'))
                       ->setLabel(' Bot: ');

  

       $password->setDecorators(array(
               'ViewHelper',
               'Description',
               'Errors',
               array(array('data'=>'HtmlTag'), array('tag' => 'td')),
               array('Label', array('tag' => 'td')),
       ));

       $SearchBox=$form->CreateElement('text','search',array('value' => ($this->_request->getParam('search')?$this->_request->getParam('search'):''),));

       $SearchBox->setDecorators(array(
               'ViewHelper',
               'Description',
               'Errors',
               array(array('data'=>'HtmlTag'), array('tag' => 'td')),
               array('Label', array('tag' => 'td')),
       ));
       

       $submit=$form->CreateElement('submit','sstring')
        ->setLabel('Search');

  

       $submit->setDecorators(array(
               'ViewHelper',
               'Description',
               'Errors', array(array('data'=>'HtmlTag'), array('tag' => 'td',
               'colspan'=>'2','align'=>'center')),
               array(array('row'=>'HtmlTag'),array('tag'=>'tr', 'closeOnly'=>'true'))

       ));

  

       $form->addElements(array(

               $username,
               $password,
               $SearchBox,
               $submit
       ));

       $form->setDecorators(array(
               'FormElements',
               array(array('data'=>'HtmlTag'),array('tag'=>'table')),
               'Form'
       ));
       return $form;
        
    }
    
    public function showAction()
    {
        $id = $this->_request->getParam('id');
        $content = $this -> scripts -> getScript($id);

        $this->view->script = $content;
        $acl = Zend_Registry::get('acl');
        $versions = $this -> scripts -> getScriptVersions($id, $acl->isAllowed(USER_RIGHT, null, 'downloadlastest'));

        $this->view->versions = $versions;
    }

    public function downloadAction()
    {
        if($this->_request->getParam('version') == 'lastest')
        {
            $file = $this -> scripts -> getLastest($this->_request->getParam('id'));
        }
        else {
            $file = $this -> scripts -> getVersion($this->_request->getParam('version'));
        }

        if($file['lastest'] == 1) {
            $type = 'downloadlastest';
        }
        else $type = 'downloadold';

        $db = Zend_Registry::get('db');

        $select = $db -> select()
                      -> from('susers')
                      -> where("ip = ?", $_SERVER['REMOTE_ADDR'])
                      -> limit("1");

        $stmt = $db->query($select);
        $result = $stmt->fetch();
        
        
        $acl = Zend_Registry::get('acl');
        
        if(!$acl->isAllowed(USER_RIGHT, null, $type) AND !($result['subscriber'] >= $file['lastest'])) {
            $this->view->noaccess = true;
            $this->_forward('error','Error');
            return;
        }
        
        if($file['bot'] == 'Powerbot' AND stripos($_SERVER['HTTP_USER_AGENT'], 'java') === false) {
            if(!$this -> scripts -> scriptListExists(_UserID, $file['versionID'])) {
                $this -> scripts -> addScriptToList(_UserID, $file['versionID']);
                $content = 'Script Added';
            }
            else {
                $this -> scripts -> deleteScriptList(_UserID, $file['versionID']);
                $content = 'Script Removed';
            }

        }
        else {
            if(file_exists(APPLICATION_PATH.'/../scriptsfolder/'.$file['serverName'])) {
                header("Content-type: application/octet-stream");
                header("Content-Disposition: filename=\"".$file["realName"]."\"");
                header("Content-length: ".filesize(APPLICATION_PATH.'/../scriptsfolder/'.$file['serverName']));
        
                readfile(APPLICATION_PATH.'/../scriptsfolder/'.$file['serverName']);
             
                // disable layout and view
                $this->view->layout()->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
                exit;
            }
            else $content = 'Script doesn\'t exists';
        }
        $this -> view -> content = $content;
    }

    public function createForm($data = array ())
    {
             
        $form = new Zend_Form();
        
        $form->setDisableLoadDefaultDecorators(true);
 
        $form->addDecorator('FormElements')
             ->addDecorator('HtmlTag', array('tag' => 'ul'))
             ->addDecorator('Form');
             
        $form->setAction('scripts/save')
             ->setMethod('post')
             ->setAttrib('enctype', 'multipart/form-data');
     
        $form->addElement('text', 'scriptName', array(
            'label'      => 'Script Name ',
            'value'		 => (isset($data['ScriptName'])?$data['ScriptName']:''),
            'required'	 => true
        ));
        
        $form->addElement('textarea', 'Description', array(
            'label'      => 'Description',
            'value'		 => (isset($data['Description'])?$data['Description']:''),
            'required'	 => true
        ));
        
        $bot=$form->CreateElement('select','bot')
					   ->addMultiOptions($this -> scripts -> getBots())
                       ->setValue((isset($data['bot'])?$data['bot']:''))
                       ->setLabel('Bot:');
                       
        $form->addElement($bot);
        
        $category=$form->CreateElement('select','category')
					   ->addMultiOptions($this -> scripts -> getCategories())
					   ->setValue((isset($data['category'])?$data['category']:''))
                       ->setLabel('Category:');
                       
        $form->addElement($category);              
                       
        $form->addElement('file', 'image', array(
            'label'      => 'Image:',
        ));
        
        $form->addElement('submit', 'sstring', array(
            'ignore'   => true,
            'label'    => 'Add Script',
        ));
        
        if($this->_request->getParam('id')!=null) {
            $form->addElement('hidden', 'scriptID', array(
                'value' => $this->_request->getParam('id'),
            ));
        }
        
        $form->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));
        
        $this -> form = $form;
    }

    public function newAction()
    {    
        $acl = Zend_Registry::get('acl');
        
        if(!$acl->isAllowed(USER_RIGHT, null, 'manage')) {
            $this->view->noaccess = true;
            $this->_forward('error','Error');
            return;
        }
        
        $this -> createForm();
        $this -> view -> form = $this -> form;
    }

    public function saveAction()
    {    
        $acl = Zend_Registry::get('acl');
        $this -> createForm();
         if(!$acl->isAllowed(USER_RIGHT, null, 'manage')) {
            $this->view->noaccess = true;
            $this->_forward('error','Error');
            return;
        }
       
        $form = $this -> form;

        $request = $this->getRequest();
		$data = $request->getPost();
		
		if ($this->getRequest()->isPost() AND $form->isValid($request->getPost())) 
        {
            if($this -> scripts -> scriptExists($data['scriptName']) == 0 OR isset($data['scriptID'])) 
            {
                
                $insert['scriptName']  = $data['scriptName'];
                $insert['Description'] = $data['Description'];
				
				$categories = $this -> scripts -> getCategories();
				$bots = $this -> scripts -> getBots();
				
				if(isset($categories[$data['category']]) AND isset($bots[$data['bot']])) {
					$insert['bot'] = $data['bot'];
					$insert['category'] = $data['category'];
				}
                
                $upload = new Zend_File_Transfer();

                $upload->addValidator('Extension', false, 'jpg,jpeg,gif,png');
                       //->addValidator('IsImage', false);
                
                if(!$upload->isValid('image') AND $upload->isUploaded()) 
                {
                    $errors[]='You can only upload images.';
                }
                else
                {
                    
                   
                    if(isset($data['scriptID'])) {
                        $lastid = $data['scriptID'];
                        $this -> scripts -> editScript($insert, $data['scriptID']);
                    }
                    else {
                        $lastid = $this -> scripts -> addScript($insert);
                    }
                    $imageName = $upload->getFileName('image', null);
                    if($upload->isUploaded()) {
                        if(file_exists(APPLICATION_PATH.'/../images/scripts/'.$lastid.'.gif')) unlink(APPLICATION_PATH.'/../images/scripts/'.$lastid.'.gif');
    
                        $upload -> addFilter('rename', APPLICATION_PATH.'/../images/scripts/'.$lastid.'.gif');
                        $upload -> receive();
                    }
                    $this -> view -> content = 'Script added';
                }
            }
            else $errors[]='Script does already exists';  
        }
        else $errors[]='Please fill all forms';  
        
        if(isset($errors))
        {
            $this -> view -> errors = $errors;
            $this->newAction(); 
            $this->render('new'); 
        }
    }

    public function editAction()
    {    
       $acl = Zend_Registry::get('acl');
       if(!$acl->isAllowed(USER_RIGHT, null, 'manage')) {
            $this->view->noaccess = true;
            $this->_forward('error','Error');
            return ;
        }
        if($this->_request->getParam('id') == null) 
        {
            $this->view->msg = 'Please submit an id';
            return ;
        }
        $data = $this -> scripts -> getScript($this->_request->getParam('id'));
        
        $this -> createForm($data);
        
        $this -> view -> form = $this -> form;
    }

    public function deleteAction()
    {    
        $acl = Zend_Registry::get('acl');
        if(!$acl->isAllowed(USER_RIGHT, null, 'manage')) {
            $this->view->noaccess = true;
            $this->_forward('error','Error');
            return;
        }
        else {
            if($this->_request->getParam('id') == null) 
            {
                $this->view->msg = 'Please submit an id';
                return ;
            }
            $data = $this -> scripts -> getScriptVersions($this->_request->getParam('id'));
            
            $versionModel = new Application_Model_Versions();
            
            $this -> scripts -> delete($this->_request->getParam('id'));
            
            foreach($data as $version) { 
                unlink(APPLICATION_PATH.'/../scriptsfolder/'.$version['serverName']);
                $versionModel->delete($this->_request->getParam($version['versionID']));
            }
            
            $this->view->msg = 'Script deleted';
        }
    }

}















