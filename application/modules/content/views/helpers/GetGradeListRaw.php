<?php
/**
 *
 * @author Edzordzinam
 * @version
 */
require_once 'Zend/View/Interface.php';

/**
 * GetGradeListRaw helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_GetGradeListRaw
{

    /**
     *
     * @var Zend_View_Interface
     */
    public $view;
    protected $gradeLevels = '';
    /**
     */
    public function getGradeListRaw ($list, $trim = false)
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
				if ($i == 1){
				   if(!$trim)
				        $result .= "<span class='label label-inverse label-mini'>$value</span>";
				    else{
				        $s = substr($value, 0, 7);
				        $result .= "<span class='label label-inverse label-mini'> $s</span>";
				    }
				}
			    else{
			       if(!$trim)
			    	$result .= " | <span class='label label-inverse label-mini'>$value</span>";
			       else{
			           $s = substr($value, 0, 7);
			           $result .= " | <span class='label label-inverse label-mini'>$s</span>";
			       }
			    }
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
