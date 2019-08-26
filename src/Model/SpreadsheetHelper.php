<?php

namespace App\Model;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpreadsheetHelper {

    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    /**
     * @var array
     */
    protected $worksheetRow;

    /**
     * SpreadsheetHelper constructor.
     *
     * @throws Exception
     */
    public function __construct() {
        $this->spreadsheet = $this->createSpreadsheet();
    }

    /**
     * Create blank Worksheet with specified name.
     *
     * @param string $name
     * @return Worksheet
     * @throws Exception
     */
    public function createWorksheet(string $name): Worksheet {
        $currentWorksheet = new Worksheet($this->spreadsheet, $name);
        $this->spreadsheet->addSheet($currentWorksheet);
        $this->worksheetRow[$name] = 1;

        return $currentWorksheet;
    }

    /**
     * Write default header into specified Worksheet.
     *
     * @param Worksheet $worksheet
     * @throws Exception
     */
    public function writeHeader(Worksheet $worksheet): void {
        $worksheet->setCellValue("A1", "Domain")
            ->setCellValue("B1", "Name")
            ->setCellValue("C1", "Type")
            ->setCellValue("D1", "Content");

        $worksheet->getCell("A1")->getStyle()->getFont()->setBold(true);
        $worksheet->getCell("B1")->getStyle()->getFont()->setBold(true);
        $worksheet->getCell("C1")->getStyle()->getFont()->setBold(true);
        $worksheet->getCell("D1")->getStyle()->getFont()->setBold(true);

        // Increase row number
        $this->worksheetRow[$worksheet->getTitle()]++;
    }

    /**
     * Write row into specified Worksheet.
     *
     * @param Worksheet $worksheet
     * @param mixed     ...$data
     */
    public function writeRow(Worksheet $worksheet, ...$data): void {
        for ($i = 0; $i < count($data); $i++) {
            $worksheet->setCellValueByColumnAndRow(
                $i + 1, $this->worksheetRow[$worksheet->getTitle()], $data[$i]
            );
        }

        // Increase row number
        $this->worksheetRow[$worksheet->getTitle()]++;
    }

    /**
     * Autosize columns in every Worksheet.
     */
    public function autoSize(): void {
        foreach ($this->spreadsheet->getAllSheets() as $worksheet) {
            foreach (range("A", "D") as $columnID) {
                $worksheet->getColumnDimension($columnID)->setAutoSize(true);
            }
        }
    }

    /**
     * Save Spreadsheet.
     *
     * @throws WriterException
     */
    public function saveSpreadsheet(): void {
        $fileName = __DIR__ . "/../../data/cloudflare-" . date("YmdHi") . ".xlsx";
        $directoryPath = dirname($fileName);

        // Create directory
        if (!file_exists($directoryPath) || !is_dir($directoryPath)) {
            mkdir($directoryPath, "0777", true);
        }

        // Create Writer
        $currentWriter = new Xlsx($this->spreadsheet);
        $currentWriter->save($fileName);
    }

    /**
     * Create Spreadsheet.
     *
     * @return Spreadsheet
     * @throws Exception
     */
    private function createSpreadsheet(): Spreadsheet {
        $currentSpreadsheet = new Spreadsheet();

        // Remove default Worksheet
        $currentSpreadsheet->removeSheetByIndex(
            $currentSpreadsheet->getIndex(
                $currentSpreadsheet->getSheetByName("Worksheet")
            )
        );

        return $currentSpreadsheet;
    }
}
