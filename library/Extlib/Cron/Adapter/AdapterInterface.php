<?php

namespace Extlib\Cron\Adapter;

use Extlib\Cron\Adapter\Job\JobInterface;

/**
 * Cron adapter interface
 * 
 * @category    Extlib
 * @package     Extlib\Cron
 * @subpackage  Extlib\Cron\Adapter
 * @author      Lukasz Ciolecki <ciolecki.lukasz@gmail.com>
 * @copyright   Copyright (c) 2013 Lukasz Ciolecki (mart)
 */
interface AdapterInterface
{

    /**
     * Return collections of cron jobs to do
     * 
     * @param int $limit
     * @return \ArrayIterator
     */
    public function getJobs($limit);

    /**
     * Start processing jobs (lock jobs)
     * 
     * @param \ArrayObject $jobs
     */
    public function startProcessing(\ArrayObject $jobs);

    /**
     * Finish processing jobs (unlock jobs)
     * 
     * @param \ArrayObject $tasks
     */
    public function finishProcessing(\ArrayObject $jobs);

    /**
     * History method (logging)
     * 
     * @param int $status
     * @param \Extlib\Cron\Adapter\Job\JobInterface $job
     * @param string $message
     */
    public function history($status, JobInterface $job, $message = null);
}
