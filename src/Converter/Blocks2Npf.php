<?php

declare(strict_types=1);

namespace CupcakeLabs\RedVelvet\Converter;

use CupcakeLabs\RedVelvet\WP\WP_Blocks;

/**
 * Converts Gutenberg HTML to NPF format.
 */
class Blocks2Npf
{
    /**
     * Converts Gutenberg HTML to NPF format.
     *
     * @param string $html_blocks The Gutenberg HTML string.
     *
     * @return string The equivalent NPF format JSON.
     */
    public function convert(string $html_blocks): string
    {
        // Parse Gutenberg HTML to get an array of blocks
        $blocks = WP_Blocks::parse_blocks($html_blocks);
        $npf = $this->initialize_npf();
        $block_index = -1;

        // Function to get the next index for block reference
        $next_index = static fn (): int => ++$block_index; // part of the layout

        // Append NPF blocks to content and layout
        $append = static function (array &$npf, array ...$npf_blocks) use ($next_index): void {
            foreach ($npf_blocks as $block) {
                $npf['content'][] = $block;
            }
        };

        $nested_items = array();
        $nested_block = false;
        $nested_block_subtype = '';

        // Convert each Gutenberg block to NPF format
        foreach ($blocks as $block) {
            match ($block['blockName']) {
                'core/list', 'tumblr/image-set' => $this->handle_nested_block($block, $nested_items, $nested_block, $nested_block_subtype),
                default => $this->process_block($block, $npf, $append, $nested_block, $nested_items)
            };
        }

        // If there's an unfinished list, add it
        if ($nested_block) {
            $append($npf, ...$nested_items);
        }

        return json_encode($npf);
    }

    /**
     * Initializes the NPF structure.
     *
     * @return array The NPF structure.
     */
    private function initialize_npf(): array
    {
        return array(
            'content' => array(),
            'layout' => array(), // TODO: implement layout
            'trail' => array(),
            'version' => 2,
        );
    }

    /**
     * Handles a block.
     *
     * @param array    $block  The block to handle.
     * @param array    $npf    The NPF structure.
     * @param callable $append The function to append blocks to the NPF structure.
     *
     * @return void
     */
    private function handle_block(array $block, array &$npf, callable $append): void
    {
        match ($block['blockName']) {
            'core/heading' => $this->process_heading_block($block, $npf, $append),
            'core/quote' => $this->process_quote_block($block, $npf, $append),
            'core/pullquote' => $append(
                $npf,
                array(
                    'type' => 'text',
                    'text' => trim(WP_Blocks::wp_strip_all_tags(str_replace('<br>', "\n", $block['innerHTML']))),
                    'subtype' => 'indented',
                )
            ),
            'core/image' => $this->process_image_block($block, $npf, $append),
            'core/audio' => $this->process_audio_block( $block, $npf, $append ),
            default => $append(
                $npf,
                array(
                    'type' => 'text',
                    'text' => trim(WP_Blocks::wp_strip_all_tags($block['innerHTML'])),
                )
            ),
        };
    }

    /**
     * Handles a nested block.
     *
     * @param array   $block                The block to handle.
     * @param array   $nested_items         The nested items.
     * @param boolean $nested_block         Whether the block is nested.
     * @param string  $nested_block_subtype The subtype of the nested block.
     *
     * @return void
     */
    private function handle_nested_block(array $block, array &$nested_items, bool &$nested_block, string &$nested_block_subtype): void
    {
        if ('tumblr/image-set' === $block['blockName']) {
            foreach ($block['innerBlocks'] as $inner_block) {
                if ('tumblr/image' === $inner_block['blockName']) {
                    foreach ($inner_block['attrs']['media'] as $media) {
                        $nested_items[] = array(
                            'type' => 'image',
                            'url' => $media['url'],
                            'width' => $media['width'],
                            'height' => $media['height'],
                        );
                    }
                }
            }
            $nested_block = true;
            return;
        }
        $ordered = $block['attrs']['ordered'] ?? false;
        $subtype = $ordered ? 'ordered-list-item' : 'unordered-list-item';
        foreach ($block['innerBlocks'] as $inner_block) {
            if ('core/list-item' === $inner_block['blockName']) {
                $nested_items[] = array(
                    'type' => 'text',
                    'text' => WP_Blocks::wp_strip_all_tags($inner_block['innerHTML']),
                    'subtype' => $subtype,
                );
            }
        }
        $nested_block = true;
        $nested_block_subtype = $subtype;
    }

    /**
     * Processes a block.
     *
     * @param array    $block        The block to process.
     * @param array    $npf          The NPF structure.
     * @param callable $append       The function to append blocks to the NPF structure.
     * @param boolean  $nested_block Whether the block is nested.
     * @param array    $nested_items The nested items.
     *
     * @return void
     */
    private function process_block(array $block, array &$npf, callable $append, bool &$nested_block, array &$nested_items): void
    {
        if ($nested_block) {
            $append($npf, ...$nested_items);
            $nested_items = array();
            $nested_block = false;
        }
        $this->handle_block($block, $npf, $append);
    }

    /**
     * Processes a heading block.
     *
     * @param array    $block  The heading block to process.
     * @param array    $npf    The NPF structure.
     * @param callable $append The function to append blocks to the NPF structure.
     *
     * @return void
     */
    private function process_heading_block(array $block, array &$npf, callable $append): void
    {
        $level = $block['attrs']['level'] ?? 2;
        $subtype = 1 === $level ? 'heading1' : 'heading2';
        $append(
            $npf,
            array(
                'type' => 'text',
                'text' => trim(WP_Blocks::wp_strip_all_tags($block['innerHTML'])),
                'subtype' => $subtype,
            )
        );
    }

    /**
     * Processes an image block.
     *
     * @param array    $block  The image block to process.
     * @param array    $npf    The NPF structure.
     * @param callable $append The function to append blocks to the NPF structure.
     *
     * @return void
     */
    private function process_image_block(array $block, array &$npf, callable $append): void
    {
        $url = $block['attrs']['url'] ?? '';
        $width = $block['attrs']['width'] ?? 0;
        $height = $block['attrs']['height'] ?? 0;

        $append(
            $npf,
            array(
                'type' => 'image',
                'url' => $url,
                'width' => $width,
                'height' => $height,
            )
        );
    }

    /**
     * Processes an audio block.
     *
     * @param array    $block  The audio block to process.
     * @param array    $npf    The NPF structure.
     * @param callable $append The function to append blocks to the NPF structure.
     *
     * @return void
     */
    private function process_audio_block( array $block, array &$npf, callable $append ): void {
        $url    = $block['attrs']['mediaURL'] ?? '';
        $title  = $block['attrs']['mediaTitle'] ?? '';
        $artist = $block['attrs']['mediaArtist'] ?? '';
        $album  = $block['attrs']['mediaAlbum'] ?? '';
        $poster = $block['attrs']['poster']['url'] ?? '';

        $append(
            $npf,
            array(
                'type'   => 'audio',
                'url'    => $url,
                'title'  => $title,
                'artist' => $artist,
                'album'  => $album,
                'poster' => array(
                    array(
                        'url' => $poster,
                    ),
                ),
            )
        );
    }

    /**
     * Processes a quote block.
     *
     * @param array    $block  The quote block to process.
     * @param array    $npf    The NPF structure.
     * @param callable $append The function to append blocks to the NPF structure.
     *
     * @return void
     */
    private function process_quote_block(array $block, array &$npf, callable $append): void
    {
        $quote_content = '';
        $formatting = array();
        foreach ($block['innerBlocks'] as $inner_block) {
            if ('core/paragraph' === $inner_block['blockName']) {
                $inner_block_inner_html = $inner_block['innerHTML'];
                $extracted_formatting = $this->extract_formatting($inner_block_inner_html);
                // flatten the array $extracted_formatting
                $formatting = array_merge($formatting, $extracted_formatting);
                $quote_content .= trim(WP_Blocks::wp_strip_all_tags(str_replace('<br>', "\n", $inner_block_inner_html))) . "\n";
            }
        }
        $subtype = $block['attrs']['className'] ?? 'quote';
        $npf_partial = array(
            'type' => 'text',
            'text' => trim($quote_content),
            'subtype' => $subtype,
        );

        if (count($formatting) > 0) {
            $npf_partial['formatting'] = $formatting;
        }

        $append(
            $npf,
            $npf_partial
        );
    }

    /**
     * Gets the quote content.
     *
     * @param array $block The quote block.
     *
     * @return string The quote content.
     */
    private function get_quote_content(array $block): string
    {
        $quote_content = '';
        foreach ($block['innerBlocks'] as $inner_block) {
            if ('core/paragraph' === $inner_block['blockName']) {
                $extracted = $this->extract_formatting($inner_block['innerHTML']);
                $quote_content .= trim(WP_Blocks::wp_strip_all_tags(str_replace('<br>', "\n", $extracted))) . "\n";
            }
        }
        return $quote_content;
    }

    /**
     * Extracts formatting information for the content.
     *
     * @param string $html The HTML content.
     *
     * @return array The extracted formatting information.
     */
    private function extract_formatting(string $html): array
    {
        $formatting = array();

        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        foreach ($xpath->query('//strong') as $node) {
            $start = $this->calculate_text_position($node, $dom);
            $length = strlen($node->textContent);
            $formatting[] = array(
                'type' => 'bold',
                'start' => $start,
                'end' => $start + $length,
            );
        }

        // Additional formatting types can be added here (e.g., italic, underline)

        return $formatting;
    }

    /**
     * Calculates the text position of a node within the content.
     *
     * @param \DOMNode     $node The node for which to calculate the position.
     * @param \DOMDocument $dom  The entire document for context.
     *
     * @return integer The start position of the node in the text content.
     */
    private function calculate_text_position(\DOMNode $node, \DOMDocument $dom): int
    {
        $text = WP_Blocks::wp_strip_all_tags($dom->saveHTML());
        $node_text = WP_Blocks::wp_strip_all_tags($dom->saveHTML($node));
        $start_pos = strpos($text, $node_text);
        return false !== $start_pos ? $start_pos : 0;
    }
}
