<?php
/**
 * @see Zend_Di_Storage_Interface
 */
require_once 'Zend/Di/Storage/Interface.php';

/**
 * Zend_Di_Storage_Object class.
 *
 * @category   Zend
 * @package    Zend_Di
 * @subpackage Storage
 * @author     Federico Cargnelutti <fedecarg@yahoo.co.uk>
 * @version    $Id$
 */
class Zend_Di_Storage_Object implements Zend_Di_Storage_Interface, Iterator
{
    /**
     * Storage container.
     * @var array
     */
    protected $_storage = array();
    
    /**
     * Current self::$_storage array position.
     * @var int
     */
    protected $_index = 0;
    
    /**
     * Returns a new instance of the class.
     *
     * @return obj Returns a new instance of Zend_Di_Storage_Object.
     */
    public function newInstance()
    {
        return new self();
    }
    
    /**
     * Returns an instance of $className.
     *
     * @param string $className The class name.
     * @return mixed Returns an instance of $className on success, or false on failure.
     */
    public function get($className)
    {
        if (array_key_exists($className, $this->_storage)) {
            return $this->_storage[$className];
        }
        return false;
    }
    
    /**
     * Stores an instance of $className.
     *
     * @param string $className The component name.
     * @param obj $instance The instance of the object to store.
     * @return void
     */
    public function set($className, $instance)
    {
        if (is_object($instance)) {
            $this->_storage[$className] = $instance;
        }
    }
    
    /**
     * Returns true if $className is stored in the container.
     *
     * @param string $className
     * @return bool
     */
    public function isStored($className)
    {
        return array_key_exists($className, $this->_storage);
    }
    
    /**
     * Advances the internal array pointer of self::$_storage.
     * 
     * @return void
     */
    public function next()
    {
        next($this->_storage);
        $this->_index++;
    }
    
    /**
     * Rewinds the internal array pointer of self::$_storage.
     *
     * @return void
     */
    public function rewind() 
    {
        rewind($this->_storage);
    }
    
    /**
     * Returns the current element in self::$_storage.
     *
     * @return obj
     */
    public function current()
    {
        return current($this->_storage);
    }
    
    /**
     * Returns the index element of self::$_storage.
     *
     * @return int
     */
    public function key()
    {
        return $this->_index;
    }
    
    /**
     * Returns true if the key from self::$_storage is valid. 
     *
     * @return bool
     */
    public function valid()
    {
        return (key($this->_storage) !== false) ? true : false;
    }
}
