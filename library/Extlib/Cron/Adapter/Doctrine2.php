<?php

namespace Extlib\Cron\Adapter;

use Extlib\Cron\Adapter\Job\JobInterface,
    Extlib\Cron\Adapter\Job\HistoryInterface;

/**
 * Cron adapter class for Doctrine ORM (v2.x)
 * 
 * @category    Extlib
 * @package     Extlib\Cron
 * @subpackage  Extlib\Cron\Adapter
 * @author      Lukasz Ciolecki <ciolecki.lukasz@gmail.com>
 * @copyright   Copyright (c) 2013 Lukasz Ciolecki (mart)
 */
class Doctrine2 implements AdapterInterface
{
    /* EntityManager configuration */
    const ENTITY_MANAGER = 'entityManager';

    /* Cron job entity configuration */
    const JOB_ENTITY_NAME = 'jobEntityName';
    const JOB_COL_STATUS = 'jobStatusColumn';
    const JOB_COL_DATE_LAST_RUN = 'jobDateLastRunColumn';
    const JOB_COL_PRIORITY = 'jobPriorityColumn';

    /* History cron job configuration */
    const HISTORY_ENTITY_NAME = 'historyEntityName';

    /**
     * Instance of EntityManager
     * 
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em = null;

    /**
     * Cron job entity name
     * 
     * @var string
     */
    protected $jobEntityName = null;

    /**
     * Cron job status name
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
     * History cron job entity name
     *
     * @var string
     */
    protected $historyEntityName = null;

    /**
     * Instance of current date
     * 
     * @var \DateTime
     */
    protected $date = null;

    /**
     * Instance of constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            switch ($key) {
                case self::ENTITY_MANAGER:
                    if (!$value instanceof \Doctrine\Common\Persistence\ObjectManager) {
                        throw new \InvalidArgumentException(sprintf('EntityManager must be an instance of "Doctrine\Common\Persistence\ObjectManager", given "%s".', get_class($value)));
                    }
                    $this->em = $value;
                    break;
                case self::JOB_ENTITY_NAME:
                    $this->jobEntityName = (string) $value;
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
                case self::HISTORY_ENTITY_NAME:
                    $this->historyEntityName = (string) $value;
                    break;
                default:
                    break;
            }
        }

        $this->date = new \DateTime('now');
    }

    /**
     * Implementation \Extlib\Cron\Adapter\AdapterInterface::getJobs
     * 
     * @parma int $limit
     * @return array
     */
    public function getJobs($limit = null)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('job')
                ->from($this->jobEntityName, 'job')
                ->where(sprintf("job.%s = :job_status_active", $this->jobStatusColumn))
                ->orderBy(sprintf('job.%s', $this->jobPriorityColumn), 'DESC')
                ->addOrderBy(sprintf('job.%s', $this->jobDateLastRunColumn), 'ASC')
                ->setParameter('job_status_active', JobInterface::STATUS_ACTIVE);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Implementation \Extlib\Cron\Adapter\AdapterInterface::startProcessing
     * 
     * @param \ArrayObject $jobs
     * @return \Extlib\Cron\Adapter\Doctrine2
     */
    public function startProcessing(\ArrayObject $jobs)
    {
        foreach ($jobs as $job) {
            $job->setStatus(JobInterface::STATUS_PROCESSING);
            $job->setDateLastRun($this->date);
        }

        $this->em->flush();

        return $this;
    }

    /**
     * Implementation \Extlib\Cron\Adapter\AdapterInterface::finishProcessing
     * 
     * @param \ArrayObject $jobs
     * @return \Extlib\Cron\Adapter\Doctrine2
     */
    public function finishProcessing(\ArrayObject $jobs)
    {
        foreach ($jobs as $job) {
            $job->setStatus(JobInterface::STATUS_ACTIVE);
        }

        $this->em->flush();

        return $this;
    }

    /**
     * Implementation \Extlib\Cron\Adapter\AdapterInterface::history
     * 
     * @param int $status
     * @param \Extlib\Cron\Adapter\Job\JobInterface $job
     * @param string $message
     * @return \Extlib\Cron\Adapter\Doctrine2
     * @throws \InvalidArgumentException
     */
    public function history($status, JobInterface $job, $message = null)
    {
        $history = new $this->historyEntityName();
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

        $this->em->persist($history);
        $this->em->flush();

        return $this;
    }
}
