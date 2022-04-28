<?php

namespace LuisaeDev\SimplePDO;

use PDO;
use PDOStatement;

/**
 * Pretty simple and fancy class for handle PDO connections and PDO statements using just one class.
 *
 * @property-read string $dbname
 * @property-read string $driver
 * @property-read string $host
 * @property-read int    $port
 */
class SimplePDO {

	/** @var PDO Wrapped PDO Instance */
	private $pdoInstance;

	/** @var PDOStatement Current PDO Statement */
	private $pdoStm;

	/** @var array|string Store the connection data */
	private $connectionData = null;

	/** @var bool Flag, define if the transaction is auto committed */
	private $autocommit = true;

	/**
	 * Constructor.
	 *
	 * @param mixed $connectionData String connection or array with connection data
	 *
	 * @throws Exceptions\SimplePDOException
	 */
	public function __construct(mixed $connectionData)
	{

		// If connection data is an array
		if (is_array($connectionData)) {

			// Merge the connection default data
			$connectionData = array_merge([
				'user'     => '',
				'password' => '',
				'driver'   => 'mysql',
				'host'     => '127.0.0.1',
				'port'     => '3306'
			], $connectionData);
			
			// Check if the database name was not specified
			if (!isset($connectionData['dbname'])) {
				throw new Exceptions\SimplePDOException('Database name not specified', 1);
			}

			// Create the PDO instance
			switch ($connectionData['driver']) {
				case 'mysql':
					$this->pdoInstance = new PDO('mysql:host=' . $connectionData['host'] . ';port=' . $connectionData['port'] . ';dbname=' . $connectionData['dbname'] . ';charset=utf8', $connectionData['user'], $connectionData['password']);
					break;

				default:
					throw new Exceptions\SimplePDOException('Driver not supported', 2);
					break;
			}

			// Remove the password from the connection data
			unset($connectionData['password']);

		// If connection data is a string
		} else {
			$this->pdoInstance = new PDO($connectionData);
		}

		// Save the connection data
		$this->connectionData = $connectionData;

		// Enable reports and exceptions for the PDO instance
		$this->pdoInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Magic __get method.
	 */
	public function __get($property)
	{
		if (is_callable(array($this, $method = 'get_' . $property))) {
			return $this->$method();
		} else {
			return null;
		}
	}

	/**
	 * Start a transaction.
	 *
	 * Disable the 'autocommit' mode
	 *
	 * @return SimplePDO Self instance for chain
	 */
	public function beginTransaction()
	{
		if ($this->autocommit == true) {
			$this->autocommit = false;
			$this->pdoInstance->beginTransaction();
		}
		return $this;
	}

	/**
	 * Commit the current transaction.
	 *
	 * Commit the transaction and enable 'autocommit' mode
	 * 
	 * @return SimplePDO Self instance for chain
	 */
	public function commit()
	{
		if ($this->autocommit == false) {
			$this->autocommit = true;
			$this->pdoInstance->commit();
		}
		return $this;
	}

	/**
	 * RollBack the current transaction.
	 *
	 * RollBack the transaction and enable 'autocommit' mode
	 *
	 * @return SimplePDO Self instance for chain
	 */
	public function rollBack()
	{
		if ($this->autocommit == false) {
			$this->autocommit = true;
			$this->pdoInstance->rollBack();
		}
		return $this;
	}

	/**
	 * Indicate the autocommit state.
	 *
	 * @return bool
	 */
	public function isAutocommit()
	{
		return $this->autocommit;
	}

	/**
	 * Prepare the SQL statement.
	 *
	 * @param string $sql    SQL statement
	 * @param array  $params Params to bind
	 *
	 * @return SimplePDO Self instance for chain
	 */
	public function prepare(string $sql, array $params = array())
	{

		// Prepare and get the PDOStatement instance
		$this->pdoStm = $this->pdoInstance->prepare($sql);

		// Add the parameters
		foreach ($params as $key => $param) {
			$this->bind($key, $param[0], $param[1]);
		}

		return $this;
	}

	/**
	 * Bind a parameter to the PDO statement.
	 *
	 * @param string $name  Parameter's name
	 * @param mixed  $value Parameter's value
	 * @param string $type  Parameter's type
	 *
	 * @return SimplePDO Self instance for chain
	 */
	public function bind(string $name, mixed $value, mixed $type)
	{

		switch ($type) {
			case 'null':
				$type = PDO::PARAM_NULL;
				break;

			case 'bool':
				$type = PDO::PARAM_BOOL;
				break;

			case 'int':
				$type = PDO::PARAM_INT;
				break;

			case 'str':
				$type = PDO::PARAM_STR;
				break;
		}

		// Bind a parameter
		$this->pdoStm->bindParam($name, $value, $type);

		return $this;
	}

	/**
	 * Return the current statement defined after a call to 'prepare' method.
	 * 
	 * @return PDOStatement|null
	 */
	public function getStatement()
	{
		return $this->pdoStm;
	}

	/**
	 * Execute the current statement.
	 *
	 * @return SimplePDO Self instance for chain
	 */
	public function execute()
	{
		if ((gettype($this->pdoStm) == 'object') && (method_exists($this->pdoStm, 'execute'))) {
			$this->pdoStm->execute();
		}
		return $this;
	}

	/**
	 * Fetch and return the next row.
	 *
	 * @return array|false
	 */
	public function fetch()
	{
		if ((gettype($this->pdoStm) == 'object') && (method_exists($this->pdoStm, 'fetch'))) {
			return $this->pdoStm->fetch(PDO::FETCH_ASSOC);
		} else {
			return false;
		}
	}

	/**
	 * Fetch and return the next row as an object value.
	 *
	 * @return object|false
	 */
	public function fetchObject()
	{
		if ((gettype($this->pdoStm) == 'object') && (method_exists($this->pdoStm, 'fetchObject'))) {
			return $this->pdoStm->fetchObject();
		} else {
			return false;
		}
	}

	/**
	 * Fetch and return an array with all results.
	 *
	 * @param bool $assoc Define if the result is going to be fecth as an associative array
	 * 
	 * @return array
	 */
	public function fetchAll(bool $assoc = false)
	{
		if ((gettype($this->pdoStm) == 'object') && (method_exists($this->pdoStm, 'fetchAll'))) {
			return $this->pdoStm->fetchAll($assoc ? PDO::FETCH_ASSOC : PDO::FETCH_BOTH);
		} else {
			return [];
		}
	}

	/**
	 * Return the last produced error.
	 *
	 * @return array|null
	 */
	public function errorInfo()
	{
		if (method_exists($this->pdoInstance, 'errorInfo')) {
			return $this->pdoInstance->errorInfo();
		} else {
			return null;
		}
	}

	/**
	 * Return the last inserted ID.
	 *
	 * @return mixed|null
	 */
	public function lastInsertId()
	{
		if (method_exists($this->pdoInstance, 'lastInsertId')) {
			if ($this->pdoInstance->lastInsertId() == 0) {
				return null;
			} else {
				return $this->pdoInstance->lastInsertId();
			}
		} else {
			return null;
		}
	}

	/**
	 * Return the total affected rows after an executed statement.
	 *
	 * @return int
	 */
	public function rowCount()
	{
		if (method_exists($this->pdoStm, 'rowCount')) {
			return $this->pdoStm->rowCount();
		} else {
			return 0;
		}
	}

	/**
	 * Return the connection data.
	 *
	 * @return array|string
	 */
	public function getConnectionData()
	{
		return $this->connectionData;
	}
	
	/**
	 * 'dbname' property.
	 *
	 * @return string|null
	 */
	private function get_dbname()
	{
		if (!is_array($this->connectionData)) {
			return null;
		}
		return $this->connectionData['dbname'];
	}

	/**
	 * 'driver' property.
	 *
	 * @return string|null
	 */
	private function get_driver()
	{
		if (!is_array($this->connectionData)) {
			return null;
		}
		return $this->connectionData['driver'];
	}

	/**
	 * 'host' property.
	 *
	 * @return string|null
	 */
	private function get_host()
	{
		if (!is_array($this->connectionData)) {
			return null;
		}
		return $this->connectionData['host'];
	}

	/**
	 * 'port' property.
	 *
	 * @return int|null
	 */
	private function get_port()
	{
		if (!is_array($this->connectionData)) {
			return null;
		}
		return $this->connectionData['port'];
	}
}
?>
