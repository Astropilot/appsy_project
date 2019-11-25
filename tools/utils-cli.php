<?php

if ($argc != 2) {
    echo "Usage: " . $argv[0] . " <command>";
    exit;
}

if ($argv[1] === "clear-cache") {
    $cache_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cache';
    $cache_files = preg_grep('~\.(php|chtml)$~', scandir($cache_path));

    foreach ($cache_files as $cache_file) {
        unlink($cache_path . DIRECTORY_SEPARATOR . $cache_file);
    }

    echo "Cache cleared !";
} else {
    echo "Error: Unknow command !";
}
