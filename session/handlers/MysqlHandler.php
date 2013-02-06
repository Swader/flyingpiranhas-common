<?php

namespace flyingpiranhas\common\session\handlers;

use flyingpiranhas\common\database\interfaces\AdapterInterface;
use flyingpiranhas\common\session\interfaces\DbSessionHandlerInterface;
use flyingpiranhas\common\database\Registry;
use flyingpiranhas\common\session\exceptions\SessionException;
use flyingpiranhas\common\database\adapters\MysqlAdapter;

/**
 * The MysqlHandler is a session handler for session management through mysql
 *
 * @category       session
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-11-23
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
class MysqlHandler implements DbSessionHandlerInterface
{

    /** @var AdapterInterface */
    protected $oAdapter;

    /** @var string */
    protected $sTableName;

    /**
     * Accepts an adapter (either a string which is a name of the adapter
     * to be fetched from the Database Registry or an instance of AdapterInterface)
     * and a table name in which to save session data.
     *
     * @param string|AdapterInterface $mAdapter
     * @param string                  $sTableName
     */
    public function __construct($mAdapter, $sTableName = 'fp_sessions')
    {
        if ($mAdapter instanceof AdapterInterface) {
            $this->setAdapter($mAdapter);
        } else if (is_string($mAdapter)) {
            $this->setAdapter(Registry::getAdapter($mAdapter));
        }

        $this->setTableName($sTableName);
    }

    /**
     * Sets the table name to be used for saving session data
     *
     * @param string $sTableName
     *
     * @return MysqlHandler
     */
    public function setTableName($sTableName)
    {
        $this->sTableName = $sTableName;

        return $this;
    }

    /**
     * Sets the database adapter
     *
     * @param AdapterInterface|string $oAdapter
     *
     * @return MysqlHandler
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
     * @return MysqlAdapter
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
     * Session open method. If the sessions table does not exist, it is automatically created
     */
    public function open($sSavePath, $sSessionName)
    {

        // Create session table if it doesn't exist
        $query = '
			CREATE TABLE IF NOT EXISTS ' . $this->sTableName . ' (
				`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
				`data` text NOT NULL,
				`updated` bigint(20) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			';

        $this->getAdapter()->query($query);

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
        $sQuery  = ' SELECT data FROM ' . $this->sTableName . ' WHERE id = :id ';
        $sResult = $this->getAdapter()->fetchOne($sQuery, array('id' => $sSessionId));
        if (!$sResult) {
            return '';
        } else {
            return $sResult;
        }
    }

    /**
     * Writes the session data into the database.
     * If session data for the given session ID already exists, it is overwritten.
     *
     * @param string $sSessionId
     * @param string $sSessionData Serialized data
     *
     * @return bool
     */
    public function write($sSessionId, $sSessionData)
    {
        $sQuery     = '
            INSERT INTO ' . $this->sTableName . ' (id, data, updated)
            VALUES (:id, :data, UNIX_TIMESTAMP())
            ON DUPLICATE KEY UPDATE data = :data, updated = UNIX_TIMESTAMP() ';
        $oStatement = $this->getAdapter()->prepare($sQuery);

        return $oStatement->execute(array('id' => $sSessionId, 'data' => $sSessionData));
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
        $sQuery     = ' DELETE FROM ' . $this->sTableName . ' WHERE id = :id ';
        $oStatement = $this->getAdapter()->prepare($sQuery);

        return $oStatement->execute(array('id' => $sSessionId));
    }

    /**
     * Deletes all outdated sessions from the database
     *
     * @param int $iMaxLifetime Default timeout in seconds. Can be fine tuned in configuration under sessionTimeout
     *
     * @return bool
     */
    public function gc($iMaxLifetime)
    {
        $iTimeout   = constant('\flyingpiranhas\SESSION_TIMEOUT');
        $sQuery     = ' DELETE FROM ' . $this->sTableName . ' WHERE (UNIX_TIMESTAMP() - updated) > :timeout ';
        $oStatement = $this->getAdapter()->prepare($sQuery);

        return $oStatement->execute(array('timeout' => (!$iTimeout) ? $iMaxLifetime : $iTimeout));
    }

}
