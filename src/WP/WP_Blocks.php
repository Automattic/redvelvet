<?php

declare(strict_types=1);

namespace CupcakeLabs\RedVelvet\WP;

class WP_Blocks
{
    /**
     * Parses blocks out of a content string.
     *
     * @since 5.0.0
     *
     * @param string $content Post content.
     * @return array[] {
     *     Array of block structures.
     *
     *     @type array ...$0 {
     *         An associative array of a single parsed block object. See WP_Block_Parser_Block.
     *
     *         @type string   $blockName    Name of block.
     *         @type array    $attrs        Attributes from block comment delimiters.
     *         @type array[]  $innerBlocks  List of inner blocks. An array of arrays that
     *                                      have the same structure as this one.
     *         @type string   $innerHTML    HTML from inside block comment delimiters.
     *         @type array    $innerContent List of string fragments and null markers where
     *                                      inner blocks were found.
     *     }
     * }
     */
    public static function parse_blocks($content)
    {
        return (new WP_Block_Parser())->parse($content);
    }

    /**
     * Returns a joined string of the aggregate serialization of the given
     * parsed blocks.
     *
     * @since 5.3.1
     *
     * @param array[] $blocks {
     *     Array of block structures.
     *
     *     @type array ...$0 {
     *         An associative array of a single parsed block object. See WP_Block_Parser_Block.
     *
     *         @type string   $blockName    Name of block.
     *         @type array    $attrs        Attributes from block comment delimiters.
     *         @type array[]  $innerBlocks  List of inner blocks. An array of arrays that
     *                                      have the same structure as this one.
     *         @type string   $innerHTML    HTML from inside block comment delimiters.
     *         @type array    $innerContent List of string fragments and null markers where
     *                                      inner blocks were found.
     *     }
     * }
     * @return string String of rendered HTML.
     */
    public static function serialize_blocks($blocks)
    {
        return implode('', array_map(self::serialize_block(...), $blocks));
    }

    /**
     * Returns the content of a block, including comment delimiters, serializing all
     * attributes from the given parsed block.
     *
     * This should be used when preparing a block to be saved to post content.
     * Prefer `render_block` when preparing a block for display. Unlike
     * `render_block`, this does not evaluate a block's `render_callback`, and will
     * instead preserve the markup as parsed.
     *
     * @since 5.3.1
     *
     * @param array $block {
     *     An associative array of a single parsed block object. See WP_Block_Parser_Block.
     *
     *     @type string   $blockName    Name of block.
     *     @type array    $attrs        Attributes from block comment delimiters.
     *     @type array[]  $innerBlocks  List of inner blocks. An array of arrays that
     *                                  have the same structure as this one.
     *     @type string   $innerHTML    HTML from inside block comment delimiters.
     *     @type array    $innerContent List of string fragments and null markers where
     *                                  inner blocks were found.
     * }
     * @return string String of rendered HTML.
     */
    public static function serialize_block($block)
    {
        $block_content = '';

        $index = 0;
        foreach ($block['innerContent'] as $chunk) {
            $block_content .= is_string($chunk) ? $chunk : self::serialize_block($block['innerBlocks'][ $index++ ]);
        }

        if (!is_array($block['attrs'])) {
            $block['attrs'] = array();
        }

        return self::get_comment_delimited_block_content(
            $block['blockName'],
            $block['attrs'],
            $block_content
        );
    }

    /**
     * Returns the content of a block, including comment delimiters.
     *
     * @since 5.3.1
     *
     * @param string|null $block_name       Block name. Null if the block name is unknown,
     *                                      e.g. Classic blocks have their name set to null.
     * @param array       $block_attributes Block attributes.
     * @param string      $block_content    Block save content.
     * @return string Comment-delimited block content.
     */
    public static function get_comment_delimited_block_content($block_name, $block_attributes, $block_content)
    {
        if (is_null($block_name)) {
            return $block_content;
        }

        $serialized_block_name = self::strip_core_block_namespace($block_name);
        $serialized_attributes = empty($block_attributes) ? '' : self::serialize_block_attributes($block_attributes) . ' ';

        if (empty($block_content)) {
            return sprintf('<!-- wp:%s %s/-->', $serialized_block_name, $serialized_attributes);
        }

        return sprintf(
            '<!-- wp:%s %s-->%s<!-- /wp:%s -->',
            $serialized_block_name,
            $serialized_attributes,
            $block_content,
            $serialized_block_name
        );
    }

    /**
     * Returns the block name to use for serialization. This will remove the default
     * "core/" namespace from a block name.
     *
     * @since 5.3.1
     *
     * @param string|null $block_name Optional. Original block name. Null if the block name is unknown,
     *                                e.g. Classic blocks have their name set to null. Default null.
     * @return string Block name to use for serialization.
     */
    public static function strip_core_block_namespace($block_name = null)
    {
        if (is_string($block_name) && str_starts_with($block_name, 'core/')) {
            return substr($block_name, 5);
        }

        return $block_name;
    }

    /**
     * Given an array of attributes, returns a string in the serialized attributes
     * format prepared for post content.
     *
     * The serialized result is a JSON-encoded string, with unicode escape sequence
     * substitution for characters which might otherwise interfere with embedding
     * the result in an HTML comment.
     *
     * This function must produce output that remains in sync with the output of
     * the serializeAttributes JavaScript function in the block editor in order
     * to ensure consistent operation between PHP and JavaScript.
     *
     * @since 5.3.1
     *
     * @param array $block_attributes Attributes object.
     * @return string Serialized attributes.
     */
    public static function serialize_block_attributes($block_attributes)
    {
        $encoded_attributes = json_encode($block_attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $encoded_attributes = preg_replace('/--/', '\\u002d\\u002d', $encoded_attributes);
        $encoded_attributes = preg_replace('/</', '\\u003c', $encoded_attributes);
        $encoded_attributes = preg_replace('/>/', '\\u003e', $encoded_attributes);
        $encoded_attributes = preg_replace('/&/', '\\u0026', $encoded_attributes);
        // Regex: /\\"/
        $encoded_attributes = preg_replace('/\\\\"/', '\\u0022', $encoded_attributes);

        return $encoded_attributes;
    }

    public static function wp_strip_all_tags($text, $remove_breaks = false)
    {
        if (is_null($text)) {
            return '';
        }

        if (!is_scalar($text)) {
            throw new \InvalidArgumentException(
                sprintf(
                    /* translators: 1: The function name, 2: The argument number, 3: The argument name, 4: The expected type, 5: The provided type. */
                    __('%1$s expects parameter %2$s (%3$s) to be a %4$s, %5$s given.'),
                    __FUNCTION__,
                    '#1',
                    '$text',
                    'string',
                    gettype($text)
                )
            );
        }

        $text = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $text);
        $text = strip_tags($text);

        if ($remove_breaks) {
            $text = preg_replace('/[\r\n\t ]+/', ' ', $text);
        }

        return trim($text);
    }
}
