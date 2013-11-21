<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CronJobs
 *
 * @ORM\Table(name="cron_jobs")
 * @ORM\Entity
 */
class CronJob implements \Extlib\Cron\Adapter\Job\JobInterface
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="task_class_name", type="string", length=255, nullable=false)
     */
    private $taskClassName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_last_run", type="datetime", nullable=false)
     */
    private $dateLastRun;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="priority", type="smallint", nullable=false)
     */
    private $priority;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="run_time", type="integer", nullable=false)
     */
    private $runTime;

    /**
     * @var string
     *
     * @ORM\Column(name="params", type="json_array", nullable=true)
     */
    private $params = null;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=1024, nullable=true)
     */
    private $description;

    public function getDateLastRun()
    {
        return $this->dateLastRun;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getIdentifier()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParams()
    {
        if ($this->params) {
            return array();
        }
        
        return (array) $this->params;
    }

    public function getRunTime()
    {
        return $this->runTime;
    }

    public function getTaskClassName()
    {
        return $this->taskClassName;
    }

    public function setDateLastRun(\DateTime $dateLastRun)
    {
        $this->dateLastRun = new \DateTime($dateLastRun->format('Y-m-d H:i'));
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

}
