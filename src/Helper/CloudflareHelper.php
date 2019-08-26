<?php

namespace App\Helper;

use App\Exception\CloudflareException;
use App\Model\DNSRecord;
use App\Model\Zone;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

class CloudflareHelper {

    /**
     * @var string
     */
    protected $cloudflareBaseURI = "https://api.cloudflare.com/client/v4/";

    /**
     * Get Cloudflare Zones.
     *
     * @return Zone[]
     * @throws CloudflareException
     */
    public function getCloudflareZones(): array {

        // Get Request content
        $responseResult = $this->sendRequest("zones");

        // Define response collection
        $toResponse = [];
        if (is_array($responseResult)) {
            foreach ($responseResult as $zoneItem) {
                if (null !== ($currentZone = $this->parseZone($zoneItem))) {
                    $toResponse[] = $currentZone;
                }
            }
        }

        return $toResponse;
    }

    /**
     * Get DNS Records for specified Zone.
     *
     * @param Zone $zone
     * @return array
     * @throws CloudflareException
     */
    public function getCloudflareDNSRecords(Zone $zone) {

        // Get Request content
        $responseResult = $this->sendRequest("zones/" . $zone->getZoneID() . "/dns_records");

        // Define response collection
        $toResponse = [];
        if (is_array($responseResult)) {
            foreach ($responseResult as $recordItem) {
                if (null !== ($currentRecord = $this->parseDNSRecord($recordItem))) {
                    $toResponse[] = $currentRecord;
                }
            }
        }

        return $toResponse;
    }

    /**
     * Read configuration file.
     *
     * @return array|null
     */
    private function readConfig(): ?array {
        $configPath = __DIR__ . "/../../config/cloudflare.json";

        if (file_exists($configPath) && is_file($configPath) && is_readable($configPath)) {
            $configContent = file_get_contents($configPath);

            return json_decode($configContent, true);
        }

        return null;
    }

    /**
     * Get HTTP Client.
     *
     * @param string $authToken
     * @return Client
     */
    private function getClient(string $authToken): Client {
        return new Client([
            "base_uri"        => $this->cloudflareBaseURI,
            "connect_timeout" => 10,
            "timeout"         => 60,
            "headers"         => [
                "Content-Type"  => "application/json",
                "Authorization" => "Bearer " . $authToken
            ]
        ]);
    }

    /**
     * Send specified Request.
     *
     * @param string $requestURI
     * @param int    $page
     * @param int    $perPage
     * @return array
     * @throws CloudflareException
     */
    private function sendRequest(string $requestURI, int $page = 1, int $perPage = 10): array {
        if (null !== ($configContent = $this->readConfig())) {
            if (is_array($configContent) && array_key_exists("authToken", $configContent)) {
                $authToken = $configContent["authToken"];

                // Define Request
                $currentRequest = new Request("GET", sprintf("%s?page=%d&per_page=%d", $requestURI, $page, $perPage));

                try {
                    if (null !== ($currentResponse = $this->getClient($authToken)->send($currentRequest))) {
                        if (200 === $currentResponse->getStatusCode()) {
                            $responseContent = $currentResponse->getBody()->getContents();

                            if (null !== ($responseContent = json_decode($responseContent, true))) {
                                if (is_array($responseContent) && array_key_exists("result", $responseContent) && array_key_exists("result_info", $responseContent)) {
                                    $resultPage = $responseContent["result_info"]["page"];
                                    $resultTotalPages = $responseContent["result_info"]["total_pages"];

                                    // Get Result
                                    $responseData = $responseContent["result"];

                                    // Check if we need to paginate
                                    if ($resultPage < $resultTotalPages) {
                                        $responseData = array_merge($responseData, $this->sendRequest($requestURI, ++$page, $perPage));
                                    }

                                    return $responseData;
                                }
                            }
                        }
                    }
                } catch (GuzzleException $exception) {
                    throw new CloudflareException($exception->getMessage());
                }
            }
        }

        throw new CloudflareException("Missing or incorrect configuration for Cloudflare API");
    }

    /**
     * Parse Zone from specified data.
     *
     * @param array $zoneData
     * @return Zone|null
     */
    private function parseZone(array $zoneData): ?Zone {
        if (
            array_key_exists("id", $zoneData) &&
            array_key_exists("name", $zoneData) &&
            array_key_exists("status", $zoneData)
        ) {
            $currentZone = new Zone();
            $currentZone->setZoneID($zoneData["id"]);
            $currentZone->setName($zoneData["name"]);
            $currentZone->setStatus($zoneData["status"]);

            return $currentZone;
        }

        return null;
    }

    /**
     * Parse DNS Record from specified data.
     *
     * @param array $recordData
     * @return DNSRecord|null
     */
    private function parseDNSRecord(array $recordData): ?DNSRecord {
        if (
            array_key_exists("id", $recordData) &&
            array_key_exists("type", $recordData) &&
            array_key_exists("name", $recordData) &&
            array_key_exists("content", $recordData)
        ) {
            $currentRecord = new DNSRecord();
            $currentRecord->setRecordID($recordData["id"]);
            $currentRecord->setType($recordData["type"]);
            $currentRecord->setName($recordData["name"]);
            $currentRecord->setContent($recordData["content"]);

            return $currentRecord;
        }

        return null;
    }
}
