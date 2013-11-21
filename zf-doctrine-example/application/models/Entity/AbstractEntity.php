<?php
namespace Entity;

/**
 * 
 */
abstract class AbstractEntity
{
    /**
     * Instancja EntityManager'a
     * 
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em = null;

    /**
     * Instancja konfiguracji aplikacji
     * 
     * @var Zend_Config
     */
    protected $config = null;

    /**
     * Instancja konstruktora - domyslnie probuje wypelnic encje danymi
     * 
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        foreach ($data as $property => $value) {
            $this->__set($property, $value);
        }
    }
    
    /**
     * Utworzenie magicznych wywolan metod set i get dla wlasciwosci rekordu
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __call($method, $args)
    {
        $property = lcfirst(substr($method, 3));
        
        if (property_exists(\get_class($this), $property)) {
            switch (substr($method, 0, 3)) {
                case 'set':
                    $arg = null;
                    if (isset($args[0])) {
                        $arg = $args[0];
                    }
                    
                    $this->$property = $arg;
                    return $this;
                case 'get':
                    return $this->$property;
            }
        }
        
        throw new \InvalidArgumentException("Call to undefined method " . get_class($this) . "::" . $method . "()");
    }
    
    /**
     * Metoda ustawiajaca wlasciwosc
     * 
     * @param string $property
     * @param miexd $value
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __set($property, $value)
    {
        $method = 'set' . ucfirst($property);

        if (method_exists($this, $method)) {
            return $this->$method($value);
        } else if (property_exists($this, $property)) {
            $this->$property = $value;
            return $this;
        }
        
        throw new \InvalidArgumentException(sprintf("Call to undefined property '%s", $property));
    }

    /**
     * Metoda zwracajaca wlasciwosc
     * 
     * @param type $property
     * @return miexd
     * @throws \InvalidArgumentException
     */
    public function __get($property)
    {
        $method = 'get' . ucfirst($property);

        if (method_exists($this, $method)) {
            return $this->$method();
        } else if (property_exists($this, $property)) {
            return $this->$property;
        }
        
        throw new \InvalidArgumentException(sprintf("Call to undefined property '%s", $property));
    }

    /**
     * Metoda wypelniajaca obiekt na podstawie tablicy
     * 
     * @param array $values
     * @return \Entity\AbstractEntity
     */
    public function fromArray(array $values)
    {
        foreach ($values as $property => $value) {
            $this->__set($property, $value);
        }
        
        return $this;
    }

    /**
     * Return array of object field=>value
     * 
     * @return array
     */
    public function toArray()
    {
        return $properties = get_object_vars($this);
    }
    
    /**
     * Get config
     * 
     * @return \Zend_Config
     */
    public function getConfig()
    {
        if (null === $this->config) {
            $this->setConfig(\Zend_Registry::get('config'));
        }
        
        return $this->config;
    }

    /**
     * Set config
     * 
     * @param Zend_Config $config
     * @return \Entity\AbstractEntity
     */
    public function setConfig(\Zend_Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get EntityManager
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        if (null === $this->em) {
            $this->setEm(\Zend_Registry::get('em'));
        }
        
        return $this->em;
    }

    /**
     * Set EntityManager
     * 
     * @param \Doctrine\ORM\EntityManager $em
     * @return \Entity\AbstractEntity
     */
    public function setEm(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }
}