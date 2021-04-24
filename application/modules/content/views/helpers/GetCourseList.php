<?php
/**
 *
 * @author Edzordzinam
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * GetCourseList helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_GetCourseList {
	
	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;
	
	/**
	 */
	public function getCourseList($courselist, $limit = -1) {
		
		// helper
		$courses = Content_Model_Course::getCourseNames();
		
		$result = '';
		
		$list = json_decode($courselist);
		$i = 0;
		
		foreach ($courses as $key => $value) {
			if (in_array($key, $list)){
				$i++;
				if($i == $limit){
					$result .= '=> <a>more</a>';
					break;
				}
				if ($i == 1)
			       $result .= $value ;		
			    else
			    	$result .= '; ' .$value ;
			}
		}
		
		return $result;
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
