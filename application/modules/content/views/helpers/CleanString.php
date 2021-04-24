<?php
/**
 *
 * @author Edzordzinam
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * CleanString helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_CleanString {
	
	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;
	
	/**
	 */
	public function cleanString($string) {
		// TODO Auto-generated Zend_View_Helper_CleanString::cleanString()
		// helper
			$s = preg_replace('/[\x00-\x1F\x7F]/', '', $string);  //removing control charge
		    $s = preg_replace('#\s{2,}#',' ', $s);  //removing extra spaces
		    $s = trim($s);
		return $s;
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
