# SimplePDO

<aside>
#️⃣ v1.0.0

</aside>

A pretty simple and fancy class for handle PDO connections and PDO statements using just one class.

# Usage

```php
<?php

	require_once __DIR__ . '/vendor/autoload.php';

	use LuisaeDev\SimplePDO\SimplePDO;

	$sPDO =  new SimplePDO([
		'dbname'   => 'test',
		'user'	 => 'root',
		'password' => ''
	]);

	$sPDO
		->prepare('SELECT * FROM users WHERE id > :status', [
			':status' => [1, 'int']
		])
		->execute();

	while ($row = $sPDO->fetch()) {
		print_r($row);
	}

?>
```

# Installation

You can install SimplePDO class via composer.

```php
composer require luisaedev/simple-pdo
```

# Documentation

See an [interactive documentation here](https://luisaedev.notion.site/SimplePDO-1047e4498f034354a7384012c93a0c69).

# Requirements

SimplePDO use PHP version 8.0 or higher and [PDO](https://www.php.net/manual/en/intro.pdo.php) extension .

# Constructor

```php
use LuisaeDev\SimplePDO\SimplePDO;

$simplePDO = new SimplePDO([
		'dbname'   => 'test',
		'user'     => 'root',
		'password' => ''
]);
```

```php
use LuisaeDev\SimplePDO\SimplePDO;

$simplePDO = new SimplePDO([
		'dbname'       => 'test',
		'user'         => 'root',
		'password'     => '',
		'driver'       => 'mysql',
		'host'         => '127.0.0.1',
		'port'         => '3306',
		'dsn-template' => '$driver:host=$host;port=$port;dbname=$dbname;charset=utf8'
], true);
```

### Arguments

| Argument name | Value Type | Description | Default Value |
| --- | --- | --- | --- |
| $connectionData | array | Array with connection data values |  |
| $throws | bool | Define if PDO instance should throws PDOExeption | true |

### Throws

| Exception Class | Code | Description |
| --- | --- | --- |
| PDOException |  | All PDO exceptions throws by PDO class |

# Methods

## beginTransaction()

Start a transaction and Disable the 'autocommit' mode.

```php
$simplePDO
	->beginTransaction()
	->prepare('DELETE FROM users WHERE id=:id')
	->bind(':id', 1, 'int')
	->execute()
	->commit();
```

### Arguments

none

### Return

| Value Type | Description |
| --- | --- |
| self | Self instance for chain |

## bind($name, $value, $type)

Binds a parameter to the PDO statement.

```php
$simplePDO->bind(':name', 'Jimmy', 'str');
```

### Arguments

| Argument name | Value Type | Description |
| --- | --- | --- |
| $name | string | Name for the parameter to bind |
| $value | mixed | Value for the parameter to bind |
| $type | string | Type for the parameter. Allowed values are: “int”, “str”, “bool”, “null” |

### Return

| Value Type | Description |
| --- | --- |
| self | Self instance for chain |

## commit()

Commit the current transaction and enable 'autocommit' mode.

```php
$simplePDO
	->beginTransaction()
	->prepare('DELETE FROM users WHERE id=:id')
	->bind(':id', 1, 'int')
	->execute()
	->commit();
```

### Arguments

none

### Return

| Value Type | Description |
| --- | --- |
| self | Self instance for chain |

## errorExists()

Check if an error was produced

```php
if ($simplePDO->errorExists()) {
	...
}
```

### Arguments

none

### Return

| Value Type | Description |
| --- | --- |
| bool | Confirm if exists an error |

## errorInfo()

Return the last error produced

```php
$error = $simplePDO->errorInfo();
```

### Arguments

none

### Return

| Value Type | Description |
| --- | --- |
| array | Array with information about the error produced.
More information see official documentation
https://www.php.net/manual/en/pdo.errorinfo.php |

## execute()

Executes the current PDO statement defined after a call to ‘prepare’ method.

```php
$simplePDO
	->prepare('SELECT * FROM users WHERE id=:id')
	->bind(':id', 1, 'int')
	->execute();
```

### Arguments

None

### Return

| Value Type | Description |
| --- | --- |
| self | Self instance for chain |

### Throws

| Exception Class | Code | Description |
| --- | --- | --- |
| PDOException |  | All PDO exceptions throws by PDOStatement class |

## fetch()

Fetch and return the next row.

```php
$result = $simplePDO
	->prepare('SELECT * FROM users WHERE id=:id')
	->bind(':id', 1, 'int')
	->execute()
	->fetch();
```

### Arguments

None

### Return

| Value Type | Description |
| --- | --- |
| array|false | Record obtained or false |

## fetchAll()

Fetch and return an array with all results.

```php
$result = $simplePDO
	->prepare('SELECT * FROM users')
	->execute()
	->fetchAll();
```

### Arguments

none

### Return

| Value Type | Description |
| --- | --- |
| array | All results obtained |

## fetchObject()

Fetch and return the next row as an object value.

```php
$result = $simplePDO
	->prepare('SELECT * FROM users WHERE id=:id')
	->bind(':id', 1, 'int')
	->execute()
	->fetchObject();
```

### Arguments

None

### Return

| Value Type | Description |
| --- | --- |
| object|false | Record obtained or false |

## getDSN()

Return the DSN connection string.

```php
$dsn= $simplePDO->getDSN();
```

### Arguments

none

### Return

| Value Type | Description |
| --- | --- |
| string | DSN connection string |

## getStatement()

Return the current PDO statement defined after a call to 'prepare' method.

```php
$pdoStd = $simplePDO->getStatement();
```

### Arguments

none

### Return

| Value Type | Description |
| --- | --- |
| PDOStatement|null | Current PDO statement |

## isAutocommit()

Indicate the autocommit state.

```php
if ($simplePDO->isAutocommit() == false) {
	$simplePDO->commit();
}

```

### Arguments

none

### Return

| Value Type | Description |
| --- | --- |
| bool | Autocommit state |

## lastInsertId()

Return the last inserted ID.

```php
$id = $simplePDO
	->prepare('INSERT INTO users (name) VALUES (:name)')
	->bind(':name', 1, 'Saul')
	->execute()
	->lastInsertId();
```

### Arguments

none

### Return

| Value Type | Description |
| --- | --- |
| mixed | Last inserted ID or null in caso that did not insert any record |

## prepare($sql, $params = array())

Prepares a PDO statement and binds the passed parameters.

```php
$simplePDO->prepare('SELECT * FROM users WHERE id=:id', [
	':id' => [1, 'int']
]);
```

### Arguments

| Argument name | Value Type | Description | Default Value |
| --- | --- | --- | --- |
| $sql | string | SQL statement definition |  |
| $params | array | Optional value. Associative array for define all the parameter that will be bind to the statement | array() |

### Return

| Value Type | Description |
| --- | --- |
| self | Self instance for chain |

### Throws

| Exception Class | Code | Description |
| --- | --- | --- |
| PDOException |  | All PDO exceptions throws by PDOStatement class |

## rollBack()

RollBack the current transaction and enable 'autocommit' mode.

```php
$simplePDO
	->beginTransaction()
	->prepare('DELETE FROM users WHERE id=:id')
	->bind(':id', 1, 'int')
	->execute()
	->rollBack();
```

### Arguments

none

### Return

| Value Type | Description |
| --- | --- |
| self | Self instance for chain |

## rowCount()

Return the total affected rows after an executed statement

```php
$rowCount = $simplePDO
	->prepare('DELETE FROM users WHERE id=:id')
	->bind(':id', 1, 'int')
	->execute()
	->rowCount();
```

### Arguments

none

### Return

| Value Type | Description |
| --- | --- |
| int | Return the total affected rows after an executed statement. If none records was affected, the result will be 0 |

# Properties

## dbname

Return the database name. (Read only property).

| Value Type |
| --- |
| string |

## driver

Return the database driver: ‘mysql’, ‘sqlite’. (Read only property).

| Value Type |
| --- |
| string |

## host

Return the host. (Read only property).

| Value Type |
| --- |
| string |

## port

Return the port. (Read only property).

| Value Type |
| --- |
| int |
