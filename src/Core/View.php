<?php
namespace Core;

class View
{
    public function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $templatePath = __DIR__ . '/../../templates/' . $template . '.php';
        if (!file_exists($templatePath)) {
            throw new \RuntimeException('Template not found: ' . $templatePath);
        }
        include $templatePath;
    }
}
