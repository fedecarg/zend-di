<?php
/**
 * @see Zend_Data_Type
 */
require_once 'Zend/Data/Type.php';

/**
 * Zend_Di_Container class.
 *
 * @category   Zend
 * @package    Zend_Di
 * @author     Federico Cargnelutti <fedecarg@yahoo.co.uk>
 * @version    $Id$
 */
class Zend_Di_Container
{
    /**
     * Configuration array.
     * 
     * @var array
     */
    protected $_config = array();

    /**
     * The Registry object provides methods for managing singletons.
     * 
     * @var Zend_Di_Registry
     */
    protected $_registry = null;

    /**
     * The Factory object provides methods for creating objects.
     * 
     * @var Zend_Di_Factory
     */
    protected $_factory = null;
    
    /**
     * The Parameter object provides control over the arguments passed to a method.
     * 
     * @var Zend_Di_Parameter
     */
    protected $_parameter = null;

    /**
     * The selected component name.
     * @var string
     */
    protected $_componentName = null;

    /**
     * The selected method name.
     * @var string
     */
    protected $_methodName = '__construct';

    /**
     * Class constructor.
     *
     * @param obj $config Instance of the Zend_Config class.
     * @return void
     * @see Zend_Config
     */
    public function __construct(Zend_Config $config = null)
    {
        if ($config !== null) {
            $this->setConfigArray($config->toArray());
        }
    }
    
    /**
     * Sets the configuration array.
     *
     * @param array $config Configuration array.
     * @return void
     */
    public function setConfigArray(array $config)
    {
        $this->_config = $config;
    }

    /**
     * Loads a class.
     *
     * @param string $componentName The Component name.
     * @return obj self() Fluent interface.
     * @throws Zend_Di_Exception
     */
    public function loadClass($componentName)
    {
        if (! array_key_exists($componentName, $this->_config)) {
            /** @see Zend_Di_Exception **/
            require_once 'Zend/Di/Exception.php';
            throw new Zend_Di_Exception('Invalid component name: ' . $componentName);
        }
        $className = $this->_config[$componentName]['class'];
        
        $factory = $this->getFactory();
        if (! $factory->isClassDefined($className)) {
            $factory->loadClass($className, $componentName);
        }

        $this->_setDefaultValues();
        $this->_componentName = $componentName;

        return $this;
    }

    /**
     * Selects a class method.
     *
     * @param string $methodName The method name.
     * @return obj self() Fluent interface.
     * @throws Zend_Di_Exception
     */
    public function selectMethod($methodName)
    {
        if ($methodName == '__construct' || $methodName == $this->_config[$this->_componentName]['class']) {
            /** @see Zend_Di_Exception **/
            require_once 'Zend/Di/Exception.php';
            throw new Zend_Di_Exception('Selecting the constructor method is not allowed.');
        }
        $this->_methodName = $methodName;
        
        return $this;
    }

    /**
     * Adds a component to the self::$_arguments array.
     *
     * @return obj self() Fluent interface.
     * @throws Zend_Di_Exception
     */
    public function addComponent()
    {
        if ($this->_componentName === null) {
            /** @see Zend_Di_Exception **/
            require_once 'Zend/Di/Exception.php';
            throw new Zend_Di_Exception('You need to load a class before adding a dependency.');
        }

        $args = func_get_args();
        $this->getParameter()->setParametersFromCode($args, $this->_componentName, $this->_methodName);
        
        return $this;
    }

    /**
     * Adds a value to the self::$_arguments array.
     *
     * @return obj self() Fluent interface.
     */
    public function addValue()
    {
        $args = func_get_args();
        
        foreach ($args as $key => $value) {
            $dataType = Zend_Data_Type::getType($value);
            $this->getParameter()->setParameter($value, $this->_methodName, $dataType);
        }
        
        return $this;
    }

    /**
     * Binds a parameter to the specified variable name.
     *
     * @param string $parameter Parameter identifier.
     * @param string $variable Variable containing the value.
     * @param int $dataType Explicit data type for the parameter using the self::PARAM_* constants.
     * @return obj self() Fluent interface.
     */
    public function bindParam($identifier, $variable, $dataType = null)
    {
        $this->getParameter()->bindParam($identifier, $variable, $dataType);
        return $this; 
    }
    
    /**
     * Returns an instance the Zend_Di_Parameter class.
     *
     * @return obj Returns an instance of the Zend_Di_Parameter class.
     */
    public function getParameter()
    {
        if ($this->_parameter === null) {
            /** @see Zend_Di_Parameter **/
            require_once 'Zend/Di/Parameter.php';
            $this->setParameter(new Zend_Di_Parameter());
        }
        return $this->_parameter;
    }

    /**
     * Binds a parameter to the specified variable name.
     *
     * @param array $parameters Parameters array.
     * @return void
     */
    public function setParameter(Zend_Di_Component_Parameter $obj)
    {
        $this->_parameter = $obj;
    }
    
    public function clearParameter()
    {
        $this->_parameter = null;
    }
    
    /**
     * Creates an instance of self::$_componentName.
     *
     * @return obj Returns an instance of self::$_componentName.
     * @throws Zend_Di_Exception
     */
    public function newInstance()
    {
        $parameter = $this->getParameter();
        $componentSpec = $this->_config[$this->_componentName];
        if (array_key_exists('arguments', $componentSpec)) {
            $parameter->setParametersFromConfig($componentSpec);
        }
        
        $newInstance = $this->getFactory()->create($parameter, $componentSpec);
        
        $registry = $this->getRegistry();
        $registry->setInstance($this->_componentName, $newInstance);
        
        $this->_setDefaultValues();

        return $newInstance;
    }

    /**
     * Returns an instance the Zend_Di_Registry class.
     *
     * @return obj Returns an instance of the Zend_Di_Registry class.
     */
    public function getRegistry()
    {
        if ($this->_registry === null) {
            $identityMap = array_keys($this->_config);
            /** @see Zend_Di_Registry **/
            require_once 'Zend/Di/Registry.php';
            $this->setRegistry(new Zend_Di_Registry($identityMap));
        }
        return $this->_registry;
    }

    /**
     * Sets an instance of the Zend_Di_Registry class.
     *
     * @param obj $registry Instance of the Zend_Di_Registry class.
     * @return void
     */
    public function setRegistry(Zend_Di_Registry $registry)
    {
        $this->_registry = $registry;
    }
    
    /**
     * Returns an instance to the Zend_Di_Factory class.
     *
     * @return obj Returns an instance of the Zend_Di_Factory class.
     */
    public function getFactory()
    {
        if ($this->_factory === null) {
            /** @see Zend_Di_Factory **/
            require_once 'Zend/Di/Factory.php';
            $this->setFactory(new Zend_Di_Factory());
        }
        return $this->_factory;
    }

    /**
     * Sets an instance of the Zend_Di_Factory class.
     *
     * @param obj $factory Instance of the Zend_Di_Factory class.
     * @return void
     */
    public function setFactory(Zend_Di_Factory $factory)
    {
        $this->_factory = $factory;
    }
    
    /**
     * Builds the constructor parameters array.
     *
     * @return void
     */
    public function buildConstructorParameters()
    {
        $constructorParams = array();
        
        $parameter = $this->getParameter();
        $parameters = $parameter->getParameters();
        if (array_key_exists('__construct', $parameters)) {
            foreach ($parameters['__construct'] as $key => $arg) {
                if ($arg['dataType'] === Zend_Data_Type::TYPE_OBJECT) {
                    if (is_object($arg['data'])) {
                        $constructorParams[] = $arg['data'];
                    } else {
                        $componentName = $arg['data'];
                        $constructorParams[] = $this->_getInstanceOf($componentName);
                    }
                } else {
                    $value = $arg['data'];
                    if ($value[0] == ':') {
                        $value = $parameter->getParamValue($value);
                    } else {
                        settype($value, $arg['dataType']);
                    }
                    $constructorParams[] = $value;
                }
            }
        }
        
        $parameter->setConstructorParameters($constructorParams);
    }

    /**
     * Builds the setter methods parameters.
     *
     * @return void
     */
    public function buildSetterParameters()
    {
        $setterParameters = array();
        
        $parameter = $this->getParameter();
        $parameters = $parameter->getParameters();
        if (array_key_exists('__construct', $parameters)) {
            unset($parameters['__construct']);
        }
        
        foreach ($parameters as $methodName => $arguments) {
            foreach ($arguments as $key => $arg) {
                if ($arg['dataType'] === Zend_Data_Type::TYPE_OBJECT) {
                    if (is_object($arg['data'])) {
                        $setterParameters[$methodName][] = $arg['data'];
                    } else {
                        $componentName = $arg['data'];
                        $setterParameters[$methodName][] = $this->_getInstanceOf($componentName);
                    }
                } else {
                    $value = $arg['data'];
                    if ($value[0] == ':') {
                        $value = $this->getParameter()->getParamValue($value);
                    } else {
                        settype($value, $arg['dataType']);
                    }
                    $setterParameters[$methodName][] = $value;
                }
            }
        }
        
        $parameter->setSetterParameters($setterParameters);
    }
    
    /**
     * Returns a single instance of Zend_Di.
     *
     * @return object
     */
    protected static function _getInstance() 
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Create a new object based on the referenced class name and construction
     * parameters. If a registered replacement object exists, this will be
     * returned instead.
     *
     * @param string $className
     * @param array $constructorArgs
     * @param array $setterArgs
     * @return object
     */
    public static function create($className, $constructorArgs = null, $setterArgs = null)
    {        
        $instance = self::_getInstance();
        
        $registry = $instance->getRegistry();
        if ($registry->isSingleton($className)) {
            return $registry->getSingleton($className);
        }
        
        $factory = $instance->getFactory();
        if ($constructorArgs !== null) {
            $factory->setConstructorArgs($constructorArgs);
        }
        if ($setterArgs !== null) {
            $factory->setSetterArgs($setterArgs);
        }
        
        if (! $factory->isClassDefined($className)) {
            $factory->loadClass($className);
        }
        $newObject = $factory->create(array('class' => $className));
        $registry->setSingleton($className, $newObject);
        
        return $newObject;
    }
    
    /**
     * Create a new object based on the referenced class name and construction 
     * parameters. If a registered replacement object exists, this will be 
     * returned instead.
     *
     * @param string $className
     * @param object $withObject
     */
    public static function replaceClass($className, $withObject)
    {
        $instance = self::_getInstance();
        $instance->getRegistry()->setSingleton($className, $withObject);
    }

    /**
     * Searches for a singleton instance or a container, if it doesn't 
     * find any of them, it returns a new instance of $class. 
     *
     * @param string $class
     * @return obj Returns a singleton instance, a container or a new instance of $class.
     */
    protected function _getInstanceOf($class)
    {
        $registry = $this->getRegistry();
        if ($registry->isContainer($class)) {
            return $registry->getContainer($class);
        } elseif ($registry->isSingleton($class)) {
            return $registry->getSingleton($class);
        } else {
            $di = new self();
            $di->setConfigArray($this->_config);
            $di->setRegistry($this->getRegistry());
            $di->setParameter($this->getParameter());
            
            return $di->loadClass($class)->newInstance();
        }
    }
    
    /**
     * Sets the default values.
     *
     * @return void
     */
    protected function _setDefaultValues()
    {
        $this->clearParameter();
        $this->_methodName = '__construct';
    }
}
