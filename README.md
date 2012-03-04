# Introduction

Zend_Di is a dependency injector component. It minimizes coupling between groups of classes, makes unit testing much simpler, and provides an easy way to re-configure a package to use custom implementations of components. The architecture of the Zend_Di component is based on the following concepts:

* Dependency injection is a technique that consists of passing objects via the constructor or setter methods.
* The Container provides an easy way of re-configuring a package to use custom implementations of components.
* Responsibility for object management is taken over by whatever container is being used to manage those objects.

**References:**

[Martin Fowler DI Pattern](http://www.martinfowler.com/articles/injection.html), [PicoContainer](http://picocontainer.org/), [NanoContainer](http://nanocontainer.codehaus.org/)

## Operation

Zend_Di provides generic factory classes that instantiate instances of classes. These instances are then configured by the container, allowing construction logic to be reused on a broader level. For example:

```php
<?php

$components = array(
	'Foo' => array(
		'class'        => 'Zend_Foo',
		'arguments'    => array(
			'__construct' => 'ComponentA',
		),
	),
	'ComponentA' => array(
		'class'        => 'Zend_Foo_Component_A',
		'instanceof'   => 'Zend_Foo_Component_Interface',
	),
	'ComponentB' => array(
		'class'        => 'Zend_Foo_Component_B',
		'instanceof'   => 'Zend_Foo_Component_Interface',
	),
);

$config = new Zend_Config($components);
// $config = new Zend_Config_Xml('components.xml', 'staging');

$di = new Zend_Di_Container($config);

// Create an instance of Zend_Foo and injects Zend_Foo_Component_A via the constructor method
$foo = $di->loadClass('Foo')->newInstance();
```

Once we separate configuration from use, we can easily test the Car with different Engines. It's just a matter of re-configuring the package and injecting Zend_Car_Parts_Engine_Gas instead of Zend_Car_Parts_Engine_Fuel.

## Use cases

Zend_Di handles injections via the constructor or setters methods. In addition, the component allows the user to map out specifications for components and their dependencies in a configuration file and generate the objects based on that specification.

**Assembling objects using reflection:**

```php
<?php

class Zend_Foo {
    public function __construct(Zend_Foo_Component_A $componentA) {}  
    public function setComponentA(Zend_Foo_Component_Interface $component) {}   
    public function setComponentB(Zend_Foo_Component_Interface $component) {} 
    public function setComponentC(Zend_Foo_Component_C $componentC) {}  
} 
 
$di = new Zend_Di_Reflection();  
$di->addComponent('Zend_Foo_Component_A', array('__construct', 'setComponentA'));  
$di->addComponent(new Zend_Foo_Component_B(), array('setComponentB')); 
$di->addComponent(new Zend_Foo_Component_C()); 

$di->loadClass('Zend_Foo')->newInstance();
$foo = $di->getComponent('Zend_Foo');
```

**Assembling objects using a DI container:**

```php
<?php

class Zend_Foo {
    public function __construct(Zend_Foo_Component_A $componentA) {}  
    public function setComponentA(Zend_Foo_Component_Interface $component) {}   
    public function setComponentB(Zend_Foo_Component_Interface $component) {} 
    public function setComponentC(Zend_Foo_Component_C $componentC) {}  
} 

$di = new Zend_Di_Container();

$di->loadClass('Zend_Foo')
	->addComponent('Zend_Foo_Component_A')
	->selectMethod('setComponentA')
		->addComponent('Zend_Foo_Component_A')
	->selectMethod('setComponentB')
		->addComponent('Zend_Foo_Component_B')
	->selectMethod('setComponentC')
		->addComponent('Zend_Foo_Component_C')
	->newInstance();
```

**Assembling objects using configuration:**

The configuration is typically set up in a different file. Each package can have its own configuration file: PHP, INI or XML file. The configuration file holds the components specifications and package dependencies. 

You can pass an instance of Zend_Config via the constructor, or set a configuration array using the setConfigArray() method.

The cases below assume that the following classes have been defined:

```php
<?php

class Zend_Foo {
	public function __construct(
		Zend_Foo_Component_Interface $componentA = null,
		Zend_Foo_Component_Interface $componentB = null,
		$arg3 = null,
		$arg4 = null) {
	}
	
	public function setComponentA(Zend_Foo_Component_Interface $component, $arg2 = null) {
	}
}

interface Zend_Foo_Component_Interface {
}
class Zend_Foo_Component_A implements Zend_Foo_Component_Interface {
}
class Zend_Foo_Component_B implements Zend_Foo_Component_Interface {
}

$components = array(
	'Foo' => array(
		'class'        => 'Zend_Foo',
		'arguments'    => array(
			'__construct' => 'ComponentA',
		),
	),
	'ComponentA' => array(
		'class'        => 'Zend_Foo_Component_A',
		'instanceof'   => 'Zend_Foo_Component_Interface',
	),
	'ComponentB' => array(
		'class'        => 'Zend_Foo_Component_B',
		'instanceof'   => 'Zend_Foo_Component_Interface',
	),
);

$config = new Zend_Config($components);
// $config = new Zend_Config_Xml('components.xml', 'staging');

$di = new Zend_Di_Container($config);

// Create an instance of Zend_Foo and injects Zend_Foo_Component_A via the constructor method
$foo = $di->loadClass('Foo')->newInstance();
```

The two major flavors of Dependency Injection are Setter Injection (injection via setter methods) and Constructor Injection (injection via constructor arguments). Zend_Di provides support for both, and even allows you to mix the two when configuring the one object.

## Constructor dependency injection

When a class is loaded, the constructor method is selected by default.

**Inject a single dependency:**

```php
<?php

$di->loadClass('Foo')
	->addComponent('ComponentA')
	->newInstance();
```

**Inject multiple dependencies:**

```php
<?php

$di->loadClass('Foo')
	->addComponent('ComponentA')
	->addComponent('ComponentB')
	->newInstance();
```

**Inject dependencies and pass arguments:**

```php
<?php

$di->loadClass('Foo')
	->addComponent('ComponentA')
	->addComponent('ComponentB')
	->addValue('arg3')
	->addValue('arg4')
	->newInstance();

// Or...
$di->loadClass('Foo')
	->addComponent('ComponentA', 'ComponentB')
	->addValue('arg3', 'arg4')
	->newInstance();
```

Users can map out specifications for components and their dependencies. So whenever a class is loaded, Zend_Di will inject the dependencies automatically. For example: 

```php
<?php

$config = array(
	'Foo' => array(
		'class'        => 'Zend_Foo',
		'instanceof'   => 'Zend_Foo',
		'arguments'    => array(
			'__construct'   => 'ComponentA, ComponentB, :param',
			'setComponentA' => 'ComponentA'
		),
	...

$di = new Zend_Di_Container();
$di->setConfigArray($config);

// Bind parameter and create an instance of the Zend_Foo class
$di->loadClass('Foo')
	->bindParam(':param', 'Parameter 1')
	->newInstance();
```

## Setter dependency injection

```php
<?php

// Pass dependencies through the setComponentA() method
$di->loadClass('Foo')
	->selectMethod('setComponentA')
		->addComponent('ComponentA')
	->newInstance();
```

Zend_Di injects dependencies using the top-down fashion, starting with the constructor and ending with the setter methods.

```php
<?php

// Constructor and setter dependency injection
$di->loadClass('Foo')
	->addComponent('ComponentA', 'ComponentB')
	->addValue('arg3', 'arg4')
	->selectMethod('setComponentA')
		->addComponent('ComponentA')
		->addValue('arg2')
	->newInstance();
```

Users can map out specifications for a component:

```php
<?php

$config = array(
	'Foo' => array(
		'class'        => 'Zend_Foo',
		'instanceof'   => 'Zend_Foo',
		'arguments'    => array(
			'setComponentA' => 'ComponentA',
		),
	...
	
$di = new Zend_Di_Container();
$di->setConfigArray($config);

$foo = $di->loadClass('Foo')->newInstance();
```

## Storage Containers

You can tell Zend_Di what components to manage by adding them to a container (the order of registration has no significance). Containers are stored are retrieved using the Zend_Di_Registry class. The Zend_Di_Registry::getContainer() method returns an instance of Zend_Di_Storage_Interface.

```php
<?php

$di = new Zend_Di_Container();
$di->setConfigArray($config);
$foo = $di->loadClass('Foo')->newInstance();

$registry = $di->getRegistry();
$registry->open('FooPackage');
$registry->add('Foo');
$registry->close();
       
// Get an instance of the container FooPackage
$fooPackage = $registry->getContainer('FooPackage');

while ($obj = $fooPackage->current()) {
	echo $fooPackage->getClassName();
	$fooPackage->next();
}

// Get a single instance of the Foo class
$foo = $registry->getSingleton('Foo');
```

You can register your own container as long as you pass an instance of Zend_Di_Storage_Interface. New containers can be register using the Zend_Di_Registry::setStorage() method.

```php
<?php

class Zend_Di_Storage_Cache implements Zend_Di_Storage_Interface {
}

$di = new Zend_Di_Container();
$di->getRegistry()->setStorage(new Zend_Di_Storage_Cache());
```

## Example

http://framework.zend.com/wiki/display/ZFPROP/Zend_Di+Example

## License

* Copyright (c) 2007, Federico Cargnelutti. All rights reserved. 
* New BSD License http://www.opensource.org/licenses/bsd-license.php

