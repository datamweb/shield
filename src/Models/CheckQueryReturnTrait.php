<?php

namespace CodeIgniter\Shield\Models;

use ReflectionObject;
use ReflectionProperty;

trait CheckQueryReturnTrait
{
    private ?bool $currentDBDebug = null;

    private function checkQueryReturn(bool $return): void
    {
        $this->restoreDBDebug();

        if ($return === false) {
            $error   = $this->db->error();
            $message = 'Query error: ' . $error['code'] . ', '
                . $error['message'] . ', query: ' . $this->db->getLastQuery();

            throw new DatabaseException($message, $error['code']);
        }
    }

    private function disableDBDebug(): void
    {
        if (! $this->db->DBDebug) {
            // `DBDebug` is false. Do nothing.
            return;
        }

        $this->currentDBDebug = $this->db->DBDebug;

        $propertyDBDebug = $this->getPropertyDBDebug();
        $propertyDBDebug->setValue($this->db, false);
    }

    private function restoreDBDebug(): void
    {
        if ($this->currentDBDebug === null) {
            // `DBDebug` has not been changed. Do nothing.
            return;
        }

        $propertyDBDebug = $this->getPropertyDBDebug();
        $propertyDBDebug->setValue($this->db, $this->currentDBDebug);

        $this->currentDBDebug = null;
    }

    private function getPropertyDBDebug(): ReflectionProperty
    {
        $refClass    = new ReflectionObject($this->db);
        $refProperty = $refClass->getProperty('DBDebug');
        $refProperty->setAccessible(true);

        return $refProperty;
    }
}
