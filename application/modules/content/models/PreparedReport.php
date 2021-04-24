<?php

/**
 * PreparedReport
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_PreparedReport extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = 'preparedreport';


	public static function saveReport($studentid, $grade, $term, $year, $report){

		$preReport = new self();

		if ($preReport->isReportInitialized($studentid, $grade, $term, $year))
			$preReport->deleteReport($studentid, $grade, $term, $year);

			foreach ($report as $row) {
				$reportRow = $preReport->createRow();
					if ($reportRow){
						$reportRow->cl_studentid = $studentid;
						$reportRow->cl_courseid = $row->cc;
						$reportRow->cl_term = $term;
						$reportRow->cl_grade = $grade;
						$reportRow->cl_year = $year;
						$reportRow->HWK = floatval($row->HWK) ;
						$reportRow->UNT = floatval($row->UNT);
						$reportRow->GPW = floatval($row->GPW);
						$reportRow->PRJ = floatval($row->PRJ);
						$reportRow->CWK = floatval($row->CWK);
						$reportRow->EXM = floatval($row->EXM);
						$reportRow->TCM = floatval($row->TCM);
						$reportRow->TM =  floatval($row->TM);
						$reportRow->save();
					}
			}
	}

	public static function loadReportDetail($studentid, $course, $grade, $term, $year){
		$pModel = new self();
		$select = $pModel->select();

		$select->where('cl_studentid =?', $studentid)
				->where('cl_courseid =?', $course)
				->where('cl_term in (?)', $term)
				->where('cl_year in (?)', $year);

		$results = $pModel->fetchAll($select);

		if ($results)
			return $results;
		else
			return null;
	}

	public function isReportInitialized($studentid, $grade, $term, $year){
		$this->_name = 'preparedreport';

		$select = $this->select();
		$select->where("cl_studentid = ?", $studentid)
		->where('cl_term = ?', $term)
		->where('cl_year = ?', $year)
		->where('cl_grade =?', $grade);

		$count = $this->fetchAll($select)->count();

		if ($count > 0) {
			//exams already initialized
			return true;
		}
		else
		{
			return false;
		}

	}

	public function deleteReport($studentid, $grade, $term, $year){

		$select = $this->select();

		$select->where("cl_studentid = ?", $studentid)
		->where('cl_term = ?', $term)
		->where('cl_year = ?', $year)
		->where('cl_grade =?', $grade);

		$count = $this->fetchAll($select)->count();

		if ($count > 0){
			$this->delete(array(
					'cl_studentid =?' => $studentid,
					'cl_term = ?' =>  $term,
					'cl_year = ?' =>  $year,
					'cl_grade =?' => $grade
			));
		}
	}

	public static function getStudentPosition($studentid, $grade, $courseid, $term, $year){
		//
		$pModel = new self();
		$db = $pModel->getDefaultAdapter();

		try {
			$stmt = $db->query(
					'select fn_studentposition(?,?,?,?,?) as position',
					array($term,$year, $grade,$studentid, $courseid)
			);

			$result = $stmt->fetch(Zend_Db::FETCH_OBJ);
			return $result->position;

		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

}
