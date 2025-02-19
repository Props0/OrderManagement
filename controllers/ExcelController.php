<?php
include_once 'CommonController.php';
require  'EmailController.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
class ExcelController extends CommonController
{
    public function __construct()
	{
        require '../vendor/autoload.php';
		parent::__construct();
	}
    public function generateReport() {
        $excelFilePath = '../example.xlsx';
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
        $excelFilePath = '../example.xlsx';
        $debug="";
        try {
            if (file_exists($excelFilePath)) {
                $spreadsheet = IOFactory::load($excelFilePath);
            } else {
                $spreadsheet = new Spreadsheet();
            }
            $debug.="Loaded excel file\n";
            $row = 2;
            $data = $this->generateReport();
            $data = json_decode($data, true);
            $sheet = $spreadsheet->getActiveSheet();
            $debug.="Loaded all data\n";
            foreach ($orders as $order) {
                $debug.="Reading order\n";
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
            $debug.="Write all data\n";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="relatorio.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            $debug.="Download File\n";
            echo json_encode(['message' => 'data escritos com sucesso!']);

        } catch (Exception $e) {

            echo json_encode(['error' => $debug . $e->getMessage()]);
        }
    }

    public function createNewFile() {
        $newFilePath = '../temp_' . uniqid() . '.xlsx';
        $excelFilePath = '../example.xlsx';
        if (!file_exists($excelFilePath)) {
            throw new Exception("O ficheiro base '{$excelFilePath}' não foi encontrado.");
        }

        if (!copy($excelFilePath, $newFilePath)) {
            throw new Exception("Falha ao criar a cópia do ficheiro base.");
        }

        return $newFilePath;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] != '') {
    $controller = new ExcelController();
    $params=(isset($_POST) && $_POST!=null) ? $_POST : ((isset($_GET) && $_GET!=null) ? $_GET : []);
    if($_SERVER['REQUEST_METHOD'] === 'POST' && $params==null){
        $params=json_decode(file_get_contents('php://input'),true);
    }
    $requesturl = $_SERVER['REQUEST_URI'];
    $parts = explode("/", $requesturl);
    $action_name = end($parts);
    call_user_func_array(array($controller, $action_name), $params);
}
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
header('Content-Type: application/json');