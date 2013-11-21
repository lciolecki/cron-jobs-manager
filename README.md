#PHP Cron Jobs Manager

PHP Cron Jobs Manager is simple library to manage cron jobs task in your application. It use some data container like database.

## Theory of library

* **Adapter** - Representation of connector to data container like Zend_Db, Doctrine, Propel, etc..
* **Job** - Representation of models/entities from data container. Has necessary information about task to do.
* **History** - Representation of models/entities, has information about status executed job. 
* **Task** - Representation of thing to do, must extends from \Extlib\Cron\Task\TaskAbstract - implement run() method.

##Using and example

In catalog zf-doctrine-example as name suggest is an example application zend framework using doctrine 2. Pleas create database using file : */zf-doctrine-example/docs/database.sql. This sql script create database with two tables: cron_histories and cron_jobs with two task to do (Core_Cron_Task_Test1, Core_Cron_Task_Test2). To run script you must edit Your crontab with command crontab -e and put this line:

    {
        * * * * * /usr/bin/php {app-dir}/zf-doctrine-example/bin/cron.php  >> /tmp/cron-logs.log 2>&1
    }

##Installation using Composer

    {
        "minimum-stability": "dev",
        "repositories": [
            {
                "type": "vcs",
                "url": "http://github.com/lciolecki/cron-jobs-manager"
            }
        ],
        "require": {
            "lciolecki/cron-jobs-manager": "dev-master"
        }
    }
