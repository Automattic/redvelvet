<?php

declare(strict_types=1);

namespace CupcakeLabs\RedVelvet\Tests\Unit;

use CupcakeLabs\RedVelvet\Converter\Blocks2Npf;
use PHPUnit\Framework\TestCase;

class Blocks2NpfTest extends TestCase
{
    public function test_blocks_to_npf(): void
    {
        $html_blocks = '<!-- wp:paragraph --><p>THIS IS A PARAGRAPH</p><!-- /wp:paragraph --><!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>THIS IS A BLOCKQUOTE</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote -->';

        $expected_npf = '{"content":[{"type":"text","text":"THIS IS A PARAGRAPH"},{"type":"text","text":"THIS IS A BLOCKQUOTE","subtype":"quote"}],"layout":[],"trail":[],"version":2}';

        $result = (new Blocks2Npf())->convert($html_blocks);

        $this->assertSame($expected_npf, $result);
    }

    public function test_performance(): void
    {
        $large_blocks = '<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">title</h1><!-- /wp:heading --><!-- wp:paragraph --><p>paragraph</p><!-- /wp:paragraph --><!-- wp:heading {"level":1} --><h1 class="wp-block-heading">biggest</h1><!-- /wp:heading --><!-- wp:heading --><h2 class="wp-block-heading">bigger</h2><!-- /wp:heading --><!-- wp:quote {"className":"quirky"} --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>quirky row 1<br>quircky row 2</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote --><!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>quote row 1<br>quote row 2</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote --><!-- wp:quote {"className":"chat"} --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p><strong>chat1:</strong> row 1<br><strong>chat2:</strong> row 2</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote --><!-- wp:pullquote --><figure class="wp-block-pullquote"><blockquote><p>indentend row 1<br>indented row 2</p></blockquote></figure><!-- /wp:pullquote --><!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>bullet 1</li><!-- /wp:list-item --><!-- wp:list-item --><li>bullet 2</li><!-- /wp:list-item --></ul><!-- /wp:list --><!-- wp:list {"ordered":true} --><ol class="wp-block-list"><!-- wp:list-item --><li>ordered bullet 1</li><!-- /wp:list-item --><!-- wp:list-item --><li>ordered bullet 2</li><!-- /wp:list-item --></ol><!-- /wp:list -->';
        $expected_npf_post = '{"content":[{"type":"text","text":"title","subtype":"heading1"},{"type":"text","text":"paragraph"},{"type":"text","text":"biggest","subtype":"heading1"},{"type":"text","text":"bigger","subtype":"heading2"},{"type":"text","text":"quirky row 1\nquircky row 2","subtype":"quirky"},{"type":"text","text":"quote row 1\nquote row 2","subtype":"quote"},{"type":"text","text":"chat1: row 1\nchat2: row 2","subtype":"chat","formatting":[{"type":"bold","start":0,"end":6},{"type":"bold","start":12,"end":18}]},{"type":"text","text":"indentend row 1\nindented row 2","subtype":"indented"},{"type":"text","text":"bullet 1","subtype":"unordered-list-item"},{"type":"text","text":"bullet 2","subtype":"unordered-list-item"},{"type":"text","text":"ordered bullet 1","subtype":"ordered-list-item"},{"type":"text","text":"ordered bullet 2","subtype":"ordered-list-item"}],"layout":[],"trail":[],"version":2}';
        $start_time = microtime(true);
        $generated_npf_post = (new Blocks2Npf())->convert($large_blocks);
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds

        $this->assertLessThan(1, $execution_time, 'Conversion took too long.');
        $this->assertSame($expected_npf_post, $generated_npf_post);
    }

    public function test_styling(): void
    {
        $html_blocks = '<!-- wp:quote {"className":"chat"} --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p><strong>chat1:</strong> row 1<br><strong>chat2:</strong> row 2</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote -->';

        $expected_npf = '{"content":[{"type":"text","text":"chat1: row 1\nchat2: row 2","subtype":"chat","formatting":[{"type":"bold","start":0,"end":6},{"type":"bold","start":12,"end":18}]}],"layout":[],"trail":[],"version":2}';

        $result = (new Blocks2Npf())->convert($html_blocks);

        $this->assertSame($expected_npf, $result);
    }

    public function test_image_conversion(): void
    {
        $html_blocks = '<!-- wp:tumblr/image-set --><!-- wp:tumblr/image {"media":[{"mediaKey":"8696e666a593dc6be3de6c48a8a08c9f:7bc0ade64723765e-65","type":"image/webp","width":1024,"height":1024,"url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s1280x1920/23796b7b57d59dc6ddd9b5a012493ad334f3be38.webp","hasOriginalDimensions":true},{"mediaKey":"8696e666a593dc6be3de6c48a8a08c9f:7bc0ade64723765e-65","type":"image/webp","width":640,"height":640,"url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s640x960/27c1bba88e9be35f65fd932f534f7e544d8025ee.webp"},{"mediaKey":"8696e666a593dc6be3de6c48a8a08c9f:7bc0ade64723765e-65","type":"image/webp","width":540,"height":540,"url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s540x810/42cf2c1781b34dd98a7a5192a8ecae2e6ea2b774.webp"},{"mediaKey":"8696e666a593dc6be3de6c48a8a08c9f:7bc0ade64723765e-65","type":"image/webp","width":500,"height":500,"url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s500x750/20568e7015bb60c77b682aaac87b0b22117a2e55.webp"},{"mediaKey":"8696e666a593dc6be3de6c48a8a08c9f:7bc0ade64723765e-65","type":"image/webp","width":400,"height":400,"url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s400x600/4257be90ac50899978bb920444db686279d747db.webp"},{"mediaKey":"8696e666a593dc6be3de6c48a8a08c9f:7bc0ade64723765e-65","type":"image/webp","width":250,"height":250,"url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s250x400/f82798e68e414f55226c7dcb1f3b52dec4d61e66.webp"},{"mediaKey":"8696e666a593dc6be3de6c48a8a08c9f:7bc0ade64723765e-65","type":"image/webp","width":250,"height":250,"url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s250x250_c1/9b93da00cd9ac1eb0faffc77872ae28ccca28fc6.webp","cropped":true},{"mediaKey":"8696e666a593dc6be3de6c48a8a08c9f:7bc0ade64723765e-65","type":"image/webp","width":100,"height":100,"url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s100x200/3f5c3da6b22ecf680b7cc4b2f3de734c34601602.webp"},{"mediaKey":"8696e666a593dc6be3de6c48a8a08c9f:7bc0ade64723765e-65","type":"image/webp","width":75,"height":75,"url":"https://64.media.tumblr.com/8696e666a593dc6be3de6c48a8a08c9f/7bc0ade64723765e-65/s75x75_c1/26fb62d21cf83e3d479f6b98f10eaf19a20d067e.webp","cropped":true}],"colWidth":540} --><!-- /wp:tumblr/image --><!-- /wp:tumblr/image-set -->';

        $expected_npf = '{"content":[{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s1280x1920\/23796b7b57d59dc6ddd9b5a012493ad334f3be38.webp","width":1024,"height":1024},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s640x960\/27c1bba88e9be35f65fd932f534f7e544d8025ee.webp","width":640,"height":640},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s540x810\/42cf2c1781b34dd98a7a5192a8ecae2e6ea2b774.webp","width":540,"height":540},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s500x750\/20568e7015bb60c77b682aaac87b0b22117a2e55.webp","width":500,"height":500},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s400x600\/4257be90ac50899978bb920444db686279d747db.webp","width":400,"height":400},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s250x400\/f82798e68e414f55226c7dcb1f3b52dec4d61e66.webp","width":250,"height":250},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s250x250_c1\/9b93da00cd9ac1eb0faffc77872ae28ccca28fc6.webp","width":250,"height":250},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s100x200\/3f5c3da6b22ecf680b7cc4b2f3de734c34601602.webp","width":100,"height":100},{"type":"image","url":"https:\/\/64.media.tumblr.com\/8696e666a593dc6be3de6c48a8a08c9f\/7bc0ade64723765e-65\/s75x75_c1\/26fb62d21cf83e3d479f6b98f10eaf19a20d067e.webp","width":75,"height":75}],"layout":[],"trail":[],"version":2}';

        $result = (new Blocks2Npf())->convert($html_blocks);

        $this->assertSame($expected_npf, $result);
    }
}
