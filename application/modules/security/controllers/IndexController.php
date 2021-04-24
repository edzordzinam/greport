<?php

class Security_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $mobileContext = $this->_helper->getHelper('MobileContext');
        $mobileContext->addActionContext('authenticate')
                      ->addActionContext('logout')
                      ->initContext();
    }

    public function indexAction()
    {
        // action body
        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()){
            $this->view->identity = $auth->getIdentity();
        }
        
        $this->view->schoolname = Content_Model_School::getSchoolName();
    }

    public function createAction()
    {
        // action body
        $userform = new Security_Form_User();

        if ($this->getRequest()->isPost()){

            if ($userform->isValid($_POST)){
                $userModel = new Security_Model_User();
                $userModel->createUser(
                        $userform->getValue('username'),
                        $userform->getValue('password'),
                        $userform->getValue('firstname'),
                        $userform->getValue('lastname'),
                        $userform->getValue('role')
            				);
                return $this->_forward('list');
            }
        }

        $userform->setAction('/security/index/create');
        $this->view->form = $userform;


    }

    public function listAction()
    {
        // action body
        $currentUsers = Security_Model_User::getUsers();
        if ($currentUsers->count() > 0){
            $this->view->users = $currentUsers;
        }
        else
            $this->view->users = null;
    }

    public function updateAction()
    {
        // action body
        $userform = new Security_Form_User();
        $userform->setAction('/security/index/update');
        $userform->removeElement('password');

        $userModel = new Security_Model_User();
        if ($this->_request->isPost()){

            if ($userform->isValid($_POST)){
                $userModel->updateUser(
                        $userform->getValue('id'),
                        $userform->getValue('username'),
                        $userform->getValue('firstname'),
                        $userform->getValue('lastname'),
                        $userform->getValue('role')
                );
                return $this->_forward('list');
            }
        }
        else{
            $id = $this->_request->getParam('uid');
            $currentUser = $userModel->find($id)->current();
            $userform->populate($currentUser -> toArray());
        }

        $this->view->form = $userform;

    }

    public function passwordAction()
    {
        // action body
        $passwordForm = new Security_Form_User();
        $passwordForm->setAction('/security/index/password');
        $passwordForm->removeElement('firstname');
        $passwordForm->removeElement('lastname');
        $passwordForm->removeElement('username');
        $passwordForm->removeElement('role');

        $userModel = new Security_Model_User();
        if ($this->_request->isPost()){
            if($passwordForm->isValid($_POST)){
                $userModel->updatePassword(
                        $passwordForm->getValue('id'),
                        $passwordForm->getValue('username'),
                        $passwordForm->getValue('password')
                );

                return $this->_forward('list');
            }
        }
        else
        {
            $id = $this->_request->getParam('id');
            $currentUser = $userModel->find($id)->current();
            $passwordForm->populate($currentUser -> toArray());
        }

        $this->view->form = $passwordForm;
    }

    public function deleteAction()
    {
        // action body
        $id = $this->_request->getParam('id');
        $userModel = new Security_Model_User();
        $userModel->deleteUser($id);
        return $this->_forward('list');
    }

    public function loginAction()
    {
        $this->_helper->layout()->setLayout('loginlayout');
    }

    public function logoutAction()
    {
        // action body
        $this->_helper->layout()->setLayout('loginlayout');
        $authAdapter = Zend_Auth::getInstance();
        $authAdapter->clearIdentity();

        if (!$this->_request->getParam('mobile'))
            $this->_redirect('/home');
    }

    public function authenticateAction(){

        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        if($this->_request->isPost()){
            //$data = $_POST;
		try {
			
            //setting up the auth adapter
            //get the default db adapter
            $db = Zend_Db_Table::getDefaultAdapter();

            $authAdapter = new Zend_Auth_Adapter_DbTable($db, 'instructors', 'username', 'password');

            //set the username and password
            $authAdapter->setIdentity($_POST['username']);
            //$authAdapter->setCredential(md5($_POST['password']));
            $authAdapter->setCredential($_POST['password']);
            //authenticate
            $result = $authAdapter->authenticate();

            //Zend_Debug::dump($_POST);

            if ($result->isValid()){
                //store the username, first and last names of the user...
                
                $auth = Zend_Auth::getInstance();
                $auth->getStorage()->clear();
                $data = $authAdapter->getResultRowObject(null,'password');

                //retrieving the current term and year and appending it to the
                //profile data hence making it global...

                if ($data->active){
                   //loading the appropriate menu structure for the system
                    $items = new Content_Model_Menu($data->role);
                    $data->menu_items = $items->getItems();
                    
                    if (md5(' ') == $_POST['password'] || md5($_POST['username']) == $_POST['password']){
                    	$data->secured = false;
                    }
                    else
                    	$data->secured = true;

                    $auth->getStorage()->write($data);

                    $url = "/home";
                    echo Zend_Json::encode(array('success'=> 1, 'url'=> $url));
                }
                else{
                    $authAdapter = Zend_Auth::getInstance();
                    $authAdapter->clearIdentity();
                    echo Zend_Json::encode(array('success'=> -1));
                }
            }
            else
            {
                //$this->view->loginMessage = "Sorry, your username or password was incorrect";
                echo  Zend_Json::encode(array('success'=> 0));
            }
        
        } catch (Exception $e) {
        	$this->getResponse()->setHttpResponseCode(501);
            $this->getResponse()->sendHeaders();
        }
        }
    }

}

