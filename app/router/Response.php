<?php

namespace Testify\Router;

class Response {

    public static function fromView(string $file) : string {
        $path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
        $file_contents = file_get_contents($path . $file);
        $file_extends_contents = null;

        // On vérifie la présence d'une commande extends
        if (preg_match('/{% *?extends *?\'(.*)\' *?%}/m', $file_contents, $matches)) {
            $file_extends_contents = file_get_contents($path . $matches[1]);
        }

        if (!$file_extends_contents)
            return $file_contents;
        else {
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
                        substr($file_extends_contents, $block['content_start'], $block['content_length']),
                        $block['start'],
                        $block['length']
                    );
                } else {
                    $block_in_file = $file_blocks[$block_in_file_index];

                    $file_extends_contents = substr_replace(
                        $file_extends_contents,
                        substr($file_contents, $block_in_file['content_start'], $block_in_file['content_length']),
                        $block['start'],
                        $block['length']
                    );
                }
            }

            return $file_extends_contents;
        }
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
        $block = null;

        if (preg_match_all('/{% *?block *?([a-z_0-9]*) *?%}([\S\s]*?){% *?endblock *?%}/m', $file_contents, $matches, PREG_OFFSET_CAPTURE)) {
            $blocks = $matches[0];
            $blocks_id = $matches[1];
            $blocks_content = $matches[2];

            for ($i = 0; $i < count($blocks); $i++) {
                $block_id = $blocks_id[$i][0];

                if ($block_id !== $id)
                    continue;

                $block_len = strlen($blocks[$i][0]);
                $block_contents_len = strlen($blocks_content[$i][0]);

                $block_start = $blocks[$i][1];
                $block_contents_start = $blocks_content[$i][1];

                $block = array(
                    'id' => $block_id,
                    'start' => $block_start,
                    'length' => $block_len,
                    'content_start' => $block_contents_start,
                    'content_length' => $block_contents_len
                );
            }
        }
        return $block;
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
