<?php

namespace LuisaeDev\SimplePDO;

use PDO;
use PDOStatement;
use PDOException;

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

	/** @var array Store the connection data */
	private $connectionData = null;

	/** @var string DSN connection string */
	private $dsn;

	/** @var bool Flag, define if the transaction is auto committed */
	private $autocommit = true;

	/**
	 * Constructor.
	 *
	 * @param array $connectionData Array with connection data values
	 * @param bool  $throws         Define if PDO instance should throws PDOExeption
	 *
	 * @throws PDOException
	 */
	public function __construct(array $connectionData, bool $throws = true)
	{

		// Merge the connection default data
		$connectionData = array_merge([
			'dbname'       => '',
			'user'         => 'root',
			'password'     => '',
			'driver'       => 'mysql',
			'host'         => '127.0.0.1',
			'port'         => '3306',
			'dsn-template' => '$driver:host=$host;port=$port;dbname=$dbname;charset=utf8'
		], $connectionData);
		
		// Build and save the DSN connection string
		$dsn = $connectionData['dsn-template'];
		$dsn = str_replace('$driver', $connectionData['driver'], $dsn);
		$dsn = str_replace('$host', $connectionData['host'], $dsn);
		$dsn = str_replace('$port', $connectionData['port'], $dsn);
		$dsn = str_replace('$dbname', $connectionData['dbname'], $dsn);
		$dsn = str_replace('$user', $connectionData['user'], $dsn);
		$dsn = str_replace('$password', $connectionData['password'], $dsn);
		$this->dsn = $dsn;

		// Create the PDO instance
		$this->pdoInstance = new PDO($this->dsn, $connectionData['user'], $connectionData['password']);

		// Remove the username and password and save the connection data
		unset($connectionData['user']);
		unset($connectionData['password']);
		$this->connectionData = $connectionData;

		// Enable throw exceptions for the PDO instance
		$this->pdoInstance->setAttribute(PDO::ATTR_ERRMODE, ($throws) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT);
	}

	/**
	 * Magic __get method.
	 */
	public function __get(string $property)
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
	 * @return self Self instance for chain
	 */
	public function beginTransaction(): self
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
	 * @return self Self instance for chain
	 */
	public function commit(): self
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
	 * @return self Self instance for chain
	 */
	public function rollBack(): self
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
	public function isAutocommit(): bool
	{
		return $this->autocommit;
	}

	/**
	 * Prepare the SQL statement.
	 *
	 * @param string $sql    SQL statement
	 * @param array  $params Params to bind
	 *
	 * @return self Self instance for chain
	 *
	 * @throws PDOException
	 */
	public function prepare(string $sql, array $params = array()): self
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
	 * @return self Self instance for chain
	 */
	public function bind(string $name, mixed $value, string $type): self
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
			default:
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
	public function getStatement(): PDOStatement|null
	{
		return $this->pdoStm;
	}

	/**
	 * Execute the current statement.
	 *
	 * @return self Self instance for chain
	 * 
	 * @throws PDOException
	 */
	public function execute(): self
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
	public function fetch(): array|false
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
	public function fetchObject(): object|false
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
	 * @return array All results obtained
	 */
	public function fetchAll(): array
	{
		if ((gettype($this->pdoStm) == 'object') && (method_exists($this->pdoStm, 'fetchAll'))) {
			return $this->pdoStm->fetchAll(PDO::FETCH_ASSOC);
		} else {
			return [];
		}
	}

	/**
	 * Return the last error produced.
	 *
	 * @return array Array with information about the error produced
	 */
	public function errorInfo(): array
	{
		if (method_exists($this->pdoStm, 'errorInfo')) {
			return $this->pdoStm->errorInfo();
		} else {
			return [
				'00000',
				null,
				null
			];
		}
	}

	/**
	 * Check if an error was produced.
	 *
	 * @return bool Confirm if exists an error
	 */
	public function errorExists(): bool
	{
		if (($this->errorinfo() !== null) && ($this->errorInfo()[1] !== null)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Return the last inserted ID.
	 *
	 * @return mixed Last inserted ID or null
	 */
	public function lastInsertId(): mixed
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
	 * @return int Return the total affected rows after an executed statement. If none records was affected, the result will be 0
	 */
	public function rowCount(): int
	{
		if (method_exists($this->pdoStm, 'rowCount')) {
			return $this->pdoStm->rowCount();
		} else {
			return 0;
		}
	}

	/**
	 * Return the DSN connection string.
	 *
	 * @return string
	 */
	public function getDSN(): string
	{
		return $this->dsn;
	}
	
	/**
	 * 'dbname' property.
	 *
	 * @return string
	 */
	private function get_dbname(): string
	{
		return $this->connectionData['dbname'];
	}

	/**
	 * 'driver' property.
	 *
	 * @return string
	 */
	private function get_driver(): string
	{
		return $this->connectionData['driver'];
	}

	/**
	 * 'host' property.
	 *
	 * @return string
	 */
	private function get_host(): string
	{
		return $this->connectionData['host'];
	}

	/**
	 * 'port' property.
	 *
	 * @return int
	 */
	private function get_port(): string
	{
		return $this->connectionData['port'];
	}
}
?>
