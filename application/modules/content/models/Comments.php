<?php

/**
 * Comments
 *  
 * @author Edzordzinam
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_Comments extends Zend_Db_Table_Abstract
{

    /**
     * The default table name
     */
    protected $_name = 'comments';
	
    public static function loadClassTeacherComment($studentid, $grade, $term, $year){
    	$cModel = new self();
    	$select = $cModel->select();
    	
    	$select->where('cl_studentid =?', $studentid)
    			->where('cl_term =?', $term)
    			->where('cl_year =?',$year)
    			->where('cl_grade =?', $grade);
    	
    	$result = $cModel->fetchRow($select);
    	if ($result)
    		return $result->cl_ctcomment;
    	else 
    		return "No class teacher's remarks available";
    }
    
    public static function updateClassTeacherComment($studentid, $grade, $term, $year, $comment){
	    $cModel = new self();
	    $select = $cModel->select();
	     
		$select->where('cl_studentid =?', $studentid)
			->where('cl_term =?', $term)
			->where('cl_year =?', $year)
			->where('cl_grade =?', $grade);

		$commentRow = $cModel->fetchRow($select);

		if ($commentRow){
			//it means a record has been located... then it must be updated with the new content
			$commentRow->cl_studentid = $studentid;
			$commentRow->cl_term = $term;
			$commentRow->cl_grade = $grade;
			$commentRow->cl_year = $year;
			$commentRow->cl_ctcomment = $comment;
			$commentRow->save();
			return $commentRow;
		}
		else{
			//no record was found and as such an entry must be made into the database for the selected period
			$newCommentRow = $cModel->createRow();
			$newCommentRow->cl_studentid = $studentid;
			$newCommentRow->cl_term = $term;
			$newCommentRow->cl_year = $year;
			$newCommentRow->cl_grade = $grade;
			$newCommentRow->cl_ctcomment = $comment;
			$newCommentRow->save();
			return $newCommentRow;
		}
	    
    }

}
