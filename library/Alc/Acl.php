<?php


class Alc_Acl extends Zend_Controller_Plugin_Abstract {


	  public function preDispatch(Zend_Controller_Request_Abstract $request){
	  	 //set up acl

	  	$acl = new Zend_Acl();

        //ADDING OF RESOURCES

	  	//Dynamically adding the roles from roles table in the database.
        $cacheManager =  Zend_Registry::get('cacheMan');
	    $cache = $cacheManager->getCache('gcache');
	    $cacheId = 'aclconfig';

	  	$ACLConfig = $cache->load($cacheId);
        //Zend_Debug::dump($ACLConfig);

	  	if ($ACLConfig){
	  	    foreach ($ACLConfig->resources as $resource) {
	  	        $acl->addResource(new Zend_Acl_Resource($resource->controller));
	  	    }

	  	    $subordinate = -1;// used in adding the access levels of a subordinate to a superior

	  	    //ADDING OF ROLES
	  	    foreach ($ACLConfig->roles as $role){
	  	        if ($role->role == -1){
	  	            $acl->addRole(new Zend_Acl_Role($role->role));
	  	        }
	  	        else{
	  	            $acl->addRole(new Zend_Acl_Role($role->role), $subordinate);
	  	        };
	  	        $subordinate = $role->role;
	  	    }

	  	    $acl->deny();

	  	    foreach ($ACLConfig->acl_list as $acl_level){
	  	        $acl->allow($acl_level->role, $acl_level->controller, $acl_level->action);
	  	    }

	  	}

	  	$acl->allow(null, array('error'));

	  	$module = $request->getModuleName();
	  	$controller = $request->getControllerName();
	  	$action = $request->getActionName();

 	   //fetch the current user
 	   $auth = Zend_Auth::getInstance();
 	   if ($auth->hasIdentity()){
 	   	 $identity = $auth->getIdentity();
 	   	 $role = $identity->role;

 	   	 $authns = new Zend_Session_Namespace($auth->getStorage()->getNamespace());
 	   	 // set an expiration on the Zend_Auth namespace where identity is held
 	   	 $authns->setExpirationSeconds(60*10); // expire auth storage after 10 min

 	   	 //auto generate password for this user because their password is insecure
 	   	 if (!$identity->secured){
 	   	 	if( ( array( 'content', 'instructor', 'generatepwd' ) !== array( $module, $controller, $action ) )) {
 	   	 	  if ($action != 'validateform' && $action != 'logout' && $action != 'update-pass')
 	   	 		$this->getResponse()->setRedirect('/generatepwd');
 	   	 	}
 	   	 }
 	   }
 	   else{
 	   	 $role = Alc_Roles::UNAUTHORIZED;
 	   }
	 	   			//$role = 0;
	 		 	   	if(!$acl->isAllowed($role, $controller, $action)){
				 	   	 if ($role == Alc_Roles::UNAUTHORIZED){
				 	   	 	if( ( array( 'content', 'index', 'index' ) !== array( $module, $controller, $action ) )) {
				 	   	 	    if ($this->getRequest()->isXMLHttpRequest()){
                                    $this->getResponse()->setHttpResponseCode(401);
                                    $this->getResponse()->sendHeaders();
                                    exit;
				 	   	 	    }
				 	   	 	    $this->getResponse()->setRedirect( '/home' )->sendResponse( );
				 	   	 	}
				 	   	 }
				 	   	 else {
				 	   	 	$request->setModuleName('security');
				 	   	 	$request->setControllerName('error');
				 	   	 	$request->setActionName('noauth');
				 	   	 	$request->setParam('allowed', $acl->isAllowed($role,$controller, $action));
				 	   	 }
 		 	       }else{
 		 	       }

	  }
}

?>