<?php

/**
 * ScheduledJobs
 *
 * @author Edzordzinam
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_ScheduledJobs extends Zend_Db_Table_Abstract
{

    /**
     * The default table name
     */
    protected $_name = 'scheduledjobs';


    public static function checkJob($jobsysid){
        /***
         * jobsysids 100 -- billstudents
         * 101 -- computeaccountbalances
         */

        $jobModel = new self();
        $select = $jobModel->select();

        $select->where('jobsysid =?', $jobsysid);

        $job = $jobModel->fetchRow($select);

        if ($job)
            return true;
        else
            return false;


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

                    $jobOptions = array('priority' => ZendJobQueue::PRIORITY_NORMAL,
                                        'name' => $host."_".$jobroute
                    );

                    $jobOptions = $jobOptions + $options;

                    // ;

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
