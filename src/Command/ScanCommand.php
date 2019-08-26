<?php

namespace App\Command;

use App\Exception\CloudflareException;
use App\Helper\CloudflareHelper;
use App\Model\DNSRecord;
use App\Model\SymfonyStyle;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScanCommand extends Command {

    /**
     * @var CloudflareHelper
     */
    protected $cloudflareHelper;

    /**
     * ScanCommand constructor.
     *
     * @param CloudflareHelper $cloudflareHelper
     */
    public function __construct(CloudflareHelper $cloudflareHelper) {
        parent::__construct();

        $this->cloudflareHelper = $cloudflareHelper;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure() {
        $this
            ->setName("cloudflare-scan")
            ->setDescription("Scan Cloudflare Zones and DNS Records");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $io->newLine();

        try {

            // Create Spreadsheet
            $currentSpreadsheet = new Spreadsheet();

            // Remove default Worksheet
            $currentSpreadsheet->removeSheetByIndex(
                $currentSpreadsheet->getIndex(
                    $currentSpreadsheet->getSheetByName("Worksheet")
                )
            );

            // Generate collection of the Cloudflare Zones
            $io->taskStart("Generating a list of the Cloudflare zones");
            $zoneCollection = $this->cloudflareHelper->getCloudflareZones();
            $io->taskEnd();

            // Generate collection of the DNS Records assigned to each Zone
            foreach ($zoneCollection as $currentZone) {
                $zoneName = $currentZone->getName();

                // Create blank Worksheet
                $currentSheet = new Worksheet($currentSpreadsheet, $zoneName);
                $currentSheet->setCellValue("A1", "Name");
                $currentSheet->setCellValue("B1", "Type");
                $currentSheet->setCellValue("C1", "Content");

                // Create collection of the DNS Records
                $io->taskStart("Collecting DNS Records for " . $currentZone->getName());
                $recordsCollection = $this->cloudflareHelper->getCloudflareDNSRecords($currentZone);

                // Write found data into Worksheet
                for ($i = 0; $i < count($recordsCollection); $i++) {
                    $rowNumber = $i + 2;

                    /** @var DNSRecord $currentRecord */
                    $currentRecord = $recordsCollection[$i];

                    $currentSheet->setCellValueByColumnAndRow(1, $rowNumber, $currentRecord->getName());
                    $currentSheet->setCellValueByColumnAndRow(2, $rowNumber, $currentRecord->getType());
                    $currentSheet->setCellValueByColumnAndRow(3, $rowNumber, $currentRecord->getContent());
                }

                // Add created Worksheet
                $currentSpreadsheet->addSheet($currentSheet);
                $io->taskEnd();
            }

            $io->taskStart("Saving the statement");

            // Create data directory
            if (!file_exists(__DIR__ . "/../../data")) {
                mkdir(__DIR__ . "/../../data");
            }

            // Save generated Excel file
            $currentWriter = new Xlsx($currentSpreadsheet);
            $currentWriter->save(__DIR__ . "/../../data/report-" . date("YmdHi") . ".xlsx");

            $io->taskEnd();
        } catch (Exception $exception) {
            $io->taskError();
            $io->writeError($exception->getMessage());
        }

        return;
    }
}
