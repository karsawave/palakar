<?php

declare(strict_types=1);

/**
 * Panada Pagination class.
 *
 *   $links = (new \Resources\Pagination())->setOption([
 *       'urlReference' => $this->location('posts%_%'),
 *       'format' => '/page/%#%',
 *       'total' => $total,
 *       'limit' => 10,
 *       'current' => $page,
 *   ])->getUrl();
 *
 *   <?= implode(' ', $links) ?>
 *
 * The links are escaped. prevText, nextText and groupSeparator are printed as
 * they are (they are HTML written by you, not by the visitor).
 *
 * @link	http://panadaframework.com/
 *
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>. Thanks to Wordpress {@link http://codex.wordpress.org/Function_Reference/paginate_links}
 *
 * The original getUrl() was adapted from WordPress paginate_links(). It was
 * rewritten from scratch in Palakar (spec-011) so no GPL code is left; the
 * thanks above is kept as history.
 *
 * @since	Version 0.1
 */

namespace Resources;

class Pagination
{
    use Configurable;

    /** Show the numbers. false gives "previous" and "next" only. */
    public bool $showNumber = true;

    /** http://example.com/allposts%_% : %_% is replaced by format (nothing on page 1). */
    public string $urlReference = '';

    /** /%#%/ : %#% is replaced by the page number. */
    public string $format = '/%#%/';

    /** Number of records. */
    public int $total = 1;

    /** Records per page. */
    public int $limit = 5;

    /** Current page number, 0 when none. */
    public int $current = 0;

    /** Show every page number. */
    public bool $showAll = false;

    /** Return [link, value, active] arrays instead of HTML. */
    public bool $noHref = false;

    /** Show previous and next links. */
    public bool $prevNext = true;

    public string $prevText = '&laquo; Previous';
    public string $nextText = 'Next &raquo;';

    /** Shown between blocks of numbers. */
    public string $groupSeparator = '...';

    /** How many numbers on either end, including the end. */
    public int $endSize = 1;

    /** How many numbers on either side of the current page. */
    public int $midSize = 2;

    /**
     * @return array<int, string|array{link: string, value: int|string, active: bool}>
     */
    public function getUrl(): array
    {
        $pages = (int) ceil($this->total / max(1, $this->limit));

        if ($pages < 2) {
            return [];
        }

        $current = $this->current;
        $links = [];

        if ($this->prevNext && $current > 1) {
            $links[] = $this->item($this->link($current - 1), $this->prevText);
        }

        $previous = 0;

        foreach ($this->shownPages($pages) as $page) {
            // The first and last page are always shown, so a gap is never at an end.
            if ($page - $previous > 1) {
                $links[] = $this->text($this->groupSeparator, false);
            }

            $links[] = $page === $current ? $this->text($page, true) : $this->item($this->link($page), $page);
            $previous = $page;
        }

        if ($this->prevNext && $current > 0 && $current < $pages) {
            $links[] = $this->item($this->link($current + 1), $this->nextText);
        }

        return $links;
    }

    /**
     * Page numbers to show, ascending: both ends, and the pages near the current one.
     *
     * @return list<int>
     */
    private function shownPages(int $pages): array
    {
        if (!$this->showNumber) {
            return [];
        }

        if ($this->showAll) {
            return range(1, $pages);
        }

        $ends = max(1, $this->endSize);
        $shown = array_merge(range(1, min($ends, $pages)), range(max(1, $pages - $ends + 1), $pages));

        if ($this->current > 0) {
            $mid = max(0, $this->midSize);
            $from = max(1, $this->current - $mid);
            $to = min($pages, $this->current + $mid);

            if ($from <= $to) {
                $shown = array_merge($shown, range($from, $to));
            }
        }

        $shown = array_unique($shown);
        sort($shown);

        return $shown;
    }

    /**
     * Text without a link: the current page or the group separator.
     */
    private function text(int|string $value, bool $active): string|array
    {
        return $this->noHref ? ['link' => '', 'value' => $value, 'active' => $active] : '<span>' . $value . '</span>';
    }

    /**
     * Page 1 is the plain urlReference, other pages get the format.
     */
    private function link(int $page): string
    {
        $link = str_replace('%_%', $page === 1 ? '' : $this->format, $this->urlReference);

        return str_replace('%#%', (string) $page, $link);
    }

    private function item(string $link, int|string $value): string|array
    {
        return $this->noHref ? ['link' => $link, 'value' => $value, 'active' => false] : '<a href="' . e($link) . '">' . $value . '</a>';
    }
}
