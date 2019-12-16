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
} elseif ($argv[1] === "find-untranslated") {
    $src_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src';
    $lang_path = $src_path . DIRECTORY_SEPARATOR . 'langs';

    $php_files = getFiles($src_path, array('php'));
    $html_files = getFiles($src_path, array('html'));
    $langs_files = getFiles($lang_path, array('json'));
    $langs_translations = array();

    foreach ($langs_files as $lang_file) {
        $translations = json_decode(file_get_contents($lang_file), true);

        $langs_translations[$lang_file] = $translations;
    }

    foreach ($php_files as $php_file) {
        $fn = fopen($php_file, 'r');
        $line_count = 1;

        while (!feof($fn))  {
            $line = fgets($fn);

            preg_match_all(
                "/I18n::getInstance\(\)->translate\('([A-Z0-9_]+)'\)/",
                $line,
                $matches,
                PREG_SET_ORDER
            );
            foreach($matches as $match) {
                $translation_key = $match[1];

                foreach ($langs_translations as $lang_file => $translations) {
                    if (!array_key_exists($translation_key, $translations)) {
                        echo "Key '$translation_key' has no translation in lang file $lang_file. In $php_file on Line $line_count\n";
                    }
                }
            }
            $line_count += 1;
        }
        fclose($fn);
    }

    foreach ($html_files as $html_file) {
        $fn = fopen($html_file, 'r');
        $line_count = 1;

        while (!feof($fn))  {
            $line = fgets($fn);

            preg_match_all(
                "/{{ *?translate *?\'([A-Z0-9_]*)\' *?}}/m",
                $line,
                $matches,
                PREG_SET_ORDER
            );
            foreach($matches as $match) {
                $translation_key = $match[1];

                foreach ($langs_translations as $lang_file => $translations) {
                    if (!array_key_exists($translation_key, $translations)) {
                        echo "Key '$translation_key' has no translation in lang file $lang_file. In $html_file on Line $line_count\n";
                    }
                }
            }
            $line_count += 1;
        }
        fclose($fn);
    }

} else {
    echo "Error: Unknow command !";
}

function getFiles($dir, $whitelist=NULL) {
    $result = array();
    $cdir = scandir($dir);

    foreach ($cdir as $file) {
        if (!in_array($file, array('.', '..'))) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
                $files = getFiles($dir . DIRECTORY_SEPARATOR . $file, $whitelist);
                if (count($files) > 0)
                    $result = array_merge($result, $files);
            } elseif($whitelist === NULL || in_array(strtolower(pathinfo($dir.DIRECTORY_SEPARATOR.$file, PATHINFO_EXTENSION)), $whitelist)) {
                $result[] = $dir . DIRECTORY_SEPARATOR . $file;
            }
        }
    }

    return $result;
}
