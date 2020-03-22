<?php

namespace Aditex\src;

use Aditex\src\exception\BindingResolutionException;

/**
 * Automatic dependency injector
 *
 * it uses core php reflaction class to create objects..
 */
class AutoDependencyInjector
{
	/**
	 * store service that created for injection
	 *
	 * @var array
	 */
	public $storeServices = [];

	/**
	 * create all service object that could be use as 
	 * dependency injection
	 * 
	 * @param 	array 	$services
	 * @param 	array 	$argsValue ( argument values that passing manually 
	 *								to the constructor)
	 */
	public function createAll(array $services,array $argsValue)
	{
		
		$requiredServices = array_keys(array_reverse($services));

		foreach ($requiredServices as $name) {

			if (is_null($services[$name])) {

				if (!isset($this->storeServices[$name])) {
					$this->storeServices[$name] = new $name;
				}

				continue; 
			}
			if (is_array($services[$name])) {

				if (!isset($this->storeServices[$name])) {
					$this->injectDependencies($name,$services[$name],$argsValue);
				}

			}
		}
		
		return $this->storeServices;

	}

	/**
	 * injecting dependency object if class dependent
	 * and create new instance using reflaction class 
	 * store as service class instance
	 *
	 * @param 	string 		$class (class name)
	 * @param 	array 		$args (__constructor arguments)
	 * @param 	array 		$argsValue (manulaly injected values)
	 */
	private function injectDependencies($class, $args, $argsValue)
	{
		$classArgs = [];

		foreach ($args as $arg => $name) {

			// if dependency is class object push it into array
			if (array_key_exists($name, $this->storeServices)) {

				$classArgs[$arg] = $this->storeServices[$name];
				
				continue;
			}

			// if dependency is not class object inject values from upcoming manual data
			if (array_key_exists($class, $argsValue)) {

				if (isset($argsValue[$class][$arg])) {

					$classArgs[$arg] = $argsValue[$class][$arg];
					unset($argsValue[$class][$arg]);

					continue;
				}

			}
			
			if (!array_key_exists($arg, $classArgs)) {
				throw new BindingResolutionException("Unresolvable dependency resolving '{$name}':'{$arg}' in {$class}::__construct");
			}
			
		}

		$this->storeServices[$class] = $this->createInstance($class, $classArgs);
	}


	/**
	 * Create class instance using reflaction Class..
	 *
	 * @param 	string 	$class
	 * @param 	array 	$args (__constructor args value)
	 *
	 * @return 	object 	Class object
	 */
	private  function createInstance($class,array $args)
	{
		if (!method_exists($class, "__construct")) {

			return new $class;

		}else{

			$reflectionClass = new \ReflectionClass($class);
			return $reflectionClass->newInstanceArgs($args);

		}
		
	}

}