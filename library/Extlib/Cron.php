<?php

namespace Extlib;

use Extlib\Cron\Adapter\AdapterInterface,
    Extlib\Cron\Adapter\Job\JobInterface,
    Extlib\Cron\Adapter\Job\HistoryInterface,
    Extlib\Cron\Task\TaskAbstract;

/**
 * Cron class
 * 
 * @category    Extlib
 * @author      Lukasz Ciolecki <ciolecki.lukasz@gmail.com>
 * @copyright   Copyright (c) 2013 Lukasz Ciolecki (mart)
 */
final class Cron
{
    /* Default configuration */
    const DEFAULT_MAX_TASKS = 3;
    const DATE_LOG_FORMAT = 'Y-m-d H:i:s';

    /**
     * Array of options
     * 
     * @var array
     */
    private $options = array(
        'max_jobs' => self::DEFAULT_MAX_TASKS
    );

    /**
     * Instance of adapter
     * 
     * @var \Extlib\Cron\Adapter\AdapterInterface
     */
    private $adapter = null;

    /**
     * Collections of available cron jobs
     * 
     * @var \ArrayObject
     */
    private $jobs;

    /**
     * Instance of construct
     * 
     * @param \Extlib\Cron\Adapter\AdapterInterface $adapter
     * @param array $options
     */
    public function __construct(AdapterInterface $adapter, array $options = array())
    {
        $this->setAdapter($adapter);
        $this->jobs = new \ArrayObject();
        $this->options = array_merge($this->options, $options);

        foreach ($this->getAdapter()->getJobs($this->options['max_jobs']) as $job) {
            $this->addJob($job);
        }

        $this->getAdapter()->startProcessing($this->getJobs());
    }

    /**
     * Cron execute method
     */
    public function execute()
    {
        foreach ($this->getJobs() as $key => $job) {
            try {
                $this->console($job, $key + 1);
                $task = $this->createTask($job);
                $return = $task->run();
                $this->getAdapter()->history(HistoryInterface::STATUS_OK, $job, is_string($return) ? $return : null);
            } catch (\Exception $exc) {
                $this->log($job, $exc);
            }
        }

        $this->getAdapter()->finishProcessing($this->getJobs());
    }
    
    /**
     * Create task class from cron job
     * 
     * @param \Extlib\Cron\Adapter\Job\JobInterface $job
     * @return \Extlib\Cron\Task\TaskAbstract
     * @throws \InvalidArgumentException
     */
    public function createTask(JobInterface $job)
    {
        $className = $job->getTaskClassName();
        $task = new $className($this, $job->getParams());

        if (!$task instanceof TaskAbstract) {
            throw new \InvalidArgumentException('Task must be an instance of "Extlib\Cron\Task\TaskAbstract", given "%s".', get_class($task));
        }
        
        return $task;
    }

    /**
     * Get cron adapter
     * 
     * @return \Extlib\Cron\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set cron adapter
     * 
     * @param \Extlib\Cron\Adapter\AdapterInterface $adapter
     * @return \Extlib\Cron
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Get execute cron jobs
     * 
     * @return \ArrayObject
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * Set collections of execute cron jobs
     * 
     * @param \ArrayObject $jobs
     * @return \Extlib\Cron
     */
    public function setJobs(\ArrayObject $jobs)
    {
        $this->jobs = new \ArrayObject();
        foreach ($jobs as $job) {
            $this->addJob($job);
        }
        
        return $this;
    }
    
    /**
     * Add job to collection of execute cron jobs
     * 
     * @param \Extlib\Cron\Adapter\Job\JobInterface $job
     * @return \Extlib\Cron
     */
    public function addJob(JobInterface $job)
    {
        $date = $job->getDateLastRun();
        if (null === $date || (int) $date->getTimestamp() + (int) $job->getRunTime() <= time()) {
            $this->getJobs()->append($job);
        }

        return $this;
    }
        
    /**
     * Cron log method
     * 
     * @param \Extlib\Cron\Adapter\Job\JobInterface $job
     * @param \Exception $exc
     * @return \Extlib\Cron
     */
    public function log(JobInterface $job ,\Exception $exc)
    {
        if (HistoryInterface::STATUS_WARNING === $exc->getCode()) {
            $this->getAdapter()->history(HistoryInterface::STATUS_WARNING, $job, $exc->getMessage());
        } else {
            $this->getAdapter()->history(HistoryInterface::STATUS_ERROR, $job, $exc->getMessage());
        }
        
        return $this;
    }

    /**
     * Cron console log method
     * 
     * @param \Extlib\Cron\Adapter\Job\JobInterface $job
     * @param int $number
     * @return \Extlib\Cron
     */
    public function console(JobInterface $job, $number)
    {
        echo sprintf(
            '%s. Run task: %s (%s) - %s.', $number, $job->getName(), $job->getTaskClassName(), $job->getDateLastRun()->format(self::DATE_LOG_FORMAT)
        ) . PHP_EOL;

        return $this;
    }
}
