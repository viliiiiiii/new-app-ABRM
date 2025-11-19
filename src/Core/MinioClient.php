<?php
namespace Core;

class MinioClient
{
    public function upload(string $path, string $content): string
    {
        $storageDir = __DIR__ . '/../../storage/temp/minio';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0775, true);
        }
        $filename = $storageDir . '/' . basename($path);
        file_put_contents($filename, $content);
        return $path;
    }
}
