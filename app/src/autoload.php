<?php
spl_autoload_register(static function (string $className): void {
    $filename = sprintf('../src/%s.php', str_replace('\\', '/', $className));
    if (file_exists($filename)) {
        include($filename);
    }
});
