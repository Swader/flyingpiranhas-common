<?php

namespace flyingpiranhas\common\session\handlers;

use flyingpiranhas\common\database\interfaces\AdapterInterface;
use flyingpiranhas\common\session\interfaces\DbSessionHandlerInterface;
use flyingpiranhas\common\database\Registry;
use flyingpiranhas\common\session\exceptions\SessionException;
use flyingpiranhas\common\database\adapters\PgsqlAdapter;

/**
 * The PgsqlHandler is a session handler for session management through pgsql
 *
 * @todo Needs to be tested, did not try it out at all :-|
 *
 * @category       session
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-11-23
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
class PgsqlHandler implements DbSessionHandlerInterface
{

    /** @var AdapterInterface */
    protected $oAdapter;

    /** @var string */
    protected $sTableName;

    /** @var string */
    protected $sSchemaName;

    /**
     * Accepts an adapter (either a string which is a name of the adapter
     * to be fetched from the Database Registry or an instance of AdapterInterface)
     * and a table name in which to save session data, along with a schema name to
     * hold said table.
     *
     * @param string|AdapterInterface $mAdapter
     * @param string                  $sSchemaName
     * @param string                  $sTableName
     */
    public function __construct($mAdapter, $sSchemaName = 'fp', $sTableName = 'fp_sessions')
    {
        if ($mAdapter instanceof AdapterInterface) {
            $this->setAdapter($mAdapter);
        } else if (is_string($mAdapter)) {
            $this->setAdapter(Registry::getAdapter($mAdapter));
        }

        $this->setTableName($sTableName);
        $this->setSchemaName($sSchemaName);
    }

    /**
     * Sets the table name to be used for saving session data
     *
     * @param string $sTableName
     *
     * @return PgsqlHandler
     */
    public function setTableName($sTableName)
    {
        $this->sTableName = $sTableName;

        return $this;
    }

    /**
     * Sets the schema name to hold the table for saving session data
     *
     * @param string $sSchemaName
     *
     * @return PgsqlHandler
     */
    public function setSchemaName($sSchemaName)
    {
        $this->sSchemaName = $sSchemaName;

        return $this;
    }

    /**
     * Sets the database adapter
     *
     * @param AdapterInterface|string $oAdapter
     *
     * @return PgsqlHandler
     */
    public function setAdapter($oAdapter)
    {
        if (is_string($oAdapter)) {
            $oAdapter = Registry::getAdapter($oAdapter);
        }
        $this->oAdapter = $oAdapter;

        return $this;
    }

    /**
     * Returns the set persistence adapter
     *
     * @return PgsqlAdapter
     * @throws SessionException
     */
    public function getAdapter()
    {
        if ($this->oAdapter === null) {
            throw new SessionException('Adapter not set');
        }

        return $this->oAdapter;
    }

    /**
     * Session open method. No use in this kind of session handling
     */
    public function open($sSavePath, $sSessionName)
    {
        // Create session table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS
				{$this->sSchemaName}{$this->sTableName}
				(
					id character varying(32) COLLATE pg_catalog.\"C.UTF-8\" NOT NULL,
					data text COLLATE pg_catalog.\"C.UTF-8\",
					updated integer,
					CONSTRAINT \"PRIMARY_KEY\" PRIMARY KEY (id)
				)";

        $this->getAdapter()->query($sql);

        return true;
    }

    /**
     * Session close method. No use in this kind of session handling.
     */
    public function close()
    {
        // Do nothing
        return true;
    }

    /**
     * Fetches the data for the corresponding ID entry
     *
     * @param string $sSessionId
     *
     * @return string Serialized string, automatically unserialized by PHP
     */
    public function read($sSessionId)
    {
        $sql = "SELECT data
				FROM {$this->sSchemaName}{$this->sTableName}
				WHERE id = :id";
        $sResult = $this->getAdapter()->fetchOne($sql, array('id' => $sSessionId));
        if (!$sResult) {
            return '';
        } else {
            return $sResult;
        }
    }

    /**
     * Writes the session data into the database. If session data for the given session ID already exists, it is overwritten.
     *
     * @param string $sSessionId
     * @param string $sSessionData Serialized data
     *
     * @return bool
     */
    public function write($sSessionId, $sSessionData)
    {
        $sql = "SELECT
					id
				FROM
					{$this->sSchemaName}{$this->sTableName}
				WHERE
					id = :id";

        if ($this->getAdapter()->fetchOne($sql, array('id' => $sSessionId))) {
            $sql = "UPDATE
						{$this->sSchemaName}{$this->sTableName}
					SET
						data = :data,
						updated = ROUND(DATE_PART('epoch',NOW()))
					WHERE
						id = :id";
        } else {
            $sql = "INSERT INTO
					{$this->sSchemaName}{$this->sTableName}
					(id, data, updated)
					VALUES (:id, :data, ROUND(DATE_PART('epoch',NOW())))";
        }

        $oStmt = $this->getAdapter()->prepare($sql);

        return $oStmt->execute(array('id' => $sSessionId, 'data' => $sSessionData));
    }

    /**
     * Deletes a user's session from the database
     *
     * @param string $sSessionId
     *
     * @return bool
     */
    public function destroy($sSessionId)
    {
        $sql = "DELETE FROM {$this->sSchemaName}{$this->sTableName}
				WHERE id = :id";
        $oStatement = $this->getAdapter()->prepare($sql);

        return $oStatement->execute(array('id' => $sSessionId));
    }

    /**
     * Deletes all outdated sessions from the database
     *
     * @param int $iMaxLifetime Default timeout in seconds.
     * Can be fine tuned in configuration under sessionTimeout
     *
     * @return bool
     */
    public function gc($iMaxLifetime)
    {
        $iTimeout = constant('\flyingpiranhas\SESSION_TIMEOUT');
        $sql = "DELETE FROM {$this->sSchemaName}{$this->sTableName}
				WHERE (ROUND(DATE_PART('epoch',NOW())) - updated) > :timeout";
        $oStatement = $this->getAdapter()->prepare($sql);

        return $oStatement->execute(array('timeout' => (!$iTimeout) ? $iMaxLifetime : $iTimeout));
    }

}
