<?php

declare(strict_types=1);

namespace SimpleSAML\TestUtils;

use SimpleSAML\Configuration;
use SimpleSAML\Metadata\MetaDataStorageHandler;
use SimpleSAML\Session;
use SimpleSAML\Store\StoreFactory;

/**
 * A helper class to aid in clearing global state that is set during SSP tests
 */
class StateClearer
{
    /**
     * Global state to restore between test runs
     * @var array
     */
    private $backups = [];

    /**
     * Class that implement \SimpleSAML\Utils\ClearableState and should have clearInternalState called between tests
     * @var array
     */
    private $clearableState = [
        Configuration::class,
        MetaDataStorageHandler::class,
        Session::class,
        StoreFactory::class,
    ];

    /**
     * Environmental variables to unset
     * @var array
     */
    private $vars_to_unset = ['SIMPLESAMLPHP_CONFIG_DIR'];


    /**
     * @return void
     */
    public function backupGlobals()
    {
        // Backup any state that is needed as part of processing, so we can restore it later.
        // TODO: phpunit's backupGlobals = false, yet we are trying to do a similar thing here. Is that an issue?
        $this->backups['$_COOKIE'] = $_COOKIE;
        $this->backups['$_ENV'] = $_ENV;
        $this->backups['$_FILES'] = $_FILES;
        $this->backups['$_GET'] = $_GET;
        $this->backups['$_POST'] = $_POST;
        $this->backups['$_SERVER'] = $_SERVER;
        /** @psalm-suppress RedundantCondition */
        $this->backups['$_SESSION'] = isset($_SESSION) ? $_SESSION : [];
        $this->backups['$_REQUEST'] = $_REQUEST;
    }


    /**
     * Clear any global state.
     * @return void
     */
    public function clearGlobals()
    {
        if (!empty($this->backups)) {
            $_COOKIE = $this->backups['$_COOKIE'];
            $_ENV = $this->backups['$_ENV'];
            $_FILES = $this->backups['$_FILES'];
            $_GET = $this->backups['$_GET'];
            $_POST = $this->backups['$_POST'];
            $_SERVER = $this->backups['$_SERVER'];
            $_SESSION = $this->backups['$_SESSION'];
            $_REQUEST = $this->backups['$_REQUEST'];
        } else {
            //TODO: what should this behavior be?
        }
    }


    /**
     * Clear any SSP specific state, such as SSP enviormental variables or cached internals.
     * @return void
     */
    public function clearSSPState()
    {
        foreach ($this->clearableState as $var) {
            $var::clearInternalState();
        }

        foreach ($this->vars_to_unset as $var) {
            putenv($var);
        }
    }
}
