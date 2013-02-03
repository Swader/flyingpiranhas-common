<?php

namespace flyingpiranhas\common\database\abstracts;

use flyingpiranhas\common\database\exceptions\DatabaseException;
use flyingpiranhas\common\database\interfaces\AdapterInterface;
use flyingpiranhas\common\database\Registry;
use PDO;
use PDOStatement;


/**
 * The AdapterAbstract description.
 *
 * @category       database
 * @package        flyingpiranhas.common
 * @license        BSD License
 * @version        0.01
 * @since          2012-11-23
 * @author         Bruno Škvorc <bruno@skvorc.me>
 * @author         Ivan Pintar
 */
abstract class AdapterAbstract implements AdapterInterface
{

    const RETURN_LAST_ID = 1;
    const RETURN_AFFECTED_ROWS = 2;
    const RETURN_BOTH = 3;

    /** @var bool */
    protected static $bRegistryIntegration = true;

    /** @var string Unique adapter name */
    protected $sAdapterName = '';

    /** @var string Host to connect to */
    protected $sDbHost = '127.0.0.1';

    /** @var string Database to connect to */
    protected $sDbName = '';

    /** @var string Username to connect with */
    protected $sDbUsername = '';

    /** @var string Password to connect with */
    protected $sDbPassword = '';

    /** @var int Port to connect on. Overridden in child adapters */
    protected $iDbPort = 0;

    /** @var string Type of database to connect to - overridden in children */
    protected $sDbType = '';

    /** @var bool Whether or not to use persistent connections */
    protected $bPersistent = false;

    /** @var int Set PDO's error mode */
    protected $iErrorMode = PDO::ERRMODE_EXCEPTION;

    /** @var int Initialize transaction level for taking care of nested transactions */
    protected $iTransactionLevel = 0;

    /** @var string Full query string to be executed */
    protected $sQueryString = '';

    /** @var PDO|null */
    protected $oDbHandle = null;

    /** @var string Unique has based on params */
    protected $sHash = '';

    /**
     * The constructor can accept properties which get auto-set
     * for the connection.
     *
     * @param array|string $aProperties
     */
    public function __construct($aProperties = array())
    {
        if (!empty($aProperties)) {
            if (is_array($aProperties)) {
                $this->setProperties($aProperties);
            } else if (is_string($aProperties) && is_readable($aProperties)) {
                $aProperties = parse_ini_file($aProperties);
                $this->setProperties($aProperties);
            }
        }
    }

    /**
     * Sets whether or not adapters should autocheck for Registry autoadd
     * when opening the connection for the first time.
     *
     * @param bool $bIntegration
     */
    public static function setRegistryIntegration($bIntegration)
    {
        self::$bRegistryIntegration = (bool) $bIntegration;
    }

    /**
     * Returns whether or not adapters autocheck for Registry autoadd when
     * opening the connection for the first time
     *
     * @return bool
     */
    public static function getRegistryIntegration()
    {
        return self::$bRegistryIntegration;
    }

    // Getters
    //<editor-fold desc="Getters">
    /**
     * Returns adapter name.
     * If adapter name is empty, hash is calculated and used as name instead
     *
     * @return string
     */
    public function getAdapterName()
    {
        if (empty($this->sAdapterName)) {
            $this->sAdapterName = $this->getHash();
        }

        return $this->sAdapterName;
    }

    /**
     * Returns an MD5 hash of this adapter's params.
     * Useful for storing active adapters without reinstantiating
     *
     * Once a hash is defined, it will never change even if some of
     * the parameters do.
     *
     * @return string
     */
    public function getHash()
    {
        if (empty($this->sHash)) {
            $this->sHash = md5(
                    $this->sDbHost
                    . $this->sAdapterName
                    . $this->sDbName
                    . $this->sDbType
                    . $this->sDbUsername
                    . $this->sDbPassword
            );
        }

        return $this->sHash;
    }

    /**
     * Returns database host (default 127.0.0.1)
     *
     * @return string
     */
    public function getDbHost()
    {
        return $this->sDbHost;
    }

    /**
     * Returns database name
     *
     * @return string
     */
    public function getDbName()
    {
        return $this->sDbName;
    }

    /**
     * Returns username
     *
     * @return string
     */
    public function getDbUsername()
    {
        return $this->sDbUsername;
    }

    /**
     * Returns database port
     *
     * @return int
     */
    public function getDbPort()
    {
        return $this->iDbPort;
    }

    /**
     * Returns full query string
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->sQueryString;
    }

    //
    // Setters
    //<editor-fold desc="Setters">
    /**
     * Accepts a non-empty string and sets it as the adapter name.
     * If empty string is given, an exception will be thrown.
     *
     * @param string $sAdapterName
     *
     * @return AdapterAbstract
     * @throws DatabaseException
     */
    public function setAdapterName($sAdapterName)
    {
        $this->sAdapterName = trim($sAdapterName);
        if (empty($this->sAdapterName)) {
            throw new DatabaseException('Adapter name cannot be empty');
        }

        return $this;
    }

    /**
     * Accepts a non-empty string and sets it as the adapter host.
     * If empty string is given, an exception will be thrown.
     *
     * @param string $sDbHost
     *
     * @return AdapterAbstract
     * @throws DatabaseException
     */
    public function setDbHost($sDbHost)
    {
        $this->sDbHost = trim($sDbHost);
        if (empty($this->sDbHost)) {
            throw new DatabaseException('Host cannot be empty');
        }

        return $this;
    }

    /**
     * Accepts a string as the database name to connect to
     *
     * @param string $sDbName
     *
     * @return AdapterAbstract
     */
    public function setDbName($sDbName)
    {
        $this->sDbName = trim($sDbName);

        return $this;
    }

    /**
     * Accepts a non-empty string and sets it as the adapter username.
     * If empty string is given, an exception will be thrown.
     *
     * @param string $sDbUsername
     *
     * @return AdapterAbstract
     * @throws DatabaseException
     */
    public function setDbUsername($sDbUsername)
    {
        $this->sDbUsername = trim($sDbUsername);
        if (empty($this->sDbUsername)) {
            throw new DatabaseException('Username cannot be empty');
        }

        return $this;
    }

    /**
     * Accepts a string as the database name to connect to
     *
     * @param string $sDbPassword
     *
     * @return AdapterAbstract
     */
    public function setDbPassword($sDbPassword)
    {
        $this->sDbPassword = trim($sDbPassword);

        return $this;
    }

    /**
     * Changes database connection port
     *
     * @param int $iDbPort
     *
     * @return AdapterAbstract
     */
    public function setDbPort($iDbPort)
    {
        $this->iDbPort = (int) trim($iDbPort);

        return $this;
    }

    /**
     * Helper method for mass-setting properties provided through the
     * configuration or the constructor
     *
     * @param array $sProperties
     *
     * @return AdapterAbstract
     */
    public function setProperties($sProperties)
    {
        foreach ($sProperties as $k => $v) {
            $k = ucfirst(trim($k));
            $sSetter = "set" . $k;
            if (method_exists($this, $sSetter)) {
                $this->$sSetter($v);
            }
        }

        return $this;
    }

    /**
     * Sets the error mode. Best to leave at default value.
     *
     * @param int $iErrorMode
     *
     * @return AdapterAbstract
     */
    public function setErrorMode($iErrorMode = PDO::ERRMODE_EXCEPTION)
    {
        $this->iErrorMode = $iErrorMode;

        return $this;
    }

    //


    /**
     * Connects to the respective database server
     *
     * @param bool $bPersistent DEF: false; if true, it will open a persistent connection
     * @param int  $iErrorMode  DEF: \PDO::ERRMODE_EXCEPTION;
     *                          possible values \PDO::ERRMODE_SILENT; \PDO::ERRMODE_WARNING; \PDO::ERRMODE_EXCEPTION
     *
     * @return resource
     */
    protected function openConnection($bPersistent = false, $iErrorMode = null)
    {
        // if a connection is already established return the connection
        if ($this->oDbHandle instanceof PDO) {
            return $this;
        }

        // set error mode
        if ($iErrorMode) {
            $this->setErrorMode($iErrorMode);
        }

        // connect to database
        $dsn = "{$this->sDbType}:host={$this->sDbHost};port={$this->iDbPort}";
        if (!empty($this->sDbName)) {
            $dsn .= ";dbname={$this->sDbName}";
        }

        $options = array();
        if ($bPersistent) {
            $options = array(
                PDO::ATTR_PERSISTENT => true,
            );
        }
        $this->oDbHandle = new PDO($dsn, $this->sDbUsername, $this->sDbPassword, $options);

        // set error mode
        $this->oDbHandle->setAttribute(PDO::ATTR_ERRMODE, $this->iErrorMode);

        // set charset to utf-8
        $this->query("SET NAMES 'UTF8'");

        // By now, everything went well. If class Registry is available, the
        // adapter adds itself into it.

        if (self::getRegistryIntegration()) {
            if (Registry::getAutoAdd()) {
                Registry::registerAdapter($this->getAdapterName(), $this);
            }
        }

        return $this;
    }

    /**
     * Closes the pdo connection by nullifying the dbHandle property
     *
     * @return AdapterAbstract
     */
    protected function closeConnection()
    {
        $this->oDbHandle = null;

        return $this;
    }

    /**
     * Test the connection, tries to momentarily open the connection to the database server.
     * Returns true on success and throws PDO exception on failure
     *
     * @return bool
     */
    public function testConnection()
    {
        // try to connect, return false if connection fails
        $this->openConnection();

        // close the connection if it was successful and return true
        $this->closeConnection();

        return true;
    }

    //


    /**
     * Opens a new transaction or just increments the transaction level
     * if a transaction has already been started
     *
     * @return AdapterAbstract
     */
    public function beginTransaction()
    {
        $this->openConnection();

        if ($this->iTransactionLevel == 0) {
            $this->oDbHandle->beginTransaction();
        }
        $this->iTransactionLevel++;

        return $this;
    }

    /**
     * Commits the started transaction
     *
     * @return AdapterAbstract
     * @throws DatabaseException
     */
    public function commitTransaction()
    {
        if ($this->iTransactionLevel == 0) {
            throw new DatabaseException('Trying to commit a transaction that was not started');
        }

        $this->oDbHandle->commit();

        return $this;
    }

    /**
     * Rolls back the transaction and sets the transaction level to 0
     *
     * @return AdapterAbstract
     * @throws DatabaseException
     */
    public function rollbackTransaction()
    {
        if ($this->iTransactionLevel == 0) {
            throw new DatabaseException('Trying to rollback a transaction that was not started');
        }

        $this->oDbHandle->rollBack();
        $this->iTransactionLevel = 0;

        return $this;
    }

    //


    /**
     * Prepares and executes the given query
     *
     * @param string $sql
     * @param array  $aBindParams DEF: array(); array of $k => $v pairs
     * @param int    $sFetchMode  DEF: \PDO::FETCH_ASSOC; the fetch mode for the query, applies only to select queries
     *
     * @return array
     */
    public function query($sql, $aBindParams = array(), $sFetchMode = PDO::FETCH_ASSOC)
    {

        // execute the query
        $this->openConnection();
        $this->sQueryString = $sql;
        $oStmt = $this->prepare($this->sQueryString);

        $oStmt->execute($aBindParams);
        if (strpos(trim(strtolower($this->sQueryString)), 'select') === 0) {
            return $oStmt->fetchAll($sFetchMode);
        } else {
            return $oStmt->rowCount();
        }
    }

    /**
     * Returns the PDOStatement object for the given query
     *
     * @param string $sql
     *
     * @return PDOStatement
     * @throws DatabaseException
     */
    public function prepare($sql)
    {
        // check sql
        $sql = trim($sql);
        if (empty($sql)) {
            throw new DatabaseException('Trying to prepare an empty sql string');
        }

        // return the statement object
        $this->openConnection();
        $oStmt = $this->oDbHandle->prepare($sql);

        return $oStmt;
    }

    //<editor-fold desc="Fetching">
    /**
     * Returns a two dimensional array of rows
     * Rows are indexed by number, starting with 0
     *
     * @param string $sql
     * @param array  $sBindParams DEF: array(); array of $k => $v pairs
     * @param int    $sFetchMode  DEF: \PDO::FETCH_ASSOC; the fetch mode for the query
     *
     * @return array
     */
    public function fetchAll($sql, $sBindParams = array(), $sFetchMode = PDO::FETCH_ASSOC)
    {
        return $this->query($sql, $sBindParams, $sFetchMode);
    }

    /**
     * Returns a two dimensional array of rows
     * The first column in a row is the key of each row
     * For duplicate keys, the last row overwrites all others
     *
     * @param string $sql
     * @param array  $aBindParams DEF: array(); array of $k => $v pairs
     *
     * @return array
     */
    public function fetchAssoc($sql, $aBindParams = array())
    {
        $rows = $this->fetchAll($sql, $aBindParams, PDO::FETCH_BOTH);

        $rowsAssoc = array();
        foreach ($rows as $row) {
            $tmp = $row[0];
            foreach ($row as $k => $v) {
                if (is_int($k)) {
                    unset($row[$k]);
                }
            }
            $rowsAssoc[$tmp] = $row;
        }

        return $rowsAssoc;
    }

    /**
     * Returns a one dimensional array of the first row found by the query
     *
     * @param string $sql
     * @param array  $sBindParams DEF: array(); array of $k => $v pairs
     *
     * @return array
     */
    public function fetchRow($sql, $sBindParams = array())
    {
        $rows = $this->fetchAll($sql, $sBindParams);
        if (isset($rows[0])) {
            return $rows[0];
        }

        return array();
    }

    /**
     * Returns a one dimensional array of the first column in the record set
     *
     * @param string $sql
     * @param array  $sBindParams DEF: array(); array of $k => $v pairs
     *
     * @return array
     */
    public function fetchCol($sql, $sBindParams = array())
    {
        return $this->query($sql, $sBindParams, PDO::FETCH_COLUMN);
    }

    /**
     * Returns the value of the first column in the first row of the record set
     *
     * @param string $sql
     * @param array  $sBindParams DEF: array(); array of $k => $v pairs
     *
     * @return array
     */
    public function fetchOne($sql, $sBindParams = array())
    {
        $rows = $this->fetchAll($sql, $sBindParams, PDO::FETCH_NUM);
        if (isset($rows[0][0])) {
            return $rows[0][0];
        }

        return false;
    }

    //

    /**
     * Inserts a single row into the given table
     * Returns either the number of inserted rows or the id of the last inserted row
     *
     * @param string $sTableName
     * @param array  $sBindParams DEF: array(); array of $k => $v pairs
     * @param int    $iReturn     DEF: self::RETURN_LAST_ID;
     *                            the function will return either the inserted row id or the number of inserted rows
     *                            available values: RETURN_LAST_ID, RETURN_AFFECTED_ROWS, RETURN_BOTH (returns array)
     *
     * @todo: Additional sanitizing of placeholders/columns required
     *
     * @return int
     * @throws DatabaseException
     */
    public function insert($sTableName, $sBindParams, $iReturn = self::RETURN_LAST_ID)
    {
        // check if table is ok
        $sTableName = trim($sTableName);
        if (empty($sTableName)) {
            throw new DatabaseException('Table name is not a valid string. Must be non-empty.');
        }

        // build columns and placeholders
        $sColumns = array();
        $sPlaceholders = array();
        foreach ($this->quotePlaceholders($sBindParams) as $sCol => $sPh) {
            $sColumns[] = $sCol;
            $sPlaceholders[] = $sPh;
        }

        // check if the placeholders and columns are empty
        if (empty($sColumns) || count($sColumns) != count($sPlaceholders)) {
            throw new DatabaseException('Could not bind all parameters');
        }

        // build and execute the insert query
        $sColumns = "(" . implode(", ", $sColumns) . ")";
        $sPlaceholders = "(" . implode(", ", $sPlaceholders) . ")";
        $sql = "INSERT INTO
					{$sTableName}
					{$sColumns}
				VALUES
					{$sPlaceholders}";

        $iRowsAffected = $this->query($sql, $sBindParams);

        // return the affected rows or the id of the inserted row
        switch ($iReturn) {
            case self::RETURN_LAST_ID:
                return ($iRowsAffected) ? $this->lastInsertId($sTableName) : false;
            case self::RETURN_BOTH:
                return array(
                    "lastInsertId" => ($iRowsAffected) ? $this->lastInsertId($sTableName) : false,
                    "affectedRows" => $iRowsAffected
                );
            default:
                return $iRowsAffected;
        }
    }

    /**
     * Updates a row
     * Returns either the number of affected rows or false if there was no change
     *
     * @param string      $sTableName
     * @param array       $sBindParams DEF: array(); array of $k => $v pairs
     * @param string|bool $sWhere      DEF: false
     *
     * @todo: Additional sanitizing of sSets and Where required
     *
     * @return int
     * @throws DatabaseException
     */
    public function update($sTableName, $sBindParams, $sWhere = false)
    {
        // check if table is ok
        $sTableName = trim($sTableName);
        $sWhere = trim($sWhere);
        if (empty($sTableName)) {
            throw new DatabaseException('Table name is not a valid string. Must be non-empty.');
        }

        // build set statements
        $sSets = array();
        foreach ($this->quotePlaceholders($sBindParams) as $sCol => $sPh) {
            $sSets[] = "{$sCol} = {$sPh}";
        }

        // build and execute the update query
        $sSets = implode(", ", $sSets);
        $sql = "UPDATE
					{$sTableName}
				SET
					{$sSets}";
        $sql .= (!empty($sWhere)) ? " WHERE {$sWhere}" : "";

        return $this->query($sql, $sBindParams);
    }

    /**
     * Returns the id of the row last inserted in the database or the given table
     *
     * @param string $sTable DEF null;
     *
     * @return int
     */
    public function lastInsertId($sTable = null)
    {
        $this->openConnection();

        return $this->oDbHandle->lastInsertId($sTable);
    }

    /**
     * Deletes rows from the table that match the where conditions
     *
     * @param string      $sTableName
     * @param string|bool $sWhere DEF: false
     *
     * @todo: Additional sanitizing of Where required
     *
     * @return int
     * @throws DatabaseException
     */
    public function delete($sTableName, $sWhere = false)
    {
        // check if table is ok
        $sTableName = trim($sTableName);
        $sWhere = trim($sWhere);
        if (empty($sTableName)) {
            throw new DatabaseException('Table name is not a valid string. Must be non-empty.');
        }

        // build and execute the delete query
        $sql = "DELETE FROM
					{$sTableName}";
        $sql .= (!empty($sWhere)) ? " WHERE {$sWhere}" : "";

        return $this->query($sql);
    }

    //


    /**
     * Quotes a single string
     *
     * @param string $sValue
     *
     * @return string
     * @throws DatabaseException
     */
    protected function quoteString($sValue)
    {
        if (!(is_scalar($sValue) || is_null($sValue))) {
            throw new DatabaseException('The quoted value needs to be a scalar');
        }

        // if integer return unquoted
        if (is_int($sValue)) {
            return (int) $sValue;
        }

        // if null return NULL
        if (is_null($sValue)) {
            return "NULL";
        }

        $this->openConnection();

        return $this->oDbHandle->quote($sValue);
    }

    /**
     * Builds an placeholders from the given  array
     *
     * @param array $aValues
     *
     * @return array
     */
    protected function quotePlaceholders(array $aValues)
    {
        // return the placeholder array
        foreach ($aValues as $sKey => &$sValue) {
            $sValue = ":{$sKey}";
        }

        return $aValues;
    }

    /**
     * Quotes a single string or an array of strings.
     * If an array was given it returns an array, otherwise it returns a string
     *
     * @param string|array $mValues
     *
     * @return string|array
     */
    public function quote($mValues)
    {
        // return the quoted string or array
        if (is_array($mValues)) {
            // quote each value
            foreach ($mValues as &$mValue) {
                if (is_array($mValue)) {
                    $mValue = $this->quoteSqlIn($mValue);
                } else {
                    $mValue = $this->quoteString($mValue);
                }
            }

            return $mValues;
        } else {
            return $this->quoteString($mValues);
        }
    }

    /**
     * Turns an array or single value into an sql IN statement
     * e.g. array(1, 3, 'apple', 'pie', 8) will return "(1, 3, 'apple', 'pie', 8)"
     *
     * @param array $aValues
     *
     * @return string
     */
    public function quoteSqlIn(array $aValues)
    {
        foreach ($aValues as &$sValue) {
            $sValue = $this->quoteString($sValue);
        }

        // return the sql IN array
        return "(" . implode(", ", $aValues) . ")";
    }

    //

}
