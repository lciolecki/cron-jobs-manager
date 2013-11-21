<?php

namespace Extlib\Cron\Adapter;

use Extlib\Cron\Adapter\Job\JobInterface,
    Extlib\Cron\Adapter\Job\HistoryInterface;

/**
 * Cron adapter class for Doctrine ORM (v1.x)
 * 
 * @category    Extlib
 * @package     Extlib\Cron
 * @subpackage  Extlib\Cron\Adapter
 * @author      Lukasz Ciolecki <ciolecki.lukasz@gmail.com>
 * @copyright   Copyright (c) 2013 Lukasz Ciolecki (mart)
 */
class Doctrine implements AdapterInterface
{
    /* Connection name */
    const CONNECTION_NAME = 'doctrineConnectionName';

    /* Cron job configuration */
    const JOB_TABLE_NAME = 'jobTableName';
    const JOB_COL_STATUS = 'jobStatusColumn';
    const JOB_COL_DATE_LAST_RUN = 'jobDateLastRunColumn';
    const JOB_COL_PRIORITY = 'jobPriorityColumn';

    /* History cron job configuration */
    const HISTORY_TABELE_NAME = 'historyTableName';

    /**
     * Instance of connection
     * 
     * @var \Doctrine_Connection
     */
    protected $connection = null;

    /**
     * Cron job table name
     * 
     * @var string
     */
    protected $jobTableName = null;

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
     * History cron job table name
     *
     * @var string
     */
    protected $historyTableName = null;

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
                case self::CONNECTION_NAME:
                    $this->connection = \Doctrine_Manager::getInstance()->getConnection($value);
                    break;
                case self::JOB_TABELE_NAME:
                    $this->jobTableName = (string) $value;
                    break;
                case self::JOB_COL_STATUS:
                    $this->jobStatusColumn = (string) $value;
                    break;
                case self::JOB_COL_DATE_LAST_RUN:
                    $this->jobDateLastRunColumn = (string) $value;
                    break;
                case self::JOB_COL_PRIORITY:
                    $this->taskPriorityColumn = (string) $value;
                    break;
                case self::HISTORY_TABELE_NAME:
                    $this->historyTableName = (string) $value;
                    break;
                default:
                    break;
            }
        }

        if (null === $this->connection) {
            $this->connection = \Doctrine_Manager::getInstance()->getCurrentConnection();
        }

        $this->date = new \DateTime('now');
    }

    /**
     * Implementation \Extlib\Cron\Adapter\AdapterInterface::getJobs
     * 
     * @parma int $limit
     * @return \Doctrine_Collection
     */
    public function getJobs($limit = null)
    {
        $query = \Doctrine_Query::create($this->connection)
                ->from($this->jobTableName)
                ->where("$this->jobStatusColumn = ?", JobInterface::STATUS_ACTIVE)
                ->orderBy("$this->jobPriorityColumn DESC")
                ->addOrderBy("$this->jobDateLastRunColumn ASC");

        if (null !== $limit) {
            $query->limit($limit);
        }

        return $query->execute(array(), \Doctrine_Core::HYDRATE_RECORD);
    }

    /**
     * Implementation \Extlib\Cron\Adapter\AdapterInterface::startProcessing
     * 
     * @param \ArrayObject $jobs
     * @return \Extlib\Cron\Adapter\Doctrine
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
     * @return \Extlib\Cron\Adapter\Doctrine
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
     * @return \Extlib\Cron\Adapter\Doctrine
     */
    public function history($status, JobInterface $job, $message = null)
    {
        $history = new $this->historyTableName();
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
