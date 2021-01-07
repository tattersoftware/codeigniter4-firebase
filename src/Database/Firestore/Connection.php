<?php

namespace Tatter\Firebase\Database\Firestore;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Events\Events;
use Config\Services;
use Google\Cloud\Firestore\FirestoreClient;
use Throwable;

class Connection extends BaseConnection
{
	/**
	 * @var FirestoreClient
	 */
	protected $client;

	/**
	 * Error
	 *
	 * @var array|null
	 */
	protected $error;

	/**
	 * Connect to the database.
	 *
	 * @param  boolean $persistent
	 * @return mixed
	 */
	abstract public function connect(bool $persistent = false);

	/**
	 * Connect
	 *
	 * @param bool $persistent Not relevant to Firestore
	 *
	 * @return FirestoreClient|null
	 */
	public function connect(bool $persistent = false): ?FirestoreClient
	{
		try
		{
			$this->client = Services::firebase()->firestore->database();

			return $this->client;
		}
		catch (Throwable $e)
		{
			$this->error['code']    = $e->getCode();
			$this->error['message'] = $e->getMessage();

			return null;
		}
	}

	/**
	 * Platform dependent way method for closing the connection.
	 *
	 * @return mixed
	 */
	abstract protected function _close();

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 *
	 * @return mixed
	 */
	abstract public function reconnect();

	/**
	 * Select a specific database table to use.
	 *
	 * @param string $databaseName
	 *
	 * @return mixed
	 */
	abstract public function setDatabase(string $databaseName);

	/**
	 * Returns a string containing the version of the database being used.
	 *
	 * @return string
	 */
	abstract public function getVersion(): string;

	/**
	 * Executes the query against the database.
	 *
	 * @param string $sql
	 *
	 * @return mixed
	 */
	abstract protected function execute(string $sql);

	/**
	 * Begin Transaction
	 *
	 * @return boolean
	 */
	abstract protected function _transBegin(): bool;

	//--------------------------------------------------------------------

	/**
	 * Commit Transaction
	 *
	 * @return boolean
	 */
	abstract protected function _transCommit(): bool;

	//--------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 *
	 * @return boolean
	 */
	abstract protected function _transRollback(): bool;

	/**
	 * Returns the total number of rows affected by this query.
	 *
	 * @return integer
	 */
	abstract public function affectedRows(): int;

	/**
	 * Returns the last error code and message.
	 *
	 * Must return an array with keys 'code' and 'message':
	 *
	 *  return ['code' => null, 'message' => null);
	 *
	 * @return array
	 */
	abstract public function error(): array;

	//--------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @return integer|string
	 */
	abstract public function insertID();

	//--------------------------------------------------------------------

	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 *
	 * @param boolean $constrainByPrefix
	 *
	 * @return string|false
	 */
	abstract protected function _listTables(bool $constrainByPrefix = false);

	//--------------------------------------------------------------------

	/**
	 * Generates a platform-specific query string so that the column names can be fetched.
	 *
	 * @param string $table
	 *
	 * @return string|false
	 */
	abstract protected function _listColumns(string $table = '');

	//--------------------------------------------------------------------

	/**
	 * Platform-specific field data information.
	 *
	 * @param  string $table
	 * @see    getFieldData()
	 * @return array
	 */
	abstract protected function _fieldData(string $table): array;

	//--------------------------------------------------------------------

	/**
	 * Platform-specific index data.
	 *
	 * @param  string $table
	 * @see    getIndexData()
	 * @return array
	 */
	abstract protected function _indexData(string $table): array;

	//--------------------------------------------------------------------

	/**
	 * Platform-specific foreign keys data.
	 *
	 * @param  string $table
	 * @see    getForeignKeyData()
	 * @return array
	 */
	abstract protected function _foreignKeyData(string $table): array;
}
