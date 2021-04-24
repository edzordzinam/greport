<?php
/**
 *
 * @author Edzordzinam
 * @version
 */
require_once 'Zend/View/Interface.php';

/**
 * GetGradeName helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_GetGradeName {

	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;

	protected $gradeLevels = '';


	/**
	 */
	public function getGradeName($gradelevel) {
		// helper

		if ($gradelevel == -1)
		   return 'Not Applicable';

	    if ($gradelevel == -2)
		   return 'Subject Teacher';

		return Content_Model_GradeLevels::getGradeName($gradelevel);
	}

	/**
	 * Sets the view field
	 *
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
