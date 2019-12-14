<?php namespace Tatter\Firebase;

use Kreait\Firebase\Factory;

/**
 * Class Firebase
 *
 * A wrapper service for Kreait\Firebase\Factory that handles instance sharing.
 */
class Firebase
{
	/**
	 * Service account credentials, if not using autodiscovery
	 *
	 * @var mixed  Anything accepted by ServiceAccount::fromValue()
	 */
	protected $serviceAccount;

	/**
	 * A pre-authenticated instance of the factory
	 *
	 * @var Kreait\Firebase\Factory
	 */
	protected $factory;

	/**
	 * Cache for instances that have already been loaded.
	 *
	 * @var array
	 */
	protected $instances = [];
	
	/**
	 * Stores the path to the optional service account credentials file.
	 *
	 * @param mixed  $serviceAccount  Anything accepted by ServiceAccount::fromValue()
	 */
	public function __construct($serviceAccount = null)
	{
		$this->serviceAccount = $serviceAccount;
	}
	
	/**
	 * Checks for and creates an authenticated Factory
	 *
	 * @param \Config\App $config
	 * @param boolean     $getShared
	 *
	 * @return \CodeIgniter\HTTP\CLIRequest
	 */
	protected function factory(): Factory
	{
		// Check for an existing instance
		if ($this->factory !== null)
		{
			return $this->factory;
		}
		
		// Create a new instance
		$this->factory = new Factory();

		// If credentials were specified then add them
		if (! empty($this->serviceAccount))
		{
			$this->factory->withServiceAccount($this->serviceAccount);
		}

		return $this->factory;
	}

	//--------------------------------------------------------------------
	// Magic Functions
	//--------------------------------------------------------------------

	/**
	 * Returns or creates an instance from the corresponding class in the factory
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get(string $name)
	{
		$name = lcfirst($name);

		if (isset($this->instances[$name]))
		{
			return $this->instances[$name];
		}
		
		if ($method = $this->factoryHas($name))
		{
			$this->instances[$name] = $this->factory()->$method();
			return $this->instances[$name];
		}

		// Look for an internal component
		$className = 'Tatter\Firebase\Components\\' . ucfirst($name);
		if (class_exists($className))
		{
			return new $className();
		}

		throw new \Exception("Property {$name} does not exist");
	}

	/**
	 * Checks for the existence of an instance or its corresponding Factory method
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function __isset(string $name): bool
	{
		$name = lcfirst($name);

		if (isset($this->instances[$name]))
		{
			return true;
		}
		elseif ($this->factoryHas($name))
		{
			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Passes method calls through to the factory.
	 *
	 * @param string $name
	 * @param array  $params
	 *
	 * @return Model|null
	 */
	public function __call(string $name, array $params)
	{
		$result = $this->factory()->$name(...$params);
	}

	//--------------------------------------------------------------------
	// Helper Utilities
	//--------------------------------------------------------------------

	/**
	 * Checks the Factory for a "create" method
	 * corresponding to a property name.
	 *
	 * @param string $name
	 *
	 * @return ?string  The name of the corresponding method, or null if not exists
	 */
	protected function factoryHas(string $name): ?string
	{
		$method = 'create' . ucfirst($name);
		return is_callable([$this->factory(), $method]) ? $method : null;
	}
}