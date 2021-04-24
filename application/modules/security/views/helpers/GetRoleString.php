<?php
/**
 *
 * @author Edzordzinam
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * GetRoleString helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_GetRoleString {
	
	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;
	
	/**
	 */
	public function getRoleString($role) {
		
		// helper
			switch ($role) {
			case 0:
			 return 'Super User';
			break;
			
			case 1:
			 return 'Administrator';
			break;
			
			case 2: 
			 return 'FrontDesk';
			break;
			
			case 3:
			 return 'Reporting';
			break;
			
			case 4:
			 return 'Principal';
			break;
			
			case 5:
			 return 'Instructor';
			break;
			default:
			  return 'Unidentified Role';
			break;
		};
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
