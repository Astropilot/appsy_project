<?php

namespace Testify\Router;

use Testify\Component\I18n;

class Response {

    public static function fromView(string $file, $context=NULL) : string {
        $path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
        $file_contents = file_get_contents($path . $file);
        $file_extends_contents = null;

        // On vérifie la présence d'une commande extends
        if (preg_match('/{% *?extends *?\'(.*)\' *?%}/m', $file_contents, $matches)) {
            $file_extends_contents = file_get_contents($path . $matches[1]);
        }

        if (!$file_extends_contents) {
            $file_contents = I18n::getInstance()->computeTranslations(
                $file_contents,
                $context
            );
            return self::computeContext($file_contents, $context);
        } else {
            $translation_not_cached = I18n::getInstance()->notCached;
            $path_cache = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
            $view_name = basename($file, '.html');
            $extends_name = basename($matches[1], '.html');

            $lang = '';
            if (I18n::getInstance()->active) {
                $lang = '_' . $_SESSION['lang'];
            }

            $cache_file = $path_cache. $view_name . '_' . $extends_name .
                '_' . filemtime($path. $file) . '_' . filemtime($path. $matches[1]) .
                $lang . '_t.chtml';
            $cache_untranslated_file = $path_cache. $view_name . '_' . $extends_name .
                '_' . filemtime($path. $file) . '_' . filemtime($path. $matches[1]) .
                $lang . '.chtml';

            if (file_exists($cache_file) && !$translation_not_cached) {
                I18n::getInstance()->setLangToContext($context);
                return self::computeContext(file_get_contents($cache_file), $context);
            } elseif (file_exists($cache_file) && $translation_not_cached) {
                $file_extends_contents = I18n::getInstance()->computeTranslations(
                    file_get_contents($cache_untranslated_file),
                    $context
                );
                file_put_contents($cache_file, $file_extends_contents, LOCK_EX);
                return self::computeContext($file_extends_contents, $context);
            }

            $file_blocks = self::getBlocks($file_contents);
            $file_extends_blocks_id = self::getBlocksID($file_extends_contents);

            foreach ($file_extends_blocks_id as $block_id) {

                $block = self::getBlock($file_extends_contents, $block_id);
                $block_in_file_index = array_search($block_id, array_column($file_blocks, 'id'));
                // Si pas de correspondance, on efface tout de même les commandes
                // en gardant le contenu
                if ($block_in_file_index === FALSE) {
                    $file_extends_contents = substr_replace(
                        $file_extends_contents,
                        substr(
                            $file_extends_contents,
                            $block['content_start'],
                            $block['content_length']
                        ),
                        $block['start'],
                        $block['length']
                    );
                } else {
                    $block_in_file = $file_blocks[$block_in_file_index];

                    $file_extends_contents = substr_replace(
                        $file_extends_contents,
                        substr(
                            $file_contents,
                            $block_in_file['content_start'],
                            $block_in_file['content_length']
                        ),
                        $block['start'],
                        $block['length']
                    );
                }
            }

            file_put_contents($cache_untranslated_file, $file_extends_contents, LOCK_EX);
            $file_extends_contents = I18n::getInstance()->computeTranslations($file_extends_contents, $context);
            file_put_contents($cache_file, $file_extends_contents, LOCK_EX);
            return self::computeContext($file_extends_contents, $context);
        }
    }

    private static function computeContext(string $file_contents, $context) : string {
        if ($context === NULL) return $file_contents;

        while (preg_match('/{{ *?([a-z_0-9]*) *?}}/m', $file_contents, $matches, PREG_OFFSET_CAPTURE)) {
            $var = $matches[0];
            $var_name = $matches[1][0];

            if (array_key_exists($var_name, $context)) {
                $var_start = $var[1];
                $var_len = strlen($var[0]);

                $file_contents = substr_replace(
                    $file_contents,
                    $context[$var_name],
                    $var_start,
                    $var_len
                );
            } else {
                throw new \Exception("The view context do not contains the key $var_name");
            }
        }
        return $file_contents;
    }

    private static function getBlocks(string $file_contents) {
        $file_blocks = array();

        if (preg_match_all('/{% *?block *?([a-z_0-9]*) *?%}([\S\s]*?){% *?endblock *?%}/m', $file_contents, $matches, PREG_OFFSET_CAPTURE)) {
            $blocks = $matches[0];
            $blocks_id = $matches[1];
            $blocks_content = $matches[2];

            for ($i = 0; $i < count($blocks); $i++) {
                $block_len = strlen($blocks[$i][0]);
                $block_contents_len = strlen($blocks_content[$i][0]);

                $block_id = $blocks_id[$i][0];
                $block_start = $blocks[$i][1];
                $block_contents_start = $blocks_content[$i][1];

                array_push(
                    $file_blocks,
                    array(
                        'id' => $block_id,
                        'start' => $block_start,
                        'length' => $block_len,
                        'content_start' => $block_contents_start,
                        'content_length' => $block_contents_len
                    )
                );
            }
        }
        return $file_blocks;
    }

    private static function getBlock(string $file_contents, string $id) {
        if (preg_match_all('/{% *?block *?([a-z_0-9]*) *?%}([\S\s]*?){% *?endblock *?%}/m', $file_contents, $matches, PREG_OFFSET_CAPTURE)) {
            $blocks = $matches[0];
            $blocks_id = $matches[1];
            $blocks_content = $matches[2];

            for ($i = 0; $i < count($blocks); $i++) {
                $block_id = $blocks_id[$i][0];

                if ($block_id === $id) {
                    $block_len = strlen($blocks[$i][0]);
                    $block_contents_len = strlen($blocks_content[$i][0]);

                    $block_start = $blocks[$i][1];
                    $block_contents_start = $blocks_content[$i][1];

                    return array(
                        'id' => $block_id,
                        'start' => $block_start,
                        'length' => $block_len,
                        'content_start' => $block_contents_start,
                        'content_length' => $block_contents_len
                    );
                }
            }
        }
        return null;
    }

    private static function getBlocksID(string $file_contents) {
        $file_blocks_id = array();

        if (preg_match_all('/{% *?block *?([a-z_0-9]*) *?%}([\S\s]*?){% *?endblock *?%}/m', $file_contents, $matches)) {
            $blocks_id = $matches[1];

            foreach ($blocks_id as $block_id) {
                array_push($file_blocks_id, $block_id);
            }
        }
        return $file_blocks_id;
    }
}
