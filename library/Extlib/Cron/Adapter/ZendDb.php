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
class ZendDb implements AdapterInterface
{
    /* Adapter options */
    const ADAPTER = 'zendDbAdapter';
    
    /* Cron job configuration */
    const JOB_TABLE = 'jobTable';
    const JOB_COL_STATUS = 'jobStatusColumn';
    const JOB_COL_DATE_LAST_RUN = 'jobDateLastRunColumn';
    const JOB_COL_PRIORITY = 'jobPriorityColumn';

    /* Zend db table rows configuration */
    const JOB_ROW_CLASS = 'jobRowClass';
    const HISTORY_ROW_CLASS = 'historyRowClass';

    /**
     * Instance of Zend Db Adapter
     * 
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $adapter = null;
    
    /**
     * Cron job zend db table row class name
     * 
     * @var string
     */
    protected $jobRowClass =  null;

    /**
     * History zend db table row name class
     *
     * @var string
     */
    protected $historyRowClass  = null;

    /**
     * Cron jobs table names
     * 
     * @var string
     */
    protected $jobTable = null;
    
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
                case self::ADAPTER:
                    $this->setAdapter($value);
                    break;
                case self::JOB_TABLE:
                    $this->jobTable = (string) $value;
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
                case self::JOB_ROW_CLASS:
                    $this->setJobRowClass($value);
                    break;
                case self::HISTORY_ROW_CLASS:
                    $this->setHistoryRowClass($value);
                    break;
                default:
                    break;
            }
        }

        $this->date = new \DateTime('now');
    }
    
    /**
     * Get job row class name
     * 
     * @return string
     */
    public function getJobRowClass()
    {
        return $this->jobRowClass;
    }

    /**
     * Get history cron class name
     * 
     * @return string
     */
    public function getHistoryRowClass()
    {
        return $this->historyRowClass;
    }

    /**
     * Set cron jobs row class name
     * 
     * @param string $jobRowClass
     * @return \Extlib\Cron\Adapter\ZendDb
     */
    public function setJobRowClass($jobRowClass)
    {
        if (!in_array('Zend_Db_Table_Row_Abstract', class_parents($jobRowClass))) {
            throw new \InvalidArgumentException(sprintf('Job row class must be instance of Zend_Db_Table_Row_Abstract, given %s.', get_class($historyRowClass)));
        }
 
        $this->jobRowClass = $jobRowClass;
        return $this;
    }

    /**
     * Set cron history row class name
     * 
     * @param string $historyRowClass
     * @return \Extlib\Cron\Adapter\ZendDb
     */
    public function setHistoryRowClass($historyRowClass)
    {
        if (!in_array('Zend_Db_Table_Row_Abstract', class_parents($historyRowClass))) {
            throw new \InvalidArgumentException(sprintf('History job row class must be instance of Zend_Db_Table_Row_Abstract, given %s.', get_class($historyRowClass)));
        }
        
        $this->historyRowClass = $historyRowClass;
        return $this;
    }
        
    /**
     * Get adapter
     * 
     * @return \Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set db adapter
     * 
     * @param \Zend_Db_Adapter_Abstract $adapter
     * @return \Extlib\Cron\Adapter\ZendDb
     */
    public function setAdapter(\Zend_Db_Adapter_Abstract $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Implementation \Extlib\Cron\Adapter\AdapterInterface::getJobs
     * 
     * @parma int $limit
     * @return \Extlib\Cron\Adapter\ZendDb
     */
    public function getJobs($limit = null)
    {
        $select = $this->getAdapter()->select();
        $select->from($this->jobTable)
               ->where("$this->jobStatusColumn = ?", JobInterface::STATUS_ACTIVE)
               ->order(array("$this->jobPriorityColumn DESC", "$this->jobDateLastRunColumn ASC"));
        
        if ($limit !== null) {
            $select->limit($limit);
        }
        
        $jobs = array();
        foreach ($select->query()->fetchAll() as $result) {
            $job = $this->createJobRowObject($result);
            $jobs[] = $job;
        }

        return $jobs;
    }

    /**
     * Implementation \Extlib\Cron\Adapter\AdapterInterface::startProcessing
     * 
     * @param \ArrayObject $jobs
     * @return \Extlib\Cron\Adapter\ZendDb
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
     * @return \Extlib\Cron\Adapter\ZendDb
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
     * @return \Extlib\Cron\Adapter\ZendDb
     */
    public function history($status, JobInterface $job, $message = null)
    {
        $history = $this->createHistoryRowObject();
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
    
    /**
     * Get new history row object
     * 
     * @return \Zend_Db_Table_Row_Abstract
     */
    protected function createHistoryRowObject()
    {
        return new $this->historyRowClass();
    }
    
    /**
     * Get new job row object
     * 
     * @param array $data
     * @return \Extlib\Cron\Adapter\jobRowClass
     */
    protected function createJobRowObject(array $data = array())
    {
        return new $this->jobRowClass(array('data' => $data, 'stored'  => true));
    }
}
