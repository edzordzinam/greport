<?php

class Custom_Grades {

    public $GRADES = array(

						);

	public static function getGradeName($gradeid){

		if ($gradeid == -1)
		   return 'Not Applicable';

	    if ($gradeid == -2)
		   return 'Subject Teacher';

		return Content_Model_GradeLevels::getGradeName($gradeid);
	}

	public static function GRADELEVELS(){
		return Content_Model_GradeLevels::gradelevels();
	}

	public static function getPrimaryLevels(){
		$grades = new self();
		$gvalues = array_keys(Content_Model_GradeLevels::gradelevels());
		$splice = array_slice($gvalues, 0);
		return $splice;
	}

	public static function getFeesBand($grade){

		if ($grade < 100){ //A Grades
			if ($grade < 6 )
				return 'A';
			else if ($grade > 11)
				return 'C';
			else if ($grade < 12 && $grade > 5)
				return 'B';
		}
		else { //B grades
		if ($grade < 106 )
			return 'A';
		else if ($grade > 111)
			return 'C';
		else if ($grade < 112 && $grade > 105)
			return 'B';
		}
	}

	public static function getFeesBandNames($grade){
		if ($grade < 6 )
			return 'A - Pre-School';
		else if ($grade > 11)
			return 'C - Secondary';
		else if ($grade < 12 && $grade > 5)
			return 'B - Primary';
	}

}

?>