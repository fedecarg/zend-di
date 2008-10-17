<?php
/**
 * Zend_Di_Storage_Interface.
 *
 * @category   Zend
 * @package    Zend_Di
 * @subpackage Storage
 * @author     Federico Cargnelutti <fedecarg@yahoo.co.uk>
 * @version    $Id$
 */
interface Zend_Di_Storage_Interface
{
    /**
     * Returns a new instance of the class.
     *
     * @return obj Returns a new instance of Zend_Di_Storage.
     */
    public function newInstance();
    
    /**
     * Returns an instance of $componentName.
     *
     * @param string $componentName The component name.
     * @return mixed Returns an instance of $componentName on success, or false on failure.
     */
    public function get($componentName);
    
    /**
     * Stores an instance of $componentName.
     *
     * @param string $componentName The component name.
     * @param obj $instance The instance of the object to store.
     * @return void
     */
    public function set($componentName, $instance);
    
    /**
     * Returns true if $componentName is stored in the container.
     *
     * @param string $componentName
     * @return bool
     */
    public function isStored($componentName);
}
