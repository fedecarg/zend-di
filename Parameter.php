<?php
/**
 * @see Zend_Data_Type
 */
require_once 'Zend/Data/Type.php';

/**
 * This class provides control over the arguments passed to a method.
 *
 * @category   Zend
 * @package    Zend_Di
 * @author     Federico Cargnelutti <fedecarg@yahoo.co.uk>
 * @version    $Id$
 */
class Zend_Di_Parameter
{
    /**
     * Parameters passed to constructor and setter methods.
     * @var array
     */
    protected $_parameters = array();

    /**
     * Arguments passed to the constructor method.
     * @var array
     */
    protected $_constructorArgs = array();
    
    /**
     * Arguments passed to the setter methods.
     * @var array
     */
    protected $_setterArgs = array();
    
    /**
     * Parameter bindings, covers bindParam().
     * @var array
     */
    protected $_bindParam = array();
    
    /**
     * Sets the constructor and setter methods parameters based on the selected   
     * component specifications.
     *
     * @return void
     */
    public function setParametersFromConfig(array $componentSpec)
    {
        foreach ($componentSpec as $methodName => $argString) {
            $argArray = explode(',', $argString);
            foreach ($argArray as $arg) {
                $data = trim($arg);
                if (strtoupper($data) == 'NULL') {
                    $data = null;
                    $dataType = Zend_Data_Type::TYPE_NULL;
                } if (strtoupper($data) == 'TRUE' || strtoupper($data) == 'FALSE') {
                    $dataType = Zend_Data_Type::TYPE_BOOLEAN;
                } else {
                    $dataType = Zend_Data_Type::getType($data);
                }
                $this->setParameter($data, $methodName, $dataType);
            }
        }
    }

    /**
     * ...
     *
     * @return void
     */
    public function setParametersFromCode(array $args, $component, $method)
    {
        foreach ($args as $key => $componentName) {
            if (is_object($componentName)) {
                $className = get_class($componentName);
            } else {
                $className = $componentNamep;
            }
            if ($className == $component) {
                /** @see Zend_Di_Exception **/
                require_once 'Zend/Di/Exception.php';
                throw new Zend_Di_Exception('Infinite recursion detected.');
            }
            $this->setParameter($componentName, $method, Zend_Data_Type::TYPE_OBJECT);
        }
    }
    
    /**
     * Sets a parameter value and data type.
     *
     * @param string $data Parameter value.
     * @param string $methodName The method name.
     * @param int $dataType Explicit data type for the parameter using the Zend_Data_Type::TYPE_* constants.
     * @return void
     */
    public function setParameter($data, $methodName, $dataType)
    {
        $this->_parameters[$methodName][] = array(
            'dataType' => $dataType,
            'data'     => $data,
        );
    }
    
    /**
     * Binds a parameter to the specified variable name.
     *
     * @param string $identifier Parameter identifier.
     * @param string $variable Variable containing the value.
     * @param int $dataType Explicit data type for the parameter using the self::PARAM_* constants.
     * @return obj self() Fluent interface.
     */
    public function bindParam($identifier, $variable, $dataType = null)
    {
        if ($dataType === null) {
            $dataType = Zend_Data_Type::getType($variable);
        }
        
        $this->_bindParam[$identifier] = array(
            'dataType' => $dataType,
            'data'     => $variable,
        );
        return $this; 
    }
    
    /**
     * Returns the value of a given parameter identifier.
     *
     * @param string $parameter Parameter identifier.
     * @return string Returns the value of a given parameter identifier.
     * @throws Zend_Di_Exception
     */
    public function getParamValue($parameter)
    {
        if (! array_key_exists($parameter, $this->_bindParam)) {
            /** @see Zend_Di_Exception **/
            require_once 'Zend/Di/Exception.php';
            throw new Zend_Di_Exception('Invalid parameter identifier ' . $parameter);
        }
        return $this->_bindParam[$parameter]['data'];
    }
    
    /**
     * Sets the constructor method arguments.
     *
     * @param array $args Arguments passed to the constructor.
     * @return void
     */
    public function setConstructorArgs(array $args)
    {
        $this->_constructorArgs = $args;
    }
    public function getConstructorArgs()
    {
        return $this->_constructorArgs;
    }
    public function hasConstructorArgs()
    {
        return (count($this->_constructorArgs > 0)) ? true : false;
    }
    
    
    /**
     * Sets the setter methods arguments.
     *
     * @param array $args Arguments passed to the setter methods.
     * @return void
     */
    public function setSetterArgs(array $args)
    {
        $this->_setterArgs = $args;
    }
    public function getSetterArgs()
    {
        return $this->_setterArgs;
    }
    public function hasSetterArgs()
    {
        return (count($this->_setterArgs > 0)) ? true : false;
    }
}
