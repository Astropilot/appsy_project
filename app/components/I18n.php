<?php

namespace Testify\Component;

class I18n {

    private static $_instance = null;
    private $langsFolder = '';
    private $defaultLang = '';
    private $cacheID = '';
    public $notCached = false;
    public $active = false;

    private function __construct($langsFolder, $default) {
        if ($default !== null)
            $this->active = true;
        $this->defaultLang = $default;
        $this->langsFolder = $langsFolder;
    }

    public function setLangFromURL(string $url): string {
        if (!$this->active)
            return $url;

        $url = ltrim($url, '/');

        if (strlen($url) === 2 || (strlen($url) > 2 && $url[2] === '/')) { // On a une langue dans l'URL
            $lang = substr($url, 0, 2);
            $url_without_lang = substr($url, 2);

            $_SESSION['lang'] = $lang;
            $this->cacheTranslationFile($lang);
            if ($url_without_lang === false || $url_without_lang === '')
                return '/';
            return $url_without_lang;
        } else {
            if (!isset($_SESSION['lang'])) {
                $path_file = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'langs' . DIRECTORY_SEPARATOR;
                $langs = array();

                preg_match_all(
                    '~([\w-]+)(?:[^,\d]+([\d.]+))?~',
                    strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]),
                    $matches,
                    PREG_SET_ORDER
                );
                foreach($matches as $match) {
                    list($a, $b) = explode('-', $match[1]) + array('', '');
                    $value = isset($match[2]) ? (float) $match[2] : 1.0;

                    if (file_exists($path_file . $match[1] . '.json')) {
                        $langs[$match[1]] = $value;
                        continue;
                    }
                    if (file_exists($path_file . $a . '.json'))
                        $langs[$a] = $value - 0.1;
                }
                if ($langs) {
                    arsort($langs);
                    $_SESSION['lang'] = key($langs);
                } else
                    $_SESSION['lang'] = $this->defaultLang;
            }
            $this->cacheTranslationFile($_SESSION['lang']);
        }
        if ($url === '')
            return '/';
        return '/' . $url;
    }

    public function translate(string $key, $lang=null): string {
        if (!$this->active)
            return 'TRANSLATION_NOT_INITIALIZED';

        if ($lang === null)
            $lang = $_SESSION['lang'];
        else
            $this->cacheTranslationFile($lang);

        $path_cache = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        $cache_file = $path_cache. $lang . '_' . $this->getCacheID($lang) . '.php';
        require($cache_file);

        $translation = 'TRANSLATION_NOT_FOUND';
        if (property_exists($strings, $key))
            $translation = $strings->{$key};

        unset($strings);
        return $translation;
    }

    public function computeTranslations(string $file_contents, &$context, $lang=null): string {
        if (!$this->active)
            return $file_contents;

        if ($lang === null)
            $lang = $_SESSION['lang'];
        else
            $this->cacheTranslationFile($lang);
        $path_cache = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        $cache_file = $path_cache. $lang . '_' . $this->getCacheID($lang) . '.php';
        require($cache_file);

        while (preg_match('/{{ *?translate *?\'([A-Z0-9_]*)\' *?}}/m', $file_contents, $matches, PREG_OFFSET_CAPTURE)) {
            $translation_command = $matches[0];
            $translation_key = $matches[1][0];

            $translation = 'TRANSLATION_NOT_FOUND';
            if (property_exists($strings, $translation_key))
                $translation = $strings->{$translation_key};

            $translation_command_start = $translation_command[1];
            $translation_command_len = strlen($translation_command[0]);

            $file_contents = substr_replace(
                $file_contents,
                $translation,
                $translation_command_start,
                $translation_command_len
            );
        }

        $this->setLangToContext($context, $lang);

        unset($strings);
        return $file_contents;
    }

    public function setLangToContext(&$context, $lang) {
        if ($context === null) {
            $context = array('lang' => $lang);
        } else
            $context['lang'] = $lang;
    }

    private function cacheTranslationFile($lang) {
        $path_file = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'langs' . DIRECTORY_SEPARATOR;
        $path_cache = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;

        if (!file_exists($path_file . $lang . '.json'))
            $lang = $this->defaultLang;

        $lang_file = $path_file . $lang . '.json';
        $cache_file = $path_cache. $lang . '_' . filemtime($lang_file) . '.php';

        if (!file_exists($cache_file)) {
            $this->notCached = true;
            file_put_contents($cache_file, '<?php $strings='.
                var_export(json_decode(file_get_contents($lang_file)), true).';', LOCK_EX);
        }
    }

    private function getCacheID(string $lang) {
        $path_file = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'langs' . DIRECTORY_SEPARATOR;

        if (!file_exists($path_file . $lang . '.json'))
            return '';
        return filemtime($path_file . $lang . '.json');
    }

    public static function getInstance($langsFolder=null, $default=null): I18n {

        if(is_null(self::$_instance)) {
            self::$_instance = new I18n($langsFolder, $default);
        }

        return self::$_instance;
    }

}
