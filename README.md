#PHP Cron Jobs Manager

PHP Cron Jobs Manager is simple library to manage cron jobs task in your application. It use some data container like database.

## Theory of library

* **Adapter** - Representation of connector to data container like Zend_Db, Doctrine, Propel, etc..
* **Job** - Representation of models/entities from data container. Has necessary information about task to do.
* **History** - Representation of models/entities, has information about status executed job. 
* **Task** - Representation of thing to do, must extends from \Extlib\Cron\Task\TaskAbstract - implements run() method.

#Sample use

    $cron = new \Extlib\Cron(new Doctrine2(array(
        Doctrine2::ENTITY_MANAGER => $entityManager,
        Doctrine2::JOB_ENTITY_NAME => 'Entity\CronJob',
        Doctrine2::JOB_COL_DATE_LAST_RUN => 'dateLastRun',
        Doctrine2::JOB_COL_PRIORITY => 'priority',
        Doctrine2::JOB_COL_STATUS => 'status',
        Doctrine2::HISTORY_ENTITY_NAME => 'Entity\CronHistory'
    )));
    $cron->execute();
    
## Example application zf+doctrine2

In catalog zf-doctrine-example as name suggest is an example application Zend Framework using Doctrine 2. Please create database using file : */zf-doctrine-example/docs/database.sql. This SQL script create database with two tables: cron_histories and cron_jobs with two task to do (Core_Cron_Task_Test1, Core_Cron_Task_Test2). To run script you must edit Your crontab with command crontab -e and put this line:

    
    * * * * * /usr/bin/php */zf-doctrine-example/bin/cron.php  >> /tmp/cron-logs.log 2>&1
    

##Installation using Composer

    {
        "require": {
            "lciolecki/cron-jobs-manager": "dev-master"
        }
    }
