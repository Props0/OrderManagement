<?php
require 'vendor/autoload.php'; // Certifica-te de que instalas a biblioteca PhpSpreadsheet via Composer

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelController {
    public function generateReport() {
        $excelFilePath = 'example.xlsx';
        try {
            $spreadsheet = IOFactory::load($excelFilePath);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();
            return $data;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function writeToExcel($orders) {
        $excelFilePath = 'example.xlsx';
        try {
            if (file_exists($excelFilePath)) {
                $spreadsheet = IOFactory::load($excelFilePath);
            } else {
                $spreadsheet = new Spreadsheet();
            }

            $sheet = $spreadsheet->getActiveSheet();
            $row = 2;
            foreach ($orders as $order) {
                $col = 1;
                foreach ($order as $order_props) {
                    $columnLetter = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++);
                    $sheet->setCellValue($columnLetter . $row, $order_props);
                }
                $row++;
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($this->createNewFile());

            return true;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function createNewFile() {
        $newFilePath = 'temp_' . uniqid() . '.xlsx';
        $excelFilePath = 'example.xlsx';
        if (!file_exists($excelFilePath)) {
            throw new Exception("O ficheiro base '{$excelFilePath}' não foi encontrado.");
        }

        if (!copy($excelFilePath, $newFilePath)) {
            throw new Exception("Falha ao criar a cópia do ficheiro base.");
        }

        return $newFilePath;
    }
}
?>