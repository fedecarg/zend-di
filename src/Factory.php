<?php
/**
 * Zend_Di_Factory class.
 *
 * @category   Zend
 * @package    Zend_Di
 * @author     Federico Cargnelutti <fedecarg@yahoo.co.uk>
 * @version    $Id$
 */
class Zend_Di_Factory
{    
    /**
     * Classes defined. 
     * @var array
     */
    protected $_classesDefined = array();
    
    /**
     * Creates a new instance of $componentName using Reflection.
     *
     * @param obj Zend_Di_Parameter
     * @param array $componentSpec The component specifications.
     * @return obj Returns a new instance of a class.
     * @throws Zend_Di_Exception
     */
    public function create(Zend_Di_Parameter $parameter, array $componentSpec)
    {
        $className = $componentSpec['class'];        
        if (! $this->isClassDefined($className)) {
            /** @see Zend_Di_Exception **/
            require_once 'Zend/Di/Exception.php';
            throw new Zend_Di_Exception('Class not defined: ' . $className);
        }
        
        $reflection = new ReflectionClass($className);
        if ($parameter->hasConstructorArgs()) {
            // Constructor dependency injection 
            $obj = $reflection->newInstanceArgs($parameter->getConstructorArgs());
        } else {
            $obj = $reflection->newInstance();
        }
        
        if (array_key_exists('instanceof', $componentSpec)) {
            $instanceOf = $componentSpec['instanceof'];
            if (! $obj instanceof $instanceOf) {
                /** @see Zend_Di_Exception **/
                require_once 'Zend/Di/Exception.php';
                throw new Zend_Di_Exception($className . ' is not an instance of ' . $instanceOf);
            }
        }
        
        if ($parameter->hasSetterArgs()) {
            foreach ($parameter->getSetterArgs() as $methodName => $methodArgs) {
                if (! $reflection->hasMethod($methodName)) {
                    /** @see Zend_Di_Exception **/
                    require_once 'Zend/Di/Exception.php';
                    throw new Zend_Di_Exception('The method ' . $methodName 
                        . '() does not exist in ' . $reflection->getName());
                }
                // Setter dependency injection 
                $reflection->getMethod($methodName)->invokeArgs($obj, $methodArgs);
            }
        }
        
        $this->_constructorArgs = array();
        $this->_setterArgs = array();
        
        return $obj;
    }
    
    /**
     * Loads a class.
     * 
     * @param string $className The class name.
     * @param string $componentName The component name.
     * @return bool Returns true on success, or false on failure.
     * @see Zend_Loader
     */
    public function loadClass($className, $componentName = null)
    {
        if (! $this->isClassDefined($className)) {
            if (count($this->_classesDefined) === 0) {
                /** @see Zend_Loader **/
                require_once 'Zend/Loader.php';
            }
            if (! class_exists($className, true)) {
                Zend_Loader::loadClass($className);
            }
            if (null === $componentName) {
                $componentName = $className;
            }
            $this->_classesDefined[$componentName] = $className;
            return true;
        }
        return false;
    }
    
    /**
     * Returns true if $className is defined or false otherwise.
     *
     * @param string $className The class name.
     * @return bool
     */
    public function isClassDefined($className)
    {
        return in_array($className, $this->_classesDefined);
    }
}
