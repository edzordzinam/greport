<?php

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_Reports	extends Zend_Db_Table_Abstract {
		/**
		 * The default table name
		 */
		protected $_name = 'vw_cumreport';

		public function buildStudentReport($studentid, $term, $year, $grade, $progress = false){

			$CWK_K = array(0);
			$CWK_V = array(0);
			$CWK_M = array(0);

			$HWK_K = array(0);
			$HWK_V = array(0);
			$HWK_M = array(0);

			$GPW_K = array(0);
			$GPW_V = array(0);
			$GPW_M = array(0);

			$PRJ_K = array(0);
			$PRJ_V = array(0);
			$PRJ_M = array(0);

			$UNT_K = array(0);
			$UNT_V = array(0);
			$UNT_M = array(0);

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

									$CWK_K[]= $cValue;
									$CWK_V[]=$row->Total - $EM ;
									$CWK_M[$cValue]=$row->MTotal - $ET;
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

									$HWK_K[]= $cValue;
									$HWK_V[]=$row->Total - $EM;
									$HWK_M[$cValue]=$row->MTotal  - $ET;

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

									$PRJ_K[]= $cValue;
									$PRJ_V[]=$row->Total  - $EM;
									$PRJ_M[$cValue]=$row->MTotal - $ET;

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

									$GPW_K[]= $cValue;
									$GPW_V[]=$row->Total  - $EM;
									$GPW_M[$cValue]=$row->MTotal - $ET;

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

									$UNT_K[]= $cValue;
									$UNT_V[]=$row->Total  - $EM;
									$UNT_M[$cValue]=$row->MTotal - $ET;

									break;

								default:
									;
									break;
							}
						}

					}

				}


				$CWK = array();
				$GPW = array();
				$PRJ = array();
				$UNT = array();
				$HWK = array();
				$EXM = array();
				$Exempts = array();
				//check for the existence of values of all arrays before combining....

				if (count($CWK_K) > 0)	 $CWK = array_combine($CWK_K, $CWK_V);
				if (count($GPW_K) > 0)	 $GPW = array_combine($GPW_K, $GPW_V);
				if (count($PRJ_K) > 0)	 $PRJ = array_combine($PRJ_K, $PRJ_V);
				if (count($UNT_K) > 0)	 $UNT = array_combine($UNT_K, $UNT_V);
				if (count($HWK_K) > 0)	 $HWK = array_combine($HWK_K, $HWK_V);

				$EXM = $this->getExamMark($studentid, $term, $year, $grade);
				$Exempts = $this->getSubjectExempt($studentid, $term, $year, $grade);
				$SchoolConfig = Content_Model_School::loadConfigData();

				$parentArray = array();

				foreach ($courses as $cKey => $cValue){
					//getting the total marks for assignments related to a course

					$maximum = Content_Model_Assignments::maximumMarks($cValue, $term, $year, $grade);

					$parent = new stdClass();
					$parent->cc = $cValue;
					$parent->stdname = $studentName;
					$parent->cname = Content_Model_Course::getCourseName($cValue);

					//Retrieving the raw marks for the computation of the Total Class Mark
					$parent->CWKT = number_format((array_key_exists($cValue, $CWK) && $CWK[$cValue] != null) ? $CWK[$cValue] : 0, 2);
					$parent->HWKT = number_format((array_key_exists($cValue, $HWK )  && $HWK[$cValue] != null) ? $HWK[$cValue] : 0 ,2);
					$parent->GPWT = number_format((array_key_exists($cValue, $GPW ) && $GPW[$cValue] != null) ? $GPW[$cValue] : 0, 2) ;
					$parent->PRJT = number_format((array_key_exists($cValue, $PRJ) && $PRJ[$cValue] != null) ? $PRJ[$cValue] : 0, 2);
					$parent->UNTT = number_format((array_key_exists($cValue, $UNT) && $UNT[$cValue] != null) ? $UNT[$cValue] : 0,2);

					//show the 100 % of the various assignments ON REPORT
					$parent->CWK = round(number_format((array_key_exists($cValue, $CWK) && $CWK[$cValue] != null) ? ($CWK[$cValue]/$CWK_M[$cValue]) * 100 : 0, 2));
					$parent->HWK = round(number_format((array_key_exists($cValue, $HWK )  && $HWK[$cValue] != null) ? ($HWK[$cValue]/$HWK_M[$cValue])* 100 : 0 ,2));
					$parent->GPW = round(number_format((array_key_exists($cValue, $GPW ) && $GPW[$cValue] != null) ? ($GPW[$cValue]/$GPW_M[$cValue])*100 : 0, 2));
					$parent->PRJ = round(number_format((array_key_exists($cValue, $PRJ) && $PRJ[$cValue] != null) ? ($PRJ[$cValue]/$PRJ_M[$cValue])*100 : 0, 2));
					$parent->UNT = round(number_format((array_key_exists($cValue, $UNT) && $UNT[$cValue] != null) ? ($UNT[$cValue]/$UNT_M[$cValue])*100 : 0,2));


					$a = (array_key_exists($cValue, $CWK_M)) ? $CWK_M[$cValue] : 0;
					$b = (array_key_exists($cValue, $HWK_M)) ? $HWK_M[$cValue] : 0;
					$c = (array_key_exists($cValue, $GPW_M)) ? $GPW_M[$cValue] : 0;
					$d = (array_key_exists($cValue, $UNT_M)) ? $UNT_M[$cValue] : 0;
					$e = (array_key_exists($cValue, $PRJ_M)) ? $PRJ_M[$cValue] : 0;

					$parent->CWKMax = $a;
					$parent->HWKMax = $b;
					$parent->GPWMax = $c;
					$parent->UNTMax = $d;
					$parent->PRJMax = $e;

					if ($a == 0) $parent->CWK = '-';
					if ($b == 0) $parent->HWK = '-';
					if ($c == 0) $parent->GPW = '-';
					if ($d == 0) $parent->UNT = '-';
					if ($e == 0) $parent->PRJ = '-';

					$maxTotal = $a + $b + $c + $d + $e;

					if ($maxTotal != 0){
						$parent->TCM = round(number_format((($parent->CWKT +  $parent->HWKT + $parent->GPWT + $parent->PRJT + $parent->UNTT)/$maxTotal) * 100,2));  //40%
						$parent->TCM40 = round(number_format((($parent->CWKT +  $parent->HWKT + $parent->GPWT + $parent->PRJT + $parent->UNTT)/$maxTotal) * $SchoolConfig['classallocate'],2));  //40%
					}
					else{
						$parent->TCM = '-';
						$parent->TCM40 = '-';
					}

					if (Content_Model_School::isCurrentPeriod($term, $year))
							$parent->isExaminable = Content_Model_Course::isExaminable($cValue, $grade);
					else
						    $parent->isExaminable = Content_Model_Course::isPastExaminable($term, $year, $cValue, $grade);

					$parent->Exempt = (array_key_exists($cValue, $Exempts) ? $Exempts[$cValue] : 0);
					if ($parent->Exempt == true){
						$parent->CWK = '-';
						$parent->HWK = '-';
						$parent->GPW = '-';
						$parent->UNT = '-';
						$parent->PRJ = '-';
						$parent->TM = '-';
						$parent->TCM ='-';
						$parent->EXM = '-';
					}

					if($parent->isExaminable){
 						$markedover =  Content_Model_Examinations::markedOverReport($term, $year, $cValue, $grade, 0, false);
						$parent->EXM =0;
						if ($markedover != 0)
							$parent->EXM = round(number_format((array_key_exists($cValue, $EXM) && $EXM[$cValue] != null) ? ($EXM[$cValue]/$markedover)*100 : 0, 2));   //100%
						$parent->EXM60 = round(number_format((array_key_exists($cValue, $EXM) && $EXM[$cValue] != null) ? $parent->EXM * $SchoolConfig['examallocate']/100 : 0, 2));   //60%
						$parent->TM = round(number_format($parent->TCM40 + $parent->EXM60,2)) ;

						}
					else{
						$parent->EXM = '-';
						$parent->TM = $parent->TCM;
					}

					if ($progress)
						$gradeRemark = $this->gradeRemark($parent->TCM);
					else
						$gradeRemark = $this->gradeRemark($parent->TM);

					if ($parent->TM != '-'){
						$parent->Letter = $gradeRemark->gradeletter;
						$parent->Comment = $gradeRemark->interpretation;
					}else {
						$parent->Letter = '-';
						$parent->Comment = '-';
					}

			 		$parent->Position = Content_Model_PreparedReport::getStudentPosition(
									$studentid,
									$grade,
									$cValue,
									$term,
									$year);

					//Zend_Debug::dump($parent->Position);
					$parentArray[] = $parent;
				}

				Content_Model_PreparedReport::saveReport($studentid, $grade, $term, $year, $parentArray);

				return $parentArray;
			}
			else {
				return $parentArray = false;
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
			->where('cl_grade', $grade);

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

