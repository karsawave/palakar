<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Resources\Pagination;
use Resources\RunException;

final class PaginationTest extends TestCase
{
    private function pagination(array $options): Pagination
    {
        return (new Pagination())->setOption(['urlReference' => 'http://x/list%_%', 'format' => '?page=%#%'] + $options);
    }

    public function testLinksAroundTheCurrentPage(): void
    {
        $this->assertSame([
            '<a href="http://x/list">&laquo; Previous</a>',
            '<a href="http://x/list">1</a>',
            '<span>2</span>',
            '<a href="http://x/list?page=3">3</a>',
            '<a href="http://x/list?page=4">4</a>',
            '<a href="http://x/list?page=5">5</a>',
            '<a href="http://x/list?page=3">Next &raquo;</a>',
        ], $this->pagination(['total' => 50, 'limit' => 10, 'current' => 2])->getUrl());
    }

    public function testDotsBetweenBlocks(): void
    {
        $links = $this->pagination(['total' => 200, 'limit' => 10, 'current' => 10, 'prevNext' => false, 'noHref' => true])->getUrl();

        $this->assertSame([1, '...', 8, 9, 10, 11, 12, '...', 20], array_column($links, 'value'));
        $this->assertSame(['link' => '', 'value' => 10, 'active' => true], $links[4]);
        $this->assertSame('http://x/list?page=8', $links[2]['link']);
    }

    public function testOptionsFromTheUrlAreStrings(): void
    {
        // e.g. setOption('current', $this->uri->path(2))
        $pagination = $this->pagination(['total' => '30', 'limit' => '10', 'current' => '3']);

        $this->assertSame(3, $pagination->current);
        $this->assertCount(4, $pagination->getUrl());
    }

    public function testUnknownOptionIsRefused(): void
    {
        $this->expectException(RunException::class);

        (new Pagination())->setOption('curent', 2);
    }

    public function testLinksAreEscaped(): void
    {
        $links = (new Pagination())->setOption([
            'urlReference' => '/search?q="><script>alert(1)</script>&p=%#%',
            'format' => '',
            'total' => 20,
            'limit' => 10,
            'current' => 1,
        ])->getUrl();

        $this->assertStringNotContainsString('<script>', implode('', $links));
        $this->assertSame('<a href="/search?q=&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;&amp;p=2">2</a>', $links[1]);
    }

    /**
     * Values for many option combinations, locked before the spec-011 rewrite.
     */
    public function testEdgeCases(): void
    {
        $values = fn (array $options) => array_column($this->pagination($options + ['noHref' => true])->getUrl(), 'value');
        $prev = '&laquo; Previous';
        $next = 'Next &raquo;';

        $this->assertSame([1, '...', 10], $values(['total' => 100, 'limit' => 10, 'current' => 0, 'midSize' => 1]));
        $this->assertSame([$prev, 1, '...', 8, 9, 10], $values(['total' => 100, 'limit' => 10, 'current' => 10]));
        $this->assertSame([1, 2, 3, $next], $values(['total' => 30, 'limit' => 10, 'current' => 1]));
        $this->assertSame([$prev, 1, '...', 10], $values(['total' => 100, 'limit' => 10, 'current' => 12]));
        $this->assertSame(range(1, 10), $values(['total' => 100, 'limit' => 10, 'current' => 5, 'showAll' => true, 'prevNext' => false]));
        $this->assertSame([$prev, $next], $values(['total' => 100, 'limit' => 10, 'current' => 5, 'showNumber' => false]));
        $this->assertSame([1, 2, '...', 5, '...', 9, 10], $values(['total' => 100, 'limit' => 10, 'current' => 5, 'endSize' => 2, 'midSize' => 0, 'prevNext' => false]));
        $this->assertSame([1, '...', 5, '...', 10], $values(['total' => 100, 'limit' => 10, 'current' => 5, 'endSize' => 0, 'midSize' => -3, 'prevNext' => false]));
        $this->assertSame([1, 2, 3, 4], $values(['total' => 31, 'limit' => 10, 'current' => 2, 'midSize' => 1, 'prevNext' => false]));
    }

    public function testOnePageOrBadLimitGivesNoLinks(): void
    {
        $this->assertSame([], $this->pagination(['total' => 5, 'limit' => 10])->getUrl());
        $this->assertCount(3, $this->pagination(['total' => 3, 'limit' => 0, 'prevNext' => false])->getUrl());
    }
}
