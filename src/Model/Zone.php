<?php

namespace App\Model;

class Zone {

    /**
     * @var string
     */
    protected $zoneID;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $status;

    /**
     * @return string
     */
    public function getZoneID(): string {
        return $this->zoneID;
    }

    /**
     * @param string $zoneID
     */
    public function setZoneID(string $zoneID): void {
        $this->zoneID = $zoneID;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getStatus(): string {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void {
        $this->status = $status;
    }
}
