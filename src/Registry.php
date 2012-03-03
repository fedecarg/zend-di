<?php
/**
 * Zend_Di_Registry class.
 *
 * @category   Zend
 * @package    Zend_Di
 * @author     Federico Cargnelutti <fedecarg@yahoo.co.uk>
 * @version    $Id$
 */
class Zend_Di_Registry
{
    /**
     * List of components.
     * @var array
     */
    protected $_identityMap = array();
    
    /**
     * The $_singletons array provides storage for objects.
     * @var array
     */
    protected $_singletons = array();
    
    /**
     * The $_containers array provides storage for containers.
     * @var array
     */
    protected $_containers = array();
    
    /**
     * Container name.
     * @var string
     */
    protected $_containerName = null;
    
    /**
     * Instance of Zend_Di_Storage_Interface.
     * @var object
     */
    protected $_storage = null;
    
    /**
     * Class constructor.
     * 
     * @param array $identityMap List of available components.
     * @return void
     */
    public function __construct(array $identityMap = null)
    {
        if ($identityMap !== null) {
            $this->_identityMap = $identityMap;
        }
    }
    
    /**
     * Opens a container.
     *
     * @param string $containerName The container name.
     * @return object self() Fluent interface.
     * @throws Zend_Di_Exception
     */
    public function open($containerName)
    {
        $exception = null;
        if ($this->isSelected()) {
            $exception = 'You need to close the container "' . $this->_containerName . '" before opening a new one.';
        } elseif (in_array($containerName, $this->_identityMap)) {
            $exception = $containerName . ' is a reserved word and therefore cannot be used.';
        }
        
        if ($exception !== null) {
            /** @see Zend_Di_Exception **/
            require_once 'Zend/Di/Exception.php';
            throw new Zend_Di_Exception($exception);
        }
        
        if (! $this->isContainer($containerName)) {
            $this->setContainer($containerName);
        }
        
        $this->_containerName = $containerName;
        return $this;
    }
    
    /**
     * Returns the container object.
     *
     * @param string $containerName The container name.
     * @return mixed Returns the container on success, or false otherwise.
     */
    public function getContainer($containerName)
    {
        if (! array_key_exists($containerName, $this->_containers)) {
            return false;
        }
        return $this->_containers[$containerName];
    }
    
    /**
     * Creates a new container.
     *
     * @param string $containerName The container name.
     * @return void
     */
    public function setContainer($containerName)
    {
        if (! array_key_exists($containerName, $this->_containers)) {
            if ($this->_storage === null) {
                $this->setStorage();
            }
            $this->_containers[$containerName] = $this->_storage->newInstance();
        }
    }
    
    /**
     * Checks whether $containerName exists or not.
     *
     * @param string $containerName The container name.
     * @return bool Returns true if the container exists, or false otherwise.
     */
    public function isContainer($containerName)
    {
        return array_key_exists($containerName, $this->_containers);
    }
    
    /**
     * Adds a component into an existing container.
     *
     * @param string $name The component name.
     * @return object self() Fluent interface.
     * @throws Zend_Di_Exception
     */
    public function add($name)
    {
        $exception = null;
        if (! $this->isSelected()) {
            $exception = 'You need to open a container before adding a component.';
        } elseif (! in_array($name, $this->_identityMap)) {
            $exception = 'Invalid component name passed to the conainer: ' . $name;
        } elseif (! array_key_exists($name, $this->_singletons)) {
            $exception = 'Cannot find any instances of ' . $name;
        }
        
        if ($exception !== null) {
            /** @see Zend_Di_Exception **/
            require_once 'Zend/Di/Exception.php';
            throw new Zend_Di_Exception($message);
        }
        
        $container = $this->_containers[$this->_containerName];
        $container->set($name, $this->_singletons[$name]);
        
        return $this;        
    }
    
    /**
     * Closes a container and resets self::$_containerName.
     *
     * @return object Returns an instance of the container.
     * @see self::getContainer()
     * @throws Zend_Di_Exception
     */
    public function close()
    {
        if (! $this->isSelected()) {
            /** @see Zend_Di_Exception **/
            require_once 'Zend/Di/Exception.php';
            throw new Zend_Di_Exception('You need to open a container first.');
        }
        
        $container = $this->_containerName;
        $this->_containerName = null;
        
        return $this->getContainer($container);
    }
    
    /**
     * Checks whether the container is selected or not.
     *
     * @return bool Returns true if the container is selected, or false otherwise.
     */
    public function isSelected()
    {
        if ($this->_containerName !== null) {
            return true;
        }
        return false;
    }
    
    /**
     * Sets an instance of Zend_Di_Storage_Interface.
     *
     * @param object $obj Instance of Zend_Di_Storage_Interface.
     * @return void
     */
    public function setStorage(Zend_Di_Storage_Interface $obj = null)
    {
        if ($obj === null) {
            /** @see Zend_Di_Storage_Object **/
            require_once 'Zend/Di/Storage/Object.php';
            $this->_storage = new Zend_Di_Storage_Object();
        } else {
            $this->_storage = $obj;
        }
    }
    
    /**
     * Returns an instance of Zend_Di_Storage_Interface.
     *
     * @return Zend_Di_Storage_Object
     */
    public function getStorage()
    {
        return $this->_storage;
    }
    
    /**
     * Sets a new instance.
     *
     * @param string $componentName The component name.
     * @param object $obj Instance. 
     * @return void
     */
    public function setSingleton($componentName, $obj)
    {
        $this->_singletons[$componentName] = $obj;
    }
    
    /**
     * Returns a singleton instance of $componentName.
     *
     * @param string $componentName The component name.
     * @return array Returns the instance of a given component.
     * @throws Zend_Di_Exception
     */
    public function getSingleton($componentName)
    {
        if (! array_key_exists($componentName, $this->_singletons)) {
            /** @see Zend_Di_Exception **/
            require_once 'Zend/Di/Exception.php';
            throw new Zend_Di_Exception('Cannot find an instance of ' . $componentName . '');
        }
        return $this->_singletons[$componentName];
    }
    
    /**
     * Checks whether an instance exists or not.
     *
     * @param string $componentName The component name.
     * @return bool Returns true if the instance exists, or false otherwise.
     */
    public function isSingleton($componentName)
    {
        return array_key_exists($componentName, $this->_singletons);
    }
}
