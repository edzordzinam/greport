<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

/* 	protected function _initDoctype()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('XHTML1_STRICT');
	}
 */
	protected function _initView() {

		$view = new Zend_View();

		//enabling the integration of JQuery into the view.
		$view->addHelperPath('EasyBib/View/Helper', 'EasyBib_View_Helper');

		ZendX_JQuery::enableView($view);

		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer->setView($view);

		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

		//initialization of the layout applied to the view instantiated
		$this->bootstrap('layout');
		$layout = $this->getResource('layout');
		$view = $layout->getView();

		return $view;

	}

	protected function _initMyRoutes() {

	    $this->bootstrap('frontcontroller');
	    $front = Zend_Controller_Front::getInstance();
	    $router = $front->getRouter();

	    // get cache for config files
	    $cacheManager = $this->bootstrap('cachemanager')->getResource('cachemanager');
	    $cache = $cacheManager->getCache('gcache');

	    if ($front->getParam('clean') == true)
	    	$cache->clean(Zend_Cache::CLEANING_MODE_ALL);

	    $cacheId = 'routesini';

	    // $t1 = microtime(true);
	    $myRoutes = $cache->load($cacheId);

	    if (!$myRoutes) {
	        // not in cache or route.ini was modified.
	        $myRoutes = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini','development');
	        $cache->save($myRoutes, $cacheId);
	    }
	    // $t2 = microtime(true);
	    // echo ($t2-$t1); // just to check what is time for cache vs no-cache scenerio

	    $router->addConfig($myRoutes, 'routes');
	}

	protected function _initACL(){
	    require APPLICATION_PATH . '/modules/security/models/Roles.php' ;
	    require APPLICATION_PATH . '/modules/security/models/Resources.php' ;

	    //setting up db adapter

	    $this->bootstrap('db'); // Bootstrap the db resource from configuration
	    $db = $this->getResource('db'); // get the db object here, if necessary

	    // get cache for config
	    $cacheManager = $this->bootstrap('cachemanager')->getResource('cachemanager');
	    $cache = $cacheManager->getCache('gcache');
	    $cacheId = 'aclconfig';

        $ACLConfig = $cache->load($cacheId);

        if (!$ACLConfig) {
            $ACLConfig = new stdClass();
            $ACLConfig->roles = Security_Model_Roles::loadRoles();
            $ACLConfig->resources = Security_Model_Resources::loadResources();
            $ACLConfig->acl_list = Security_Model_Resources::loadAcl_list();
            $cache->save($ACLConfig, $cacheId);
        }

        //injecting cachemanager into the registry....
        Zend_Registry::set('cacheMan', $cacheManager);
	}

	protected function _initSchoolDB(){
	    $host = $_SERVER['HTTP_HOST'];
	    $host = explode('.', $host);
	    $subdomain = $host[0];

	    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);

	    // set database configuration parameters
	    $adapter = $config->resources->db->adapter;
	    $params = $config->resources->db->params;

	    $db = $this->getResource('db');

	    if ($subdomain == 'www' || $subdomain == ''){
	    	$db = Zend_Db::factory('Pdo_Mysql', array(
	    			'host'     => $params->host,
	    			'username' => $params->username,
	    			'password' => $params->password,
	    			'dbname'   => $params->dbname,
	    			'profiler' => array('enabled' => (APPLICATION_ENV == 'production') ? false : true, 'class' => 'Zend_Db_Profiler_Firebug')
	    	));
	    }else{
	    	$db = Zend_Db::factory('Pdo_Mysql', array(
	    			'host'     => $params->host,
	    			'username' => $params->username,
	    			'password' => $params->password,
	    			'dbname'   => (APPLICATION_ENV == 'production') ? $subdomain : $params->dbname,
	    			//'dbname' => $params->dbname,
	    			'profiler' => array('enabled' => (APPLICATION_ENV == 'production') ? false : true, 'class' => 'Zend_Db_Profiler_Firebug')
	    	));
	    }

	    $frontController = Zend_Controller_Front::getInstance();

	    $frontController->setParam('identifier', (APPLICATION_ENV == 'production') ? $subdomain : $params->dbname);
	    //$frontController->setParam('identifier', 'bethelinter');
	    Zend_Db_Table_Abstract::setDefaultAdapter($db);
	}

	protected function _initUAgent(){
	    //bootstrapping the layout and user agent identification and redirections.
	    $frontController = Zend_Controller_Front::getInstance();

	    //bootstrapping the useragent resource...
	    //$this->bootstrap('useragent');
	    $layout = $this->getResource('layout');

	    //$bootstrap = $this->getInvokeArg('bootstrap');
	    //$userAgent = $this->getResource('useragent');
	   // $device = $userAgent->getDevice();

	    $loggedIn = Zend_Auth::getInstance()->hasIdentity();
	    if ($loggedIn){
	        if ($frontController->getParam('mobileLayout') === "1"){
        	    if ($device->getFeature('is_mobile') && $device->getFeature('is_tablet') == 'false'){
        	            $suffix = $layout->getViewSuffix();
        	            $layout->setViewSuffix('mobile.'.$suffix);

        	            if ($frontController->getParam('mobileViews') == "1") {
        	                Zend_Controller_Action_HelperBroker::getStaticHelper('MobileContext')->enable();
        	            }
        	    }
        	    else if ($device->getFeature('is_tablet') && $device->getFeature('is_tablet') && $device->getFeature('is_wireless_device')){
        	            $suffix = $layout->getViewSuffix();
        	            $layout->setViewSuffix('tablet.'.$suffix);

        	            if ($frontController->getParam('mobileViews') == "1") {
        	                Zend_Controller_Action_HelperBroker::getStaticHelper('MobileContext')->enable();
        	            }
        	    }
	        }
	    }
	    else{
    	     //$layout->setLayout('loginlayout_scaffold');
    	     $layout->setLayout('loginlayout_1');
	    	 //$layout->setLayout('alter-loginlayout');
    	     if ($frontController->getParam('mobileLayout') === "1"){
        	     if ($device->getFeature('is_mobile') && $device->getFeature('is_tablet') == 'false'){
        	         //automatically applying a suffix to original file in order to load appropriate layout and views
        	            $suffix = $layout->getViewSuffix();
        	            $layout->setViewSuffix('mobile.'.$suffix);

        	            if ($frontController->getParam('mobileViews') == "1") {
        	                Zend_Controller_Action_HelperBroker::getStaticHelper('MobileContext')->enable();
        	            }

        	    }
        	    else if ($device->getFeature('is_tablet') && $device->getFeature('is_tablet') && $device->getFeature('is_wireless_device')){
        	            $suffix = $layout->getViewSuffix();
        	            $layout->setViewSuffix('mobile.'.$suffix);

        	            if ($frontController->getParam('mobileViews') == "1") {
        	                Zend_Controller_Action_HelperBroker::getStaticHelper('MobileContext')->enable();
        	            }

        	    }
    	     }
	    }
	}

    protected function _initZFDebug()
	 {
/* 	     $autoloader = Zend_Loader_Autoloader::getInstance();
	    $autoloader->registerNamespace('ZFDebug');

	    $options = array(
	    		'plugins' => array('Variables',
	                    'File' => array('base_path' => APPLICATION_PATH . '/../'),
	                    'Memory',
	                    'Time',
	                    'Exception',
	                    'Html',
	            		'Database' => array(),

	    ));

	    # Instantiate the database adapter and setup the plugin.
	    # Alternatively just add the plugin like above and rely on the autodiscovery feature.
	    if ($this->hasPluginResource('db')) {
	    $this->bootstrap('db');
	        $db = $this->getPluginResource('db')->getDbAdapter();
	        $options['plugins']['Database']['adapter'] = $db;
	    }

    # Setup the cache plugin
	        if ($this->hasPluginResource('cache')) {
	        $this->bootstrap('cache');
	        $cache = $this->getPluginResource('cache')->getDbAdapter();
	        $options['plugins']['Cache']['backend'] = $cache->getBackend();
    }

    $debug = new ZFDebug_Controller_Plugin_Debug($options);

	        $this->bootstrap('frontController');
	        $frontController = $this->getResource('frontController');
	        $frontController->registerPlugin($debug);*/
	}

	protected function _initPDFHost(){
/* 		$frontController = Zend_Controller_Front::getInstance();
		if (APPLICATION_ENV == 'development')
			$frontController->setParam('pdfhost', 'cent.pdf'); */
	}

	protected function _initRequestLocation(){
		if (!($_SERVER['REQUEST_URI'] == '/notermerror'))
			$_SESSION['lastUrl'] = $_SERVER['REQUEST_URI'];
	}

}

