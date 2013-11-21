<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Initialize EntityManagera
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    public function _initEntityManager()
    {
        $this->bootstrap('doctrine');

        $container = Zend_Registry::get('doctrine');
        Zend_Registry::set('em', $container->getEntityManager('default'));
        return $container->getEntityManager('default');
    }
}

