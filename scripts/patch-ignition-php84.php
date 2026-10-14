<?php

$path = dirname(__DIR__).'/vendor/spatie/laravel-ignition/src/Recorders/DumpRecorder/MultiDumpHandler.php';

if (! is_file($path)) {
    return;
}

$contents = file_get_contents($path);
$updated = str_replace(
    'public function addHandler(callable $callable = null): self',
    'public function addHandler(?callable $callable = null): self',
    $contents
);

if ($updated !== $contents) {
    file_put_contents($path, $updated);
}
