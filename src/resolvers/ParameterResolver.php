<?php

namespace Aditex\src\resolvers;

use Aditex\src\exception\ParameterNotFoundException;
use Aditex\src\exception\BindingResolutionException;
use Aditex\src\resolvers\InterfaceResolver;

/**
 * parameter Resolver class is used to 
 *
 * get function argument name using core php reflaction class
 *
 * this class uses typehinting method to get dependency name,
 * 
 */
class ParameterResolver
{

    /**
     * calling object
     *
     * @var string
     */
    public $object;

    /**
     * calling method
     *
     * @var string
     */
    public $method;

    /**
	 * method parameter holder
	 *
     * @var array
     */
	public $parameters = [];

	/**
     * default param Values
     *
     * @var array
     */
    public $defaults = [];

	
	function __construct($object, $method)
	{
		$this->object = $object;
		$this->method = $method;
	}

	/**
	 * get constructor or method arguments
	 * of the class and set it into $this class property
	 *
	 * @param 	string 	$name (class name)
	 * @param 	string 	$method (class method)
	 *
	 * @return 	array 	calling method arguments
	 */
	public function set()
	{
		
		$parameters = $this->getMethodArgs($this->object, $this->method);

		$this->parameters[$this->object] = $parameters;

		if ($parameters) {
			$this->setParameterIfDependent($this->parameters[$this->object]);
		}
		
		return $this->parameters;
	}

	/**
	 * get method arguments name and there type
	 * using reflaction class 
	 *
	 * @param 	string 		$object (class name)
	 * @param 	string 		$methodName (class method name)
	 *
	 * @return array, [args name => args type]
	 */
	private function getMethodArgs(string $object,string $methodName)
	{
		try{
			$this->temp = $object;
			$reflectionClass = new \ReflectionClass($object);
			try{
				
				$reflectionMethod = $reflectionClass->getMethod($methodName);

				if ($parameters = $reflectionMethod->getParameters()) {
					return $this->parameterTypeName($parameters);
				}

			}catch(\ReflectionException $e){
				return;
			}
		}catch(ReflectionException $err){
			throw new ParameterNotFoundException($err->getMessage());
		}
	}


	/**
	 * Parse argument type and return array in which key as argument name 
	 * and value as argument type..
	 *
	 * @param 	array  	$parameters
	 *
	 * @return 	array 	[argsName => argsType,...]
	 */
	private function parameterTypeName(array $parameters)
	{
		$typeName = [];$defaults = [];
		foreach ($parameters as $parameter) {

			// set default values
			if ($parameter->isOptional()) {
				$defaults[$parameter->name] = $parameter->getDefaultValue();
			}

			if ($parameter instanceof \ReflectionParameter) {
				$type = $parameter->gettype();
			}

			if ($type instanceof \ReflectionNamedType) {

				$typeName[$parameter->name] =  $type->getName();

			}else{

				$typeName[$parameter->name] = null;

			}
		}

		if ($defaults) {
			// set default values 
			$this->defaults[$this->temp]  = $defaults;
		}

		return $typeName;
	}



	/**
	 * if method argument has dependency of deferent class 
	 * check dependency constructor if constructor has there
	 * own dendency check further dependency constructor till 
	 * constructor has no dependency..
	 *
	 * push all constructor arguments name and type in $this class 
	 * property name parameters recursively..
	 *
	 * @param 	array 	$Args
	 *
	 * @return 	null 	
	 */
	private function setParameterIfDependent(array $arguments)
	{
		$methodArgs = [];

		if (!isset($InterfaceResolver)) {
			$InterfaceResolver = new InterfaceResolver();
		}
		
		foreach ($arguments as $argName => $parameterName) {

			// resolve interface
			if (interface_exists($parameterName)) {
				
				$parameterName = $InterfaceResolver->resolve($parameterName);
				$this->parameters[$this->object][$argName] = $parameterName;

			}

			if (is_null($parameterName)) {
				continue;
			}

			if ($this->IsDependent($parameterName)) {
				$methodArgs[$parameterName] = $this->getMethodArgs($parameterName,"__construct");
			}

		}
		
		if ($methodArgs) {

			foreach ($methodArgs as $key => $args) {
				
				if (!array_key_exists($key, $this->parameters)) {
					$this->parameters[$key] = $args;
				}

				if (is_array($args)) {
					foreach ($args as  $name => $arg) {

						if (interface_exists($arg)) {
							$arg = $InterfaceResolver->resolve($arg);
							// update Parameter when interface
							$args[$name] = $arg;
							$this->parameters[$key] = $args;
						}

						if ($this->IsDependent($arg)) {
				
							$this->setParameterIfDependent($args);

						}else{
							
							$this->checkBindingResolution($arg);
						}
					}
				}
			}
		}
	}

	/**
	 * check, argsType is a dependency or not..
	 * 
	 * @param 	string 		$parameterName
	 *
	 * @return 	bool 
	 */
	private function IsDependent($parameterName)
	{
		return class_exists($parameterName) && !array_key_exists($parameterName, $this->parameters) ? true : false;
	}

	/**
	 * check binding resolution..
	 *
	 * @param 	string 	$parameterName
	 */
	private function checkBindingResolution($parameterName)
	{
		// if dendency name is ethier of given below..return null
		switch ($parameterName) {
			case '':
				break;
			case 'string':
				break;
			case 'int':
				break;
			case 'array':
				break;
			case 'bool':
				break;
			case 'float':
				break;
			// else throw binding error resolution
			default:
				throw new BindingResolutionException("Cannot instantiate: {$parameterName}");
				break;
		}
	}

}