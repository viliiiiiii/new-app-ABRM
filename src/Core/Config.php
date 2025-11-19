<?php
namespace Core;

class Config
{
    private static array $config;

    public static function load(): void
    {
        if (!isset(self::$config)) {
            $path = __DIR__ . '/../../config/config.php';
            if (!file_exists($path)) {
                throw new \RuntimeException('Missing config file');
            }
            self::$config = require $path;
        }
    }

    public static function get(string $key, $default = null)
    {
        self::load();
        $segments = explode('.', $key);
        $value = self::$config;
        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}
