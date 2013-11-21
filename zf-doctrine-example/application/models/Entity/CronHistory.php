<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CronHistories
 *
 * @ORM\Table(name="cron_histories", indexes={@ORM\Index(name="fk_table1_cron_jobs_idx", columns={"cron_job_id"})})
 * @ORM\Entity
 */
class CronHistory implements \Extlib\Cron\Adapter\Job\HistoryInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="run_date", type="datetime", nullable=false)
     */
    private $runDate;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var \Entity\CronJob
     *
     * @ORM\ManyToOne(targetEntity="Entity\CronJob")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cron_job_id", referencedColumnName="id")
     * })
     */
    private $cronJob;

    public function setJob(\Extlib\Cron\Adapter\Job\JobInterface $job)
    {
        $this->cronJob = $job;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setRunDate(\DateTime $runDate)
    {
        $this->runDate = $runDate;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

}
