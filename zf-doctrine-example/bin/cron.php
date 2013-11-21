<?php

use \Extlib\Cron\Adapter\Doctrine2;

/* @var $mainLogger App_Log_LogHelper */
require_once(realpath(dirname(__FILE__) . '/bootstrap.php'));

$cron = new \Extlib\Cron(new Doctrine2(array(
    Doctrine2::ENTITY_MANAGER => Zend_Registry::get('em'),
    Doctrine2::JOB_ENTITY_NAME => 'Entity\CronJob',
    Doctrine2::JOB_COL_DATE_LAST_RUN => 'dateLastRun',
    Doctrine2::JOB_COL_PRIORITY => 'priority',
    Doctrine2::JOB_COL_STATUS => 'status',
    Doctrine2::HISTORY_ENTITY_NAME => 'Entity\CronHistory'
)));

$cron->execute();
exit(0);
