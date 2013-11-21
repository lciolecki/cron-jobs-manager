<?php

namespace Extlib\Cron\Adapter\Job;

/**
 * Cron job interface
 * 
 * @category    Extlib
 * @package     Extlib\Adapter
 * @subpackage  Extlib\Adapter\Job
 * @author      Lukasz Ciolecki <ciolecki.lukasz@gmail.com>
 * @copyright   Copyright (c) 2013 Lukasz Ciolecki (mart)
 */
interface JobInterface
{
    /* Adapter job status */
    const STATUS_NOACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_PROCESSING = 2;

    /**
     * Get cron job identifier
     * 
     * @return int
     */
    public function getIdentifier();

    /**
     * Get cro job name
     * 
     * @return string 
     */
    public function getName();

    /**
     * Get cron job task class name
     * 
     * @return string
     */
    public function getTaskClassName();

    /**
     * Get cron job task params
     * 
     * @return stdClass
     */
    public function getParams();

    /**
     * Ger cron job descriptio 
     * 
     * @return string
     */
    public function getDescription();

    /**
     * Get cron job interval runtime in seconds
     * 
     * @return int
     */
    public function getRunTime();

    /**
     * Get cron job date last run
     * 
     * @return \DateTime
     */
    public function getDateLastRun();

    /**
     * Set cron job date last run (Y-m-D H:i)
     * 
     * @param \DateTime $dateLastRun
     */
    public function setDateLastRun(\DateTime $dateLastRun);

    /**
     * Set cron job status
     * 
     * @param int $status
     */
    public function setStatus($status);
}
