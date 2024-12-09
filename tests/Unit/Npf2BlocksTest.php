<?php

declare(strict_types=1);

namespace CupcakeLabs\RedVelvet\Tests\Unit;

use CupcakeLabs\RedVelvet\Converter\Npf2Blocks;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class Npf2BlocksTest extends TestCase
{
    public static function provide_npf_to_blocks(): iterable
    {
        yield 'paragraph' => [
            'npf_post' => '{"content":[{"type":"text","text":"this is a paragraph"}],"layout":[],"trail":[],"version":2}',
            'blocks' => '<!-- wp:paragraph --><p>this is a paragraph</p><!-- /wp:paragraph -->',
        ];

        yield 'formatted_paragraph' => [
            'npf_post' => '{"content":[{"type":"text","text":"supercalifragilisticexpialidocious","formatting":[{"type":"bold","start":0,"end":9},{"type":"italic","start":9,"end":34},{"type":"bold","start":9,"end":20}]}],"layout":[],"trail":[],"version":2}',
            'blocks' => '<!-- wp:paragraph --><p><strong>supercali</strong><strong><em>fragilistic</em></strong><em>expialidocious</em></p><!-- /wp:paragraph -->',
        ];

        yield 'blockquote' => [
            'npf_post' => '{"content":[{"type":"text","text":"this is a quote","subtype":"quote"}],"layout":[],"trail":[],"version":2}',
            'blocks' => '<!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>this is a quote</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote -->',
        ];

        yield 'chat' => [
            'npf_post' => '{"content":[{"type":"text","text":"John: How are you?\nJane: Good and you?","subtype":"chat","formatting":[{"type":"bold","start":0,"end":5},{"type":"bold","start":19,"end":24}]}],"layout":[],"trail":[],"version":2}',
            'blocks' => '<!-- wp:quote {"className":"chat"} --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p><strong>John:</strong> How are you?<br><strong>Jane:</strong> Good and you?</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote -->'
        ];

        yield 'quirky' => [
            'npf_post' => '{"content":[{"type":"text","text":"this is quirky","subtype":"quirky"}],"layout":[],"trail":[],"version":2}',
            'blocks' => '<!-- wp:quote {"className":"quirky"} --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>this is quirky</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote -->',
        ];

        yield 'heading1' => [
            'npf_post' => '{"content":[{"type":"text","text":"this is a heading level 1","subtype":"heading1"}],"layout":[],"trail":[],"version":2}',
            'blocks' => '<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">this is a heading level 1</h1><!-- /wp:heading -->',
        ];

        yield 'heading2' => [
            'npf_post' => '{"content":[{"type":"text","text":"this is a heading level 2","subtype":"heading2"}],"layout":[],"trail":[],"version":2}',
            'blocks' => '<!-- wp:heading --><h2 class="wp-block-heading">this is a heading level 2</h2><!-- /wp:heading -->',
        ];

        yield 'indented' => [
            'npf_post' => '{"content":[{"type":"text","text":"this is indented\nthis is a second line indented","subtype":"indented"}],"layout":[],"trail":[],"version":2}',
            'blocks' => '<!-- wp:pullquote --><figure class="wp-block-pullquote"><blockquote><p>this is indented<br>this is a second line indented</p></blockquote></figure><!-- /wp:pullquote -->',
        ];

        yield 'bulleted-list' => [
            'npf_post' => '{"content":[{"type":"text","text":"first bullet","subtype":"unordered-list-item"},{"type":"text","text":"second bullet","subtype":"unordered-list-item"},{"type":"text","text":"third bullet","subtype":"unordered-list-item"}],"layout":[],"trail":[],"version":2}',
            'blocks' => '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>first bullet</li><!-- /wp:list-item --><!-- wp:list-item --><li>second bullet</li><!-- /wp:list-item --><!-- wp:list-item --><li>third bullet</li><!-- /wp:list-item --></ul><!-- /wp:list -->',
        ];

        yield 'ordered-list' => [
            'npf_post' => '{"content":[{"type":"text","text":"first ordered bullet","subtype":"ordered-list-item"},{"type":"text","text":"second ordered bullet","subtype":"ordered-list-item"},{"type":"text","text":"third ordered bullet","subtype":"ordered-list-item"}],"layout":[],"trail":[],"version":2}',
            'blocks' => '<!-- wp:list {"ordered":true} --><ol class="wp-block-list"><!-- wp:list-item --><li>first ordered bullet</li><!-- /wp:list-item --><!-- wp:list-item --><li>second ordered bullet</li><!-- /wp:list-item --><!-- wp:list-item --><li>third ordered bullet</li><!-- /wp:list-item --></ol><!-- /wp:list -->',
        ];

        yield 'image' => [
            'npf_post' => '{"content":[{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s1280x1920\/23796b7b57d59dc6ddd9b5a012493ad334f3be38.webp","width":1024,"height":1024},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s640x960\/27c1bba88e9be35f65fd932f534f7e544d8025ee.webp","width":640,"height":640},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s540x810\/42cf2c1781b34dd98a7a5192a8ecae2e6ea2b774.webp","width":540,"height":540},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s500x750\/20568e7015bb60c77b682aaac87b0b22117a2e55.webp","width":500,"height":500},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s400x600\/4257be90ac50899978bb920444db686279d747db.webp","width":400,"height":400},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s250x400\/f82798e68e414f55226c7dcb1f3b52dec4d61e66.webp","width":250,"height":250},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s250x250_c1\/9b93da00cd9ac1eb0faffc77872ae28ccca28fc6.webp","width":250,"height":250},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s100x200\/3f5c3da6b22ecf680b7cc4b2f3de734c34601602.webp","width":100,"height":100},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s75x75_c1\/26fb62d21cf83e3d479f6b98f10eaf19a20d067e.webp","width":75,"height":75}],"layout":[],"trail":[],"version":2}',
            'blocks' => '<!-- wp:tumblr/image {"media":[{"type":"image","url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s1280x1920/23796b7b57d59dc6ddd9b5a012493ad334f3be38.webp"}],"attribution":[{"type":"","url":""}],"altText":"","file":[],"colWidth":512,"displayWidth":1024,"displayHeight":1024} /--><!-- wp:tumblr/image {"media":[{"type":"image","url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s640x960/27c1bba88e9be35f65fd932f534f7e544d8025ee.webp"}],"attribution":[{"type":"","url":""}],"altText":"","file":[],"colWidth":320,"displayWidth":640,"displayHeight":640} /--><!-- wp:tumblr/image {"media":[{"type":"image","url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s540x810/42cf2c1781b34dd98a7a5192a8ecae2e6ea2b774.webp"}],"attribution":[{"type":"","url":""}],"altText":"","file":[],"colWidth":270,"displayWidth":540,"displayHeight":540} /--><!-- wp:tumblr/image {"media":[{"type":"image","url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s500x750/20568e7015bb60c77b682aaac87b0b22117a2e55.webp"}],"attribution":[{"type":"","url":""}],"altText":"","file":[],"colWidth":250,"displayWidth":500,"displayHeight":500} /--><!-- wp:tumblr/image {"media":[{"type":"image","url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s400x600/4257be90ac50899978bb920444db686279d747db.webp"}],"attribution":[{"type":"","url":""}],"altText":"","file":[],"colWidth":200,"displayWidth":400,"displayHeight":400} /--><!-- wp:tumblr/image {"media":[{"type":"image","url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s250x400/f82798e68e414f55226c7dcb1f3b52dec4d61e66.webp"}],"attribution":[{"type":"","url":""}],"altText":"","file":[],"colWidth":125,"displayWidth":250,"displayHeight":250} /--><!-- wp:tumblr/image {"media":[{"type":"image","url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s250x250_c1/9b93da00cd9ac1eb0faffc77872ae28ccca28fc6.webp"}],"attribution":[{"type":"","url":""}],"altText":"","file":[],"colWidth":125,"displayWidth":250,"displayHeight":250} /--><!-- wp:tumblr/image {"media":[{"type":"image","url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s100x200/3f5c3da6b22ecf680b7cc4b2f3de734c34601602.webp"}],"attribution":[{"type":"","url":""}],"altText":"","file":[],"colWidth":50,"displayWidth":100,"displayHeight":100} /--><!-- wp:tumblr/image {"media":[{"type":"image","url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s75x75_c1/26fb62d21cf83e3d479f6b98f10eaf19a20d067e.webp"}],"attribution":[{"type":"","url":""}],"altText":"","file":[],"colWidth":37.5,"displayWidth":75,"displayHeight":75} /-->',
        ];

        yield 'audio' => [
            'npf_post' => '{"content":[{"type":"audio","url":"https://example.com/audio.mp3","title":"Song Title","artist":"Artist Name","album":"Album Name","poster":[{"url":"https://example.com/cover.jpg"}]}],"layout":[],"trail":[],"version":2}',
            'blocks' => '<!-- wp:audio {"mediaURL":"https://example.com/audio.mp3","mediaTitle":"Song Title","mediaArtist":"Artist Name","mediaAlbum":"Album Name","poster":{"url":"https://example.com/cover.jpg"}} --><figure class="wp-block-audio"><audio controls src="https://example.com/audio.mp3"></audio></figure><!-- /wp:audio -->',
        ];
    }

    #[DataProvider('provide_npf_to_blocks')]
    public function test_npf_to_blocks(string $npf_post, string $blocks): void
    {
        $this->assertSame($blocks, (new Npf2Blocks())->convert($npf_post));
    }

    public function test_npf_to_blocks_performance(): void
    {
        $large_npf = '{"content":[{"type":"text","text":"title","subtype":"heading1"},{"type":"text","text":"paragraph"},{"type":"text","text":"biggest","subtype":"heading1"},{"type":"text","text":"bigger","subtype":"heading2"},{"type":"text","text":"quirky row 1\nquircky row 2","subtype":"quirky"},{"type":"text","text":"quote row 1\nquote row 2","subtype":"quote"},{"type":"text","text":"chat: row 1\nchat: row 2","subtype":"chat","formatting":[{"type":"bold","start":0,"end":5},{"type":"bold","start":12,"end":17}]},{"type":"text","text":"indentend row 1\nindented row 2","subtype":"indented"},{"type":"text","text":"bullet 1","subtype":"unordered-list-item"},{"type":"text","text":"bullet 2","subtype":"unordered-list-item"},{"type":"text","text":"ordered bullet 1","subtype":"ordered-list-item"},{"type":"text","text":"ordered bullet 2","subtype":"ordered-list-item"}],"layout":[],"trail":[],"version":2}';
        $expected_blocks = '<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">title</h1><!-- /wp:heading --><!-- wp:paragraph --><p>paragraph</p><!-- /wp:paragraph --><!-- wp:heading {"level":1} --><h1 class="wp-block-heading">biggest</h1><!-- /wp:heading --><!-- wp:heading --><h2 class="wp-block-heading">bigger</h2><!-- /wp:heading --><!-- wp:quote {"className":"quirky"} --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>quirky row 1<br>quircky row 2</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote --><!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>quote row 1<br>quote row 2</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote --><!-- wp:quote {"className":"chat"} --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p><strong>chat:</strong> row 1<br><strong>chat:</strong> row 2</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote --><!-- wp:pullquote --><figure class="wp-block-pullquote"><blockquote><p>indentend row 1<br>indented row 2</p></blockquote></figure><!-- /wp:pullquote --><!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>bullet 1</li><!-- /wp:list-item --><!-- wp:list-item --><li>bullet 2</li><!-- /wp:list-item --></ul><!-- /wp:list --><!-- wp:list {"ordered":true} --><ol class="wp-block-list"><!-- wp:list-item --><li>ordered bullet 1</li><!-- /wp:list-item --><!-- wp:list-item --><li>ordered bullet 2</li><!-- /wp:list-item --></ol><!-- /wp:list -->';
        $start_time = microtime(true);
        $generated_blocks = (new Npf2Blocks())->convert($large_npf);
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds

        $this->assertLessThan(1, $execution_time, 'Conversion took too long.');
        $this->assertSame($expected_blocks, $generated_blocks);
    }
}
