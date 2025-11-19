<?php
namespace Core;

class ExportPdf
{
    public static function fromHtml(string $html, string $filename): void
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=' . $filename);
        echo "%PDF-1.1\n% placeholder pdf generated from html\n" . strip_tags($html);
    }
}
