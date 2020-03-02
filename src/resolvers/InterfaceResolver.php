<?php

namespace aditex\src\resolvers;

/**
 * this is name resolver class, which resolves
 *
 * interfaces name with required class name that should 
 *
 * be used during processing of this application..
 *
 * InterfaceResolver uses initTx class to get
 *
 * Enteries in perticular formate. so that, further process
 *
 * can be done without any interuption..
 *
 */
class InterfaceResolver
{
	/**
	 * interface mapped array..
	 *
	 * @var array
	 */
	public $ResolverEntries = [];

	/**
	 * if file path exist set resolver array
	 * 
	 * @param 	object 	initTx
	 */
	public function __construct()
	{

		$mapper = $this->getMapperPath();
		$this->setEnteries($mapper);
		
	}

	/** 
	 * resolve Interface name and replace with, 
	 * corressponding class name 
	 *
	 * @param 	string 		$InterfaceName
	 * @return 	string 		class name
	 */
	public function resolve($InterfaceName)
	{
		if (array_key_exists($InterfaceName, $this->ResolverEntries)) {
			return $this->resolvedEntry($this->ResolverEntries[$InterfaceName]);
		}
	}

	/**
	 * if array return callback function then, 
	 * call function and get returned value
	 * 
	 * @param 	string or callback object
	 * @return 	class name
	 */
	private function resolvedEntry($callback)
	{
		return is_object($callback) ? $callback() : $callback;
	}

	/**
	 * get interface mapping file path
	 */
	private function getMapperPath()
	{
		$configArry = include __dir__. "/../../config/ADI.php";
		return $configArry['mapper'];
	}

	/**
	 * set Enteries
	 * 
	 * @param 	string 	$mapper
	 */
	private function setEnteries($mapper)
	{
		if (!$mapper) {
			throw new \Exception("mapper file path not specified!!");
		}

		if (file_exists($mapper)) {

			$this->ResolverEntries = include $mapper;
			return;

		}else
			throw new \Exception("'{$mapper}' failed to open dir: No such file or directory");

	}
}