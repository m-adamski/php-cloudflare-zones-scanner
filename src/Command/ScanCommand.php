<?php

namespace App\Command;

use App\Helper\CloudflareHelper;
use App\Model\SpreadsheetHelper;
use App\Model\SymfonyStyle;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScanCommand extends Command {

    /**
     * @var CloudflareHelper
     */
    protected $cloudflareHelper;

    /**
     * @var SpreadsheetHelper
     */
    protected $spreadsheetHelper;

    /**
     * ScanCommand constructor.
     *
     * @param CloudflareHelper  $cloudflareHelper
     * @param SpreadsheetHelper $spreadsheetHelper
     */
    public function __construct(CloudflareHelper $cloudflareHelper, SpreadsheetHelper $spreadsheetHelper) {
        parent::__construct();

        $this->cloudflareHelper = $cloudflareHelper;
        $this->spreadsheetHelper = $spreadsheetHelper;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName("scan")
            ->setDescription("Scan Cloudflare Zones and DNS Records");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int {
        $io = new SymfonyStyle($input, $output);
        $io->newLine();

        try {

            // Generate collection of the Cloudflare Zones
            $io->taskStart("Generating a list of the Cloudflare zones");
            $zoneCollection = $this->cloudflareHelper->getCloudflareZones();
            $io->taskEnd();

            // Create summary Worksheet
            $summaryWorksheet = $this->spreadsheetHelper->createWorksheet("Summary");
            $this->spreadsheetHelper->writeHeader($summaryWorksheet);

            // Generate collection of the DNS Records assigned to each Zone
            foreach ($zoneCollection as $currentZone) {
                $zoneName = $currentZone->getName();

                // Create blank Worksheet
                $currentSheet = $this->spreadsheetHelper->createWorksheet($zoneName);
                $this->spreadsheetHelper->writeHeader($currentSheet);

                // Create collection of the DNS Records
                $io->taskStart("Collecting DNS Records for " . $currentZone->getName());
                $recordsCollection = $this->cloudflareHelper->getCloudflareDNSRecords($currentZone);

                // Write found data into Worksheet
                for ($i = 0; $i < count($recordsCollection); $i++) {
                    $currentRecord = $recordsCollection[$i];

                    // Write row
                    $this->spreadsheetHelper->writeRow($currentSheet, $zoneName, $currentRecord->getName(), $currentRecord->getType(), $currentRecord->getContent());
                    $this->spreadsheetHelper->writeRow($summaryWorksheet, $zoneName, $currentRecord->getName(), $currentRecord->getType(), $currentRecord->getContent());
                }

                $io->taskEnd();
            }

            $io->taskStart("Saving the statement");
            $this->spreadsheetHelper->autoSize();
            $this->spreadsheetHelper->saveSpreadsheet();
            $io->taskEnd();
        } catch (Exception $exception) {
            $io->taskError();
            $io->writeError($exception->getMessage());
        }

        return null;
    }
}
