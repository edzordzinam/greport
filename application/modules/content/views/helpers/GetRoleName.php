<?php
/**
 *
 * @author Edzordzinam
 * @version
 */
require_once 'Zend/View/Interface.php';

/**
 * GetRoleName helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_GetRoleName {

	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;

	/**
	 */
	public function getRoleName($role) {
		// TODO Auto-generated Zend_View_Helper_GetRoleName::getRoleName()
		// helper
		switch ($role) {
			case 40 :
				return 'Front Desk.';
			break;

			case 50 :
				return 'Instructor';
			break;

			case 100:
				return 'Principal';
			break;

			case 110:
				return 'Accountant';
			break;

			case 200 :
				return 'Root';
			break;
		    default:
				;
			break;
		}

		return $role;
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
