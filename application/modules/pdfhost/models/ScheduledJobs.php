<?php

/**
 * ScheduledJobs
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Pdfhost_Model_ScheduledJobs extends Zend_Db_Table_Abstract
{
    /**
     * The default table name
     */
    protected $_name = 'scheduledjobs';

    public static function checkJob($joburl){

        $jobModel = new self();
        $select = $jobModel->select();

        $select->where('joburl =?', $joburl);

        $job = $jobModel->fetchRow($select);

        if ($job)
            return $job->jobid;
        else
            return null;
    }

    public static function addJob($jobsysid, $jobroute, $options, $params){
        $queue = new ZendJobQueue();
        $jobQueue = false;

        if ($queue->isJobQueueDaemonRunning()){
            try {
                    $jobQueue = true;

                    $host = $_SERVER['HTTP_HOST'];
                    $secret = md5('14233791');

                    $jobURI = "http://$host/$jobroute&secret=$secret";

                    	//locating job in our database...
                        $existingJobID = Pdfhost_Model_ScheduledJobs::checkJob($jobURI);

                        if ($existingJobID != null){
                         	$jobStatus = $queue->getJobStatus(311);
                         	$jobInfo = $queue->getJobInfo($existingJobID);

                          	if (!($jobStatus['status'] == 3) || !($jobStatus['status'] == 5))
                          	  return false;
                        }

	                    $jobOptions = array('priority' => ZendJobQueue::PRIORITY_NORMAL,
	                                        'name' => $host."_".$jobroute
	                    );

	                    $jobOptions = $jobOptions + $options;

	                    $jobID = $queue->createHttpJob($jobURI, array(), $jobOptions);

	                    $jobModel = new self();

	                    $select = $jobModel->select();
	                    $select->where('jobsysid =?', $jobsysid);

	                    $job = $jobModel->fetchRow($select);

	                    if ($job){
	                        $job->jobid = $jobID;
	                        $job->jobdate = date('Y-m-d H:i:s');
	                        $job->save();
	                    }
	                    else{
	                        $job = $jobModel->createRow();
	                        if ($job){
	                            $job->jobid = $jobID;
	                            $job->jobname = $host."_".$jobroute;
	                            $job->jobsysid = $jobsysid;
	                            $job->joburl = $jobURI;
	                            $job->jobdate = date('Y-m-d H:i:s');
	                            $job->save();
	                        }
	                    }
	                    return true;
            } catch (Exception $e) {
                Zend_Debug::dump($e->getMessage());
                return false;
            }

        }
    }
}
