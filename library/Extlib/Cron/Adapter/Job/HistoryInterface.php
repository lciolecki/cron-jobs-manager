<?php

namespace Extlib\Cron\Adapter\Job;

/**
 * Cron job history interface 
 * 
 * @category    Extlib
 * @package     Extlib\Adapter
 * @subpackage  Extlib\Adapter\Job
 * @author      Lukasz Ciolecki <ciolecki.lukasz@gmail.com>
 * @copyright   Copyright (c) 2013 Lukasz Ciolecki (mart)
 */
interface HistoryInterface
{
    /* History cron job status */
    const STATUS_OK = 1;
    const STATUS_WARNING = 2;
    const STATUS_ERROR = 3;

    /**
     * Set cron job
     * 
     * @param \Extlib\Cron\Adapter\JobInterface $job
     */
    public function setJob(JobInterface $job);

    /**
     * Set history cron job status
     * 
     * @param int $status
     */
    public function setStatus($status);

    /**
     * Set history cron job message (catch error)
     * 
     * @param string $message
     */
    public function setMessage($message);

    /**
     * Set cron job run date
     * 
     * @param \DateTime $runDate
     */
    public function setRunDate(\DateTime $runDate);
}
