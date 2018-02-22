<?php


namespace ellsif\WelCMS;

// TODO 修正必要
class CsvPrinter extends Printer
{
    /**
     * CSVを表示する。
     */
    public function print(ServiceResult $result = null)
    {
        if ($result->isError()) {
            http_response_code(500);
            exit;
        }
        $resultData = $result->resultData();
        $fileName = $resultData['fileName'] ?? 'sample';
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=${fileName}.csv");
        header("Content-Transfer-Encoding: binary");

        $output = fopen('php://output', 'w');
        $indent = [];
        $recursive = function($out, $data) use(&$recursive, &$indent) {
            if (\ellsif\isArray($data)) { // 連番の配列
                foreach($data as $row) {
                    if (\ellsif\isObjectArray($row)) {
                        $indent[] = '';
                        $recursive($out, $row);
                    } elseif(is_array($row)) {
                        fputcsv($out, array_merge($indent, $row));
                    } else {
                        fputcsv($out, array_merge($indent, [$row]));
                    }
                }
            } elseif (is_array($data)) {  // 連想配列
                foreach($data as $key => $row) {
                    if (\ellsif\isObjectArray($row)) {
                        fputcsv($out, array_merge($indent, [$key]));
                        $indent[] = '';
                        $recursive($out, array_merge($indent, $row));
                    } elseif(\ellsif\isArray($row)) {
                        fputcsv($out, array_merge($indent, $row));
                    } elseif (is_object($row)) {
                        $indent[] = '';
                        $recursive($out, $row);
                    } else {
                        fputcsv($out, array_merge($indent, [$key, $row]));
                    }
                }
            } elseif (is_object($data)) {
                $recursive($out, json_decode(json_encode($data), true));
            }
        };
        $recursive($output, $resultData);
        fclose($output);
    }
}