<?php

require_once 'Zend/Db/Table/Abstract.php';

interface ReportsUtilityFunctions {
     function getExamMark($studentid, $term, $year, $grade);
     function getSubjectExempt($studentid, $term, $year, $grade);
     function getGradeLetter($termMark);
     function getGradeComment($termMark);
     function getCourseExamMark($studentid, $courseid, $term, $year, $grade);
     function gradeRemark($termMark);
}

class Content_Model_Reports	extends Zend_Db_Table_Abstract implements ReportsUtilityFunctions {
		/**
		 * The default table name
		 */
		protected $_name = 'vw_cumreport';

		//data structures for the different types of assessments specified
		private $ClassWorkData = array('id'=>array(), 'mark' =>array(), 'max' => array());
		private $HomeWorkData = array('id'=>array(), 'mark' =>array(), 'max' => array());
		private $GroupWorkData = array('id'=>array(), 'mark' =>array(), 'max' => array());
		private $ProjectWorkData = array('id'=>array(), 'mark' =>array(), 'max' => array());
		private $UnitTestData = array('id'=>array(), 'mark' =>array(), 'max' => array());

		private $StudentReport = array();

		public function buildStudentReport($studentid, $term, $year, $grade, $progress = false){
		    $courses = array();
		    $studentName = '';

		    $this->_primary = array('cl_studentid', 'cl_term', 'cl_year', 'cl_grade');

		    $select = $this->select();

		    $select->from($this, array('Sum(cl_mark) AS Total', '*', 'sum(cl_maxmark) as MTotal' ))
		    ->where('cl_studentid =?', $studentid)
		    ->where('cl_term =?', $term)
		    ->where('cl_year =?', $year)
		    ->where('cl_grade =?', $grade)
		    ->group('cl_course')
		    ->group('cl_type')
		    ->group('cl_studentid');

		    $reportObj = $this->fetchAll($select);

		    if ($reportObj->count() > 0) {

		        if (Content_Model_School::isCurrentPeriod($term, $year))
		            $va = Content_Model_Course::getGradeCourses($grade);
		        else
		            $va = Content_Model_Assignments::pastCourses($term, $year, $grade);

		        foreach ($va as $row){
		            if (in_array($row->cl_courseid, $courses))
		                continue;
		            else
		                $courses[]=$row->cl_courseid;
		        }


		        foreach ($courses as $cKey => $cValue){
		            $rValues[]= $cValue;

		            $CEResults = Content_Model_AssignmentMarks::getAllExemptTotal($studentid, $term, $year, $grade, $cValue ,0);
		            $CE = array();

		            if (count($CEResults) > 0){
		                foreach ($CEResults as $CER) {
		                    $CEE = array('EMT' => $CER->EMarkTotal, 'ET' => $CER->ETotal);
		                    $CE[$cValue]["$CER->cl_type"]  = $CEE;
		                }
		            }

		            foreach ($reportObj as $row) {
		                if ($studentName == '')
		                    $studentName = $row->cl_FirstName.' '. $row->cl_OtherName .' '. $row->cl_LastName;

		                if ($cValue == $row->cl_course){
		                    switch ($row->cl_type) {
		                    	case "CWK":
		                    	    if (count($CEResults) > 0 && isset($CE[$cValue]["$row->cl_type"])){
		                    	        $EM = $CE[$cValue]["$row->cl_type"]["EMT"];
		                    	        $ET = $CE[$cValue]["$row->cl_type"]["ET"];
		                    	    }
		                    	    else{
		                    	        $EM = 0;
		                    	        $ET = 0;
		                    	    }

		                    	    $this->ClassWorkData['id'][] = $cValue;
		                    	    $this->ClassWorkData['mark'][] = $row->Total - $EM;
		                    	    $this->ClassWorkData['max'][$cValue] = $row->MTotal - $ET;
		                    	    break;

		                    	case "HWK":
		                    	    if (count($CEResults) > 0 && isset($CE[$cValue]["$row->cl_type"])){
		                    	        $EM = $CE[$cValue]["$row->cl_type"]["EMT"];
		                    	        $ET = $CE[$cValue]["$row->cl_type"]["ET"];
		                    	    }
		                    	    else{
		                    	        $EM = 0;
		                    	        $ET = 0;
		                    	    }

		                    	    $this->HomeWorkData['id'][] = $cValue;
		                    	    $this->HomeWorkData['mark'][] = $row->Total - $EM;
		                    	    $this->HomeWorkData['max'][$cValue] = $row->MTotal - $ET;

		                    	    break;

		                    	case "PRJ":
		                    	    if (count($CEResults) > 0 && isset($CE[$cValue]["$row->cl_type"])){
		                    	        $EM = $CE[$cValue]["$row->cl_type"]["EMT"];
		                    	        $ET = $CE[$cValue]["$row->cl_type"]["ET"];
		                    	    }
		                    	    else{
		                    	        $EM = 0;
		                    	        $ET = 0;
		                    	    }

		                    	    $this->ProjectWorkData['id'][] = $cValue;
		                    	    $this->ProjectWorkData['mark'][] = $row->Total - $EM;
		                    	    $this->ProjectWorkData['max'][$cValue] = $row->MTotal - $ET;
		                    	    break;

		                    	case "GPW":
		                    	    if (count($CEResults) > 0 && isset($CE[$cValue]["$row->cl_type"])){
		                    	        $EM = $CE[$cValue]["$row->cl_type"]["EMT"];
		                    	        $ET = $CE[$cValue]["$row->cl_type"]["ET"];
		                    	    }
		                    	    else{
		                    	        $EM = 0;
		                    	        $ET = 0;
		                    	    }

		                    	    $this->GroupWorkData['id'][] = $cValue;
		                    	    $this->GroupWorkData['mark'][] = $row->Total - $EM;
		                    	    $this->GroupWorkData['max'][$cValue] = $row->MTotal - $ET;
		                    	    break;

		                    	case "UNT":
		                    	    if (count($CEResults) > 0 && isset($CE[$cValue]["$row->cl_type"])){
		                    	        $EM = $CE[$cValue]["$row->cl_type"]["EMT"];
		                    	        $ET = $CE[$cValue]["$row->cl_type"]["ET"];
		                    	    }
		                    	    else{
		                    	        $EM = 0;
		                    	        $ET = 0;
		                    	    }

		                    	    $this->UnitTestData['id'][] = $cValue;
		                    	    $this->UnitTestData['mark'][] = $row->Total - $EM;
		                    	    $this->UnitTestData['max'][$cValue] = $row->MTotal - $ET;
		                    	    break;

		                    	default:
		                    	    ;
		                    	    break;
		                    }
		                }

		            }

		        }

		        $CWK = $GPW = $PRJ = $UNT = $HWK = $EXM = $Exempts = array();
		        //check for the existence of values of all arrays before combining....

		        if (count($this->ClassWorkData['id']) > 0)	 $CWK = array_combine($this->ClassWorkData['id'], $this->ClassWorkData['mark']);
		        if (count($this->GroupWorkData['id']) > 0)	 $GPW = array_combine($this->GroupWorkData['id'], $this->GroupWorkData['mark']);
		        if (count($this->ProjectWorkData['id']) > 0) $PRJ = array_combine($this->ProjectWorkData['id'], $this->ProjectWorkData['mark']);
		        if (count($this->UnitTestData['id']) > 0)	 $UNT = array_combine($this->UnitTestData['id'], $this->UnitTestData['mark']);
		        if (count($this->HomeWorkData['id']) > 0)	 $HWK = array_combine($this->HomeWorkData['id'], $this->HomeWorkData['mark']);

		        $EXM          =   $this->getExamMark($studentid, $term, $year, $grade);
		        $Exempts      =   $this->getSubjectExempt($studentid, $term, $year, $grade);
		        $SchoolConfig =   Content_Model_School::loadConfigData();

		        foreach ($courses as $cKey => $cValue){
		            //getting the total marks for assignments related to a course
		            $maximum = Content_Model_Assignments::maximumMarks($cValue, $term, $year, $grade);

		            $ReportItem = new stdClass();
		            $ReportItem->cc = $cValue;
		            $ReportItem->stdname = $studentName;
		            $ReportItem->cname = Content_Model_Course::getCourseName($cValue);

		            //Retrieving the raw marks for the computation of the Total Class Mark
		            $ReportItem->CWKT = number_format((array_key_exists($cValue, $CWK) && $CWK[$cValue] != null) ? $CWK[$cValue] : 0, 2);
		            $ReportItem->HWKT = number_format((array_key_exists($cValue, $HWK )  && $HWK[$cValue] != null) ? $HWK[$cValue] : 0 ,2);
		            $ReportItem->GPWT = number_format((array_key_exists($cValue, $GPW ) && $GPW[$cValue] != null) ? $GPW[$cValue] : 0, 2) ;
		            $ReportItem->PRJT = number_format((array_key_exists($cValue, $PRJ) && $PRJ[$cValue] != null) ? $PRJ[$cValue] : 0, 2);
		            $ReportItem->UNTT = number_format((array_key_exists($cValue, $UNT) && $UNT[$cValue] != null) ? $UNT[$cValue] : 0,2);

		            //show the 100 % of the various assignments ON REPORT
		            $ReportItem->CWK = round(number_format((array_key_exists($cValue, $CWK) && $CWK[$cValue] != null) ? ($CWK[$cValue]/$this->ClassWorkData['max'][$cValue]) * 100 : 0, 2));
		            $ReportItem->HWK = round(number_format((array_key_exists($cValue, $HWK )  && $HWK[$cValue] != null) ? ($HWK[$cValue]/$this->HomeWorkData['max'][$cValue])* 100 : 0 ,2));
		            $ReportItem->GPW = round(number_format((array_key_exists($cValue, $GPW ) && $GPW[$cValue] != null) ? ($GPW[$cValue]/$this->GroupWorkData['max'][$cValue])*100 : 0, 2));
		            $ReportItem->PRJ = round(number_format((array_key_exists($cValue, $PRJ) && $PRJ[$cValue] != null) ? ($PRJ[$cValue]/$this->ProjectWorkData['max'][$cValue])*100 : 0, 2));
		            $ReportItem->UNT = round(number_format((array_key_exists($cValue, $UNT) && $UNT[$cValue] != null) ? ($UNT[$cValue]/$this->UnitTestData['max'][$cValue])*100 : 0,2));


		            $a = (array_key_exists($cValue, $this->ClassWorkData['max'])) ? $this->ClassWorkData['max'][$cValue] : 0;
		            $b = (array_key_exists($cValue, $this->HomeWorkData['max'])) ? $this->HomeWorkData['max'][$cValue] : 0;
		            $c = (array_key_exists($cValue, $this->GroupWorkData['max'])) ? $this->GroupWorkData['max'][$cValue] : 0;
		            $d = (array_key_exists($cValue, $this->UnitTestData['max'])) ? $this->UnitTestData['max'][$cValue] : 0;
		            $e = (array_key_exists($cValue, $this->ProjectWorkData['max'])) ? $this->ProjectWorkData['max'][$cValue] : 0;

		            $ReportItem->CWKMax = $a;
		            $ReportItem->HWKMax = $b;
		            $ReportItem->GPWMax = $c;
		            $ReportItem->UNTMax = $d;
		            $ReportItem->PRJMax = $e;

		            if ($a === 0) $ReportItem->CWK = '-';
		            if ($b === 0) $ReportItem->HWK = '-';
		            if ($c === 0) $ReportItem->GPW = '-';
		            if ($d === 0) $ReportItem->UNT = '-';
		            if ($e === 0) $ReportItem->PRJ = '-';

		            $maxTotal = $a + $b + $c + $d + $e;

		            if ($maxTotal != 0){
		                $ReportItem->TCM = round(number_format((($ReportItem->CWKT +  $ReportItem->HWKT + $ReportItem->GPWT + $ReportItem->PRJT + $ReportItem->UNTT)/$maxTotal) * 100,2));  //40%
		                $ReportItem->TCM40 = round(number_format((($ReportItem->CWKT +  $ReportItem->HWKT + $ReportItem->GPWT + $ReportItem->PRJT + $ReportItem->UNTT)/$maxTotal) * $SchoolConfig['classallocate'],2));  //40%
		            }
		            else{
		                $ReportItem->TCM = '-';
		                $ReportItem->TCM40 = '-';
		            }

		            if (Content_Model_School::isCurrentPeriod($term, $year))
		                $ReportItem->isExaminable = Content_Model_Course::isExaminable($cValue, $grade);
		            else
		                $ReportItem->isExaminable = Content_Model_Course::isPastExaminable($term, $year, $cValue, $grade);

		            $ReportItem->Exempt = (array_key_exists($cValue, $Exempts) ? $Exempts[$cValue] : 0);
		            if ($ReportItem->Exempt == true){
		                $ReportItem->CWK = '-';
		                $ReportItem->HWK = '-';
		                $ReportItem->GPW = '-';
		                $ReportItem->UNT = '-';
		                $ReportItem->PRJ = '-';
		                $ReportItem->TM = '-';
		                $ReportItem->TCM ='-';
		                $ReportItem->EXM = '-';
		            }

		            if($ReportItem->isExaminable){
		                $markedover =  Content_Model_Examinations::markedOverReport($term, $year, $cValue, $grade, 0, false);
		                $ReportItem->EXM =0;
		                if ($markedover != 0)
		                    $ReportItem->EXM = round(number_format((array_key_exists($cValue, $EXM) && $EXM[$cValue] != null) ? ($EXM[$cValue]/$markedover)*100 : 0, 2));   //100%
		                $ReportItem->EXM60 = round(number_format((array_key_exists($cValue, $EXM) && $EXM[$cValue] != null) ? $ReportItem->EXM * $SchoolConfig['examallocate']/100 : 0, 2));   //60%
		                $ReportItem->TM = round(number_format($ReportItem->TCM40 + $ReportItem->EXM60,2)) ;

		            }
		            else{
		                $ReportItem->EXM = '-';
		                $ReportItem->TM = $ReportItem->TCM;
		            }

		            if ($progress)
		                $gradeRemark = $this->gradeRemark($ReportItem->TCM);
		            else
		                $gradeRemark = $this->gradeRemark($ReportItem->TM);

		            if ($ReportItem->TM != '-'){
		                $ReportItem->Letter = $gradeRemark->gradeletter;
		                $ReportItem->Comment = $gradeRemark->interpretation;
		            }else {
		                $ReportItem->Letter = '-';
		                $ReportItem->Comment = '-';
		            }

		            $ReportItem->Position = Content_Model_PreparedReport::getStudentPosition(
		                    $studentid,
		                    $grade,
		                    $cValue,
		                    $term,
		                    $year);

		            //Zend_Debug::dump($parent->Position);
		            $this->StudentReport[] = $ReportItem;
		        }

		        Content_Model_PreparedReport::saveReport($studentid, $grade, $term, $year, $this->StudentReport);
		        return $this->StudentReport;
		    }
		    else {
		        return $this->StudentReport = false;
		    }
		}

		public function getExamMark($studentid, $term, $year, $grade){

			$this->_name = 'exammarks';

			$exams = array();
			$exams_K = array();
			$exams_V = array();

			$this->_primary = array('cl_studentid', 'cl_term', 'cl_year', 'cl_grade');

			$select = $this->select();
			$select->from($this,array('cl_courseid','sum(cl_mark) as cl_mark'))
				   ->where('cl_studentid =?', $studentid)
					->where('cl_term =?', $term)
					->where('cl_year =?', $year)
					->where('cl_grade =?', $grade)
					->group('cl_courseid');

			$reportObj = $this->fetchAll($select);

			foreach ($reportObj as $row) {
				$exams_K[] = $row->cl_courseid;
				$exams_V[] = $row->cl_mark;
			}

			if (count($exams_K) > 0)
				return array_combine($exams_K, $exams_V);
			else
				return array();

		}

		public function getSubjectExempt($studentid, $term, $year, $grade){

			$this->_name = 'exammarks';

			$exempt = array();
			$exempt_K = array();
			$exempt_V = array();

			$this->_primary = array('cl_studentid', 'cl_term', 'cl_year', 'cl_grade');

			$select = $this->select();
			$select->where('cl_studentid =?', $studentid)
			->where('cl_term =?', $term)
			->where('cl_year =?', $year)
			->where('cl_grade =?', $grade);

			$reportObj = $this->fetchAll($select);

			foreach ($reportObj as $row) {
				$exempt_K[] = $row->cl_courseid;
				$exempt_V[] = $row->cl_exempt;
			}

			if (count($exempt_K) > 0)
				return array_combine($exempt_K, $exempt_V);
			else
				return array();

		}

		public function getCourseExamMark($studentid, $courseid, $term, $year, $grade){

			$this->_name = 'exammarks';

			$exams = array();
			$exams_K = array();
			$exams_V = array();

			$this->_primary = array('cl_studentid', 'cl_term', 'cl_year', 'cl_grade','cl_courseid');

			$select = $this->select();
			$select->where('cl_studentid =?', $studentid)
			->where('cl_term =?', $term)
			->where('cl_year =?', $year)
			->where('cl_grade =?', $grade)
			->where('cl_courseid =?', $courseid);

			$reportObj = $this->fetchRow($select);

			return $reportObj;
		}

		public function getGradeLetter($TM){
			$interpretation = new self();
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

		public function getGradeComment($TM){
			$interpretation = new self();
			$db = $interpretation->getDefaultAdapter();

			try {
				$stmt = $db->query(
						'select fn_remark(?) as remark',
						array(round($TM))
				);

				$result = $stmt->fetch(Zend_Db::FETCH_OBJ);

				return $result->remark;

			} catch (Exception $e) {
				return $e->getMessage();
			}
		}

		public function gradeRemark($TM){
			$interpretation = new self();
			$db = $interpretation->getDefaultAdapter();

			try {
				$stmt = $db->query(
						'call sp_graderemark(?);',
						array(round($TM))
				);

				$result = $stmt->fetch(Zend_Db::FETCH_OBJ);

				return $result;

			} catch (Exception $e) {
				return $e->getMessage();
			}
		}

		public static function getCommentReport($studentid, $term, $year, $grade){
			$reportModel = new self();
			$reportModel->_name = 'vw_commentreport';
			$reportModel->_primary = array('cl_studentid', 'cl_term', 'cl_year', 'cl_grade');

			$select = $reportModel->select();
			$select->where('cl_studentid =?', $studentid)
			->where('cl_term =?', $term)
			->where('cl_year =?', $year)
			->where('cl_grade =?', $grade);

			$result = $reportModel->fetchAll($select);

			if ($result->count() > 0){
				return $result;
			}
			else
			{

				return null;
			}

		}

		public function courseReport($type, $course, $assignmentid, $term, $year, $grade, $student = -1){

			$this->_primary = array('cl_studentid', 'cl_term', 'cl_year', 'cl_grade');

			$select = $this->select();

			$select->from($this, array('*'))
			->where('cl_type =?', $type)
			->where('cl_term =?', $term)
			->where('cl_year =?', $year)
			->where('cl_grade =?', $grade)
			->where('cl_course =?', $course)
			->where('cl_assignmentid =?', $assignmentid)
			->order('cl_assignmentid');

			if ($student != -1)
				$select->where('cl_studentid =?', $student);

			$reportObj = $this->fetchAll($select);

			return $reportObj;
		}

		public function courseAssignments($type, $course, $term, $year, $grade, $instructor, $student = -1){
			$this->_primary = array('cl_studentid', 'cl_term', 'cl_year', 'cl_grade');

			$select = $this->select();

			$select->distinct(true)
			->from($this, array('cl_assignmentid', 'cl_maxmark'))
			->where('cl_type =?', $type)
			->where('cl_term =?', $term)
			->where('cl_year =?', $year)
			->where('cl_course =?', $course)
			->where('cl_grade =?', $grade)
			->order('cl_assignmentid');

			if ($student != -1)
				$select->where('cl_studentid =?', $student);
			else
				$select->where('cl_instructor =?',$instructor);


			$reportObj = $this->fetchAll($select);

			return $reportObj;
		}

		public function courseAssignmentSummary( $course, $term, $year, $grade, $instructor){
			$this->_primary = array('cl_studentid', 'cl_term', 'cl_year', 'cl_grade');

			$select = $this->select();

			$select->distinct(true)
			->from($this, array('*','sum(cl_mark) as total', 'sum(cl_maxmark) as Mtotal' ))
			->where('cl_term =?', $term)
			->where('cl_year =?', $year)
			->where('cl_grade =?', $grade)
			->where('cl_course =?', $course)->where('cl_instructor =?',$instructor)
			->group('cl_studentid')
			->group('cl_type')
			->order('cl_type');

			$reportObj = $this->fetchAll($select);

			return $reportObj;
		}

		public static function locked($term, $year, $grade){
			$lockedrecord = new self();
			$lockedrecord->_name = 'tb_lockedrecords';

			$select = $lockedrecord->select();
			$select->where('term =?', $term)->where('year =?', $year)->where('grade =?', $grade);

			$result = $lockedrecord->fetchRow($select);

			if ($result){
				return $result->locked;
			}
			else
				return false;
		}

		public static function gradingSystem(){
			$iModel = new self();
			$iModel->_name = 'gradeinterpretation';
			return $iModel->fetchAll();
		}

		public static function publishreports($term, $year, $grade){
			//publishing of results will refresh the position of students after

		}
}

