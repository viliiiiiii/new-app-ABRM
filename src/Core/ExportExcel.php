<?php
namespace Core;

class ExportExcel
{
    public static function fromArray(array $rows, string $filename): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $filename);
        echo "Generated Excel\n";
        foreach ($rows as $row) {
            echo implode("\t", $row) . "\n";
        }
    }
}
