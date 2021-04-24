<?php
/**
 *
 * @author Edzordzinam
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * getGradeLetter helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_getGradeLetter {
	
	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;
	
	/**
	 */
	public function getGradeLetter($TM) {
		// TODO Auto-generated Zend_View_Helper_getGradeLetter::getGradeLetter()
		// helper
		$interpretation = new Content_Model_Reports();
		$db = $interpretation->getDefaultAdapter();
		
		try {
			$stmt = $db->query(
					'select fn_letter(?) as letter',
					array(round($TM))
			);
				
			$result = $stmt->fetch(Zend_Db::FETCH_OBJ);
		
			return $result->letter;
				
		} catch (Exception $e) {
			return $e->getMessage();
		}
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
