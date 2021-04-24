<?php
/**
 *
 * @author Edzordzinam
 * @version
 */
require_once 'Zend/View/Interface.php';

/**
 * GetGradeList helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_GetGradeList {

	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;

	protected $gradeLevels = '';

	/**
	 */
	public function getGradeList($list, $courseid, $instructor) {

		// helper
		$this->gradeLevels = Content_Model_GradeLevels::gradelevels();

		$result = '';

		$list = json_decode($list);

		$i = 0;

		foreach ($this->gradeLevels as $key => $value) {
			if (in_array($key, $list)){
				$i++;
				if ($i == 1)
			       $result .= "<span class='label label-info label-mini'>$value</span><button class='close' style='float : none;' onclick='$.fn.deleteSubjectClass($key,$courseid,$instructor)'>&times;</button>";
			    else
			    	$result .= " | <span class='label label-info label-mini'>$value</span><button class='close' style='float : none;' onclick='$.fn.deleteSubjectClass($key,$courseid,$instructor)'>&times;</button>";
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
