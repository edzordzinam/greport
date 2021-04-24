<?php
/**
 *
 * @author Edzordzinam
 * @version
 */
require_once 'Zend/View/Interface.php';

/**
 * GetGradeListSubject helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_GetGradeListSubject
{

    /**
     *
     * @var Zend_View_Interface
     */
    public $view;

    protected $gradeLevels = '';

    /**
     */
    public function getGradeListSubject ($list, $courseid)
    {
	// helper
		$this->gradeLevels = Content_Model_GradeLevels::gradelevels();

		$result = '';

		$list = json_decode($list);

		if (count($list) == 0){
		    return $result;
		}

		$i = 0;

		foreach ($this->gradeLevels as $key => $value) {
			if (in_array($key, $list)){
				$i++;
				if ($i == 1)
			       $result .= "<span class='label label-info label-mini'>$value</span><button class='close' style='float : none;' onclick='$.fn.deleteClass($key,$courseid)'>&times;</button>";
			    else
			    	$result .= " | <span class='label label-info label-mini'>$value</span><button class='close' style='float : none;' onclick='$.fn.deleteClass($key,$courseid)'>&times;</button>";
			}
		}

		return $result;
    }

    /**
     * Sets the view field
     *
     * @param $view Zend_View_Interface
     */
    public function setView (Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
