<?php

namespace Extlib\Cron\Task;

/**
 * Cron job abstract task name - base class of 
 * 
 * @category    Extlib
 * @package     Extlib\Cron
 * @subpackage  Extlib\Cron\Task
 * @author      Lukasz Ciolecki <ciolecki.lukasz@gmail.com>
 * @copyright   Copyright (c) 2013 Lukasz Ciolecki (mart)
 */
abstract class TaskAbstract
{
    /**
     * Instance of current run cron
     * 
     * @var \Extlib\Cron
     */
    protected $cron = null;

    /**
     * Array of params
     * 
     * @var array 
     */
    protected $params = array();

    /**
     * Instance of construct
     * 
     * @param \Extlib\Cron $cron
     * @param array $params
     */
    public function __construct(\Extlib\Cron $cron, array $params = array())
    {
        $this->setCron($cron);
        $this->addParams($params);
    }
    
    /**
     * Set params
     * 
     * @param array $params
     * @return \Extlib\Cron\Task\TaskAbstract
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Add params
     * 
     * @param array $params
     * @return \Extlib\Cron\Task\TaskAbstract
     */
    public function addParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Clear params
     * 
     * @return \Extlib\Cron\Task\TaskAbstract
     */
    public function clearParams()
    {
        $this->params = array();
        return $this;
    }

    /**
     * Get params
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Get current cron
     * 
     * @return \Extlib\Cron
     */
    public function getCron()
    {
        return $this->cron;
    }

    /**
     * Set cron
     * 
     * @param \Extlib\Cron $cron
     * @return \Extlib\Cron\Task\TaskAbstract
     */
    public function setCron(\Extlib\Cron $cron)
    {
        $this->cron = $cron;
        return $this;
    }

    /**
     * Run task
     */
    abstract public function run();
}
