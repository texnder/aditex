<?php

namespace aditex\src;

use bootex\services;
use aditex\src\AutoDependencyInjector;
use aditex\src\resolvers\ParameterResolver;
use aditex\src\exception\ServiceNotFoundException;


class Container 
{
	/**
     * calling object
     *
     * @var string
     */
    public $service;
	
	/**
     * object calling method
     *
     * @var string
     */
    public $method;

	/**
     * store created object
     *
     * @var array
     */
	public $storedObj = [];

	/**
     * boolen to store all neccessary dependencies..
     * by default, it's false
     *
     * @var string
     */
    public $storeAllDependencies = false;

	/**
	 * return class Object if exist
	 *
	 * @param 	string 	$objName
	 * @return 	object 	
	 */
	public function isExist(string $objName)
	{
		
		$objName = $this->has($objName);
		
		if (array_key_exists($objName, $this->storedObj)) {

			return $this->storedObj[$objName];
		}
	}
	
	/**
	 * create and store Services available in application..
	 *
	 * @param 	string 		$name (class name)
	 * @param 	string 		$method (class method)
	 * @param 	string 		$argsValue (args data for method)
	 *
	 * @return 	service class object
	 */
	public function call(string $objName, array $argsValue = [])
	{
		$this->method = "__construct";
		
		$ObjectName = $this->has($objName);

		$this->service = $ObjectName;
		 
        if (!isset($this->storedObj[$ObjectName])) {
            $this->make($ObjectName,$argsValue);
        }
        
        return $this;
		
	}

	/**
	 * exec method of $this->service object
	 *
	 * @param 	string 		$method
	 * @param 	array 		$argsValue
	 * @return 	any 		returned value of calling method..
	 */
	public function exec(string $method,array $argsValue = [])
	{
		$this->method = $method;

		if ($this->method !== "" && $this->method !== "__construct") {
			
	        $paramArray = $this->storeMethodDependencies($argsValue);
	        
			return call_user_func_array([
				$this->storedObj[$this->service],
				$this->method
			],$paramArray);
		}
	}

	/**
	 * create called  class objects and store it
	 *
	 * @param 	string 	$objName 
	 * @param 	array 	$argsValue 
	 */
	private function make($objName,array $argsValue)
	{
		
		$paramResolver = new ParameterResolver($objName,$this->method);

		$paramResolver->set();

		$argsValue = $this->updateArgsValue($paramResolver->defaults,$argsValue);
		
		$this->storeServices($paramResolver->parameters, $argsValue);
	}

	/**
	 * inject dependency and create called class object
	 * and store it..
	 *
	 * @param 	array 	$parameters 
	 * @param 	array 	$argsValue
	 */
	private function storeServices(array $parameters,array $argsValue)
	{
		$autoDI = new AutoDependencyInjector();

		$createdObj = $autoDI->createAll($parameters,$argsValue);


		if ($this->storeAllDependencies) {
			
			$this->storeAll($createdObj);

		}else{
			
			if (array_key_exists($this->service, $createdObj)) {
				$this->storedObj[$this->service] = $createdObj[$this->service];
			}
		}	
	}

	/**
	 * push all default argument values to push into function 
	 * if no value given manually
	 *
	 * @param 	array 	$defaults
	 * @param 	array 	$argsValue
	 */
	private function updateArgsValue(array $defaults, array $argsValue)
	{
		
		$argsValue = $this->reConstructArray($argsValue);

		if ($defaults) {
			foreach ($defaults as $ObjName => $args) {
				
				if (!isset($argsValue[$ObjName])) {
					$argsValue[$ObjName] = $args;
				}else{
					
					foreach ($args as $key => $value) {

						if (!isset($argsValue[$ObjName][$key])) {
							$argsValue[$ObjName][$key] = $value;
						} 

					}
				}
			}
		}

		// after updation completed return
		return $argsValue;
	}

	/**
	 * check Service available in application..
	 * return full qualified name of the service
	 *
	 * @param 	string 		$name
	 */
	private function has(string $name)
	{
		if ($fullName = $this->checkNameIsExist($name)) {
			return $fullName;
		}
		
		throw new ServiceNotFoundException('service not found: ' . $name);
	}

	/**
	 * get return output for object method or  
	 * instance of that object
	 *
	 * @param 	array 		$argdata					 
	 * @return 	array 		return method output 
	 *						or object instance
	 */
	public function get(string $objName = '')
	{
		if ($objName) {

			return $this->isExist($objName);

		}elseif (isset($this->storedObj[$this->service])) {

			return $this->storedObj[$this->service];

		}
	}


	/**
	 * call method if service available and
	 * store dependency objects
	 *
	 * arrange dependencies and other veriables in order  
	 * to call object method by injection 
	 *
	 * @param 	string 		$service (class name)
	 * @param 	string 		$method  (method name)
	 * @param 	array  		$argsValue (optional) 
	 *
	 * @return 	array 		return array, of method argument 
	 *						as key and dependency object or 
	 *						request value as $argdata values..
	 */
	private function storeMethodDependencies(array $argsValue)
	{

		if(!method_exists($this->service, $this->method)){
			throw new ServiceNotFoundException("Method '{$this->method}' not exist in class '{$this->service}'");
		}

		$argArray = [];

		$ParameterResolver = new ParameterResolver($this->service,$this->method);
		$parameters = $ParameterResolver->set();
		
		// if method has arguments check they are dependent or not
		if ($parameters[$this->service]) {

			$argArray = $parameters[$this->service];
			unset($parameters[$this->service]);
			
			$argsValue = $this->updateArgsValue($ParameterResolver->defaults,$argsValue);
			
			$this->storeAllDependencies = true;
			
			$this->storeServices($parameters, $argsValue);
			
			$this->storeAllDependencies = false;
			if ($argArray) {
				foreach ($argArray as $arg => $type) {

					
		    		if (!is_null($type)) {
		    			if (isset($this->storedObj[$type])) {

		    				$argArray[$arg] = $this->storedObj[$type];
		    				unset($this->storedObj[$type]);

		    				continue;

		    			}	
		    		}

		    		
		    		if (isset($argsValue[$arg])) {
	    				$argArray[$arg] = $argsValue[$arg];
	    			}
					
				}
			}
		}

		return $argArray;
	}


	/**
	 * to delete stored object call this function
	 *
	 * @param 	any
	 */
	public function delete(...$objNames)
	{
		if ($objNames) {

			foreach ($objNames as $objName) {
				$this->removeService($this->has($objName));
			}
		}

		return $this;
	}


	/**
	 * to store all injectable dependencies objects
	 * call this function..
	 * 
	 * @param  array 	$createdObj 
	 */
	private function storeAll($createdObj)
	{
		foreach ($createdObj as $class => $classObj) {
			if (!isset($this->storedObj[$class])) {
				$this->storedObj[$class] = $classObj;
			}
		}
	}

	
	/**
	 * check $name is available class or not
	 * 
	 * @param 	string 	$name
	 */
	private function checkNameIsExist($name)
	{
		if (class_exists($name) || interface_exists($name) || trait_exists($name)) {
			return $name;
		}
	}

	/**
	 * to store all dependency object
	 * set  parameter
	 *
	 * @param 	boolean 	$bool 
	 */
	public function storeAllDependencies(bool $bool = false)
	{
		$this->storeAllDependencies = $bool;
	}

	/**
	 * if argument value is in ['class.argname' => 'value'] formate
	 * reConstruct to inject it in calling method
	 *
	 * @param 	array 	$arrgsValue
	 */
	private function reConstructArray(array $argsValue)
	{
		$new_arry = [];
		// check upcoming array in which formate..
		foreach ($argsValue as $args => $value) {
			$arry = explode(".", $args);
			// if count two it means first is class name 
			// and second is argument name
			if (count($arry) === 2) {
				$new_arry[$arry[0]][$arry[1]] =  $value;
			}elseif (count($arry) === 1) {
				$new_arry[$arry[0]] = $value;
			}
		}
		// return new constructed array if constructed successfully..
		return $new_arry ? $new_arry : $argsValue;
	}

	/**
	 * remove stored object
	 *
	 * @param 	string 	$objName
	 */
	private function removeService($objName)
	{
		if (isset($this->storedObj[$objName])) {
			unset($this->storedObj[$objName]);
		}
	}


}