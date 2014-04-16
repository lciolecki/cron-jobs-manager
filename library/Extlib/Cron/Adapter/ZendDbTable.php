<?php

namespace Extlib\Cron\Adapter;

use Extlib\Cron\Adapter\Job\JobInterface,
    Extlib\Cron\Adapter\Job\HistoryInterface;

/**
 * Cron adapter class for Zend Framework ORM
 * 
 * @category    Extlib
 * @package     Extlib\Cron
 * @subpackage  Extlib\Cron\Adapter
 * @author      Lukasz Ciolecki <ciolecki.lukasz@gmail.com>
 * @copyright   Copyright (c) 2014 Lukasz Ciolecki (mart)
 */
class ZendDbTable implements AdapterInterface
{
    /* Cron job configuration */
    const JOBS_TABLE = 'jobsTable';
    const JOB_COL_STATUS = 'jobStatusColumn';
    const JOB_COL_DATE_LAST_RUN = 'jobDateLastRunColumn';
    const JOB_COL_PRIORITY = 'jobPriorityColumn';

    /* History cron job configuration */
    const HISTORIES_TABLE = 'historiesTable';

    /**
     * Cron job table
     * 
     * @var \Zend_Db_Table_Abstract
     */
    protected $jobsTable = null;

    /**
     * Cron job status column name
     * 
     * @var string 
     */
    protected $jobStatusColumn = null;

    /**
     * Cron job date last run column name
     * 
     * @var string 
     */
    protected $jobDateLastRunColumn = null;

    /**
     * Cron job priority column name
     * 
     * @var string
     */
    protected $jobPriorityColumn = null;

    /**
     * Histories cron job table
     *
     * @var \Zend_Db_Table_Abstract
     */
    protected $historiesTable = null;

    /**
     * Instance of current date
     * 
     * @var \DateTime
     */
    protected $date = null;

    /**
     * Instance of construct
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            switch ($key) {
                case self::JOBS_TABLE:
                    $this->setJobsTable($value);
                    break;
                case self::JOB_COL_STATUS:
                    $this->jobStatusColumn = (string) $value;
                    break;
                case self::JOB_COL_DATE_LAST_RUN:
                    $this->jobDateLastRunColumn = (string) $value;
                    break;
                case self::JOB_COL_PRIORITY:
                    $this->jobPriorityColumn = (string) $value;
                    break;
                case self::HISTORIES_TABLE:
                    $this->setHistoriesTable($value);
                    break;
                default:
                    break;
            }
        }

        $this->date = new \DateTime('now');
    }
    
    /**
     * Get jobs table
     * 
     * @return \Zend_Db_Table_Abstract
     */
    public function getJobsTable()
    {
        return $this->jobsTable;
    }

    /**
     * Get histories table
     * 
     * @return \Zend_Db_Table_Abstract
     */
    public function getHistoriesTable()
    {
        return $this->historiesTable;
    }

    /**
     * Set jobs table
     * 
     * @param \Zend_Db_Table_Abstract $jobsTable
     * @return \Extlib\Cron\Adapter\ZendDbTable
     */
    public function setJobsTable(\Zend_Db_Table_Abstract $jobsTable)
    {
        $this->jobsTable = $jobsTable;
        return $this;
    }

    /**
     * Set histories table
     * 
     * @param \Zend_Db_Table_Abstract $historiesTable
     * @return \Extlib\Cron\Adapter\ZendDbTable
     */
    public function setHistoriesTable(\Zend_Db_Table_Abstract $historiesTable)
    {
        $this->historiesTable = $historiesTable;
        return $this;
    }

    
    /**
     * Implementation \Extlib\Cron\Adapter\AdapterInterface::getJobs
     * 
     * @parma int $limit
     * @return \Extlib\Cron\Adapter\ZendDbTable
     */
    public function getJobs($limit = null)
    {
        return $this->getJobsTable()->fetchAll(
            array("$this->jobStatusColumn = ?" => JobInterface::STATUS_ACTIVE), 
            array("$this->jobPriorityColumn DESC", "$this->jobDateLastRunColumn ASC"),
            $limit
        );
    }

    /**
     * Implementation \Extlib\Cron\Adapter\AdapterInterface::startProcessing
     * 
     * @param \ArrayObject $jobs
     * @return \Extlib\Cron\Adapter\ZendDbTable
     */
    public function startProcessing(\ArrayObject $jobs)
    {
        foreach ($jobs as $job) {
            $job->setStatus(JobInterface::STATUS_PROCESSING);
            $job->setDateLastRun($this->date);
            $job->save();
        }

        return $this;
    }

    /**
     * Implementation \Extlib\Cron\Adapter\AdapterInterface::finishProcessing
     * 
     * @param \ArrayObject $jobs
     * @return \Extlib\Cron\Adapter\ZendDbTable
     */
    public function finishProcessing(\ArrayObject $jobs)
    {
        foreach ($jobs as $job) {
            $job->setStatus(JobInterface::STATUS_ACTIVE);
            $job->save();
        }

        return $this;
    }

    /**
     * Implementacja metody tworzacej wpis historii zadania
     * 
     * @param int $status
     * @param \Extlib\Cron\Adapter\Job\JobInterface $job
     * @param string $message
     * @return \Extlib\Cron\Adapter\ZendDbTable
     */
    public function history($status, JobInterface $job, $message = null)
    {
        $history = $this->getHistoriesTable()->createRow();
        if (!$history instanceof HistoryInterface) {
            throw new \InvalidArgumentException(sprintf('History must be an instance of "Extlib\Cron\Adapter\Job\HistoryInterface", "%s" given.', get_class($history)));
        }

        if (!in_array($status, array(HistoryInterface::STATUS_OK, HistoryInterface::STATUS_WARNING, HistoryInterface::STATUS_ERROR))) {
            throw new \InvalidArgumentException(sprintf('Unknown status - "%s".', $status));
        }

        $history->setJob($job);
        $history->setStatus($status);
        $history->setRunDate($this->date);

        if (null !== $message) {
            $history->setMessage($message);
        }

        $history->save();
        return $this;
    }
}
