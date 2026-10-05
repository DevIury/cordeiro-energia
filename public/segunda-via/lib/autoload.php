<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    static $map = [
        'chillerlan\\QRCode\\' => __DIR__ . '/php-qrcode/src/',
        'chillerlan\\Settings\\' => __DIR__ . '/php-settings-container/src/',
    ];

    foreach ($map as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }
        $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});
