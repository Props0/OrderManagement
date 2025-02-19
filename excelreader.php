<?php
require 'vendor/autoload.php'; // Certifica-te de que instalas a biblioteca PhpSpreadsheet via Composer

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ExcelController {
    public function generateReport() {
        $excelFilePath = 'example.xlsx';
        try {
            $spreadsheet = IOFactory::load($excelFilePath);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();
            $campos = $data[0];
            $valores = $data[1];
            $uniqueKeys = [];
            foreach ($data[0] as $key) {
                $uniqueKey = $key;
                $i = 1;
                while (in_array($uniqueKey, $uniqueKeys)) {
                    $uniqueKey = $key . '_' . $i;
                    $i++;
                }
                $uniqueKeys[] = $uniqueKey;
            }

            $resultado = ['data' => array_combine($uniqueKeys, $data[1])];
            return json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
          
            $row = 2;
            $data = $this->generateReport();
            $data = json_decode($data, true);
            $sheet = $spreadsheet->getActiveSheet();
            foreach ($orders["order"] as $order) {
                if (isset($data['data'])) {
                    $order = array_merge($data['data'], $order);
                }
                $col = 1;
                foreach ($order as $order_props) {
                    $columnLetter = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++);
                    $sheet->setCellValueExplicit($columnLetter . $row, $order_props, DataType::TYPE_STRING);
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