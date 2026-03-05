<?php

namespace App\Pagination;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Pagination wrapper for session index (Doctrine Paginator result), compatible with knp_pagination_render.
 *
 * @template T
 * @implements SlidingPaginationInterface<T>
 */
final class SessionPagination implements SlidingPaginationInterface, \IteratorAggregate, \ArrayAccess
{
    /** @var array<string, mixed> */
    private array $paginatorOptions = [];

    /** @var array<string, mixed> */
    private array $customParameters = [];

    /**
     * @param array<int, T> $items
     */
    public function __construct(
        private readonly array $items,
        private readonly int $total,
        private readonly int $page,
        private readonly int $perPage,
        private readonly string $pageParam = 'page',
    ) {
        $this->paginatorOptions = ['pageParameterName' => $this->pageParam];
    }

    public function setItems(iterable $items): void
    {
        // Read-only: no-op
    }

    public function getItems(): iterable
    {
        return $this->items;
    }

    public function setTotalItemCount(int $numTotal): void
    {
        // Read-only: no-op
    }

    public function getTotalItemCount(): int
    {
        return $this->total;
    }

    public function setCurrentPageNumber(int $pageNumber): void
    {
        // Read-only: no-op
    }

    public function getCurrentPageNumber(): int
    {
        return $this->page;
    }

    public function setItemNumberPerPage(int $numItemsPerPage): void
    {
        // Read-only: no-op
    }

    public function getItemNumberPerPage(): int
    {
        return $this->perPage;
    }

    public function setPaginatorOptions(array $options): void
    {
        $this->paginatorOptions = array_merge($this->paginatorOptions, $options);
    }

    public function getPaginatorOption(string $name): mixed
    {
        return $this->paginatorOptions[$name] ?? null;
    }

    public function setCustomParameters(array $parameters): void
    {
        $this->customParameters = $parameters;
    }

    public function getCustomParameter(string $name): mixed
    {
        return $this->customParameters[$name] ?? null;
    }

    public function getPageCount(): int
    {
        if ($this->perPage <= 0) {
            return 0;
        }
        return (int) ceil($this->total / $this->perPage);
    }

    public function getPaginationData(): array
    {
        $pageCount = $this->getPageCount();
        $current = min($this->page, max(1, $pageCount));
        $pageRange = min(5, $pageCount);

        $delta = (int) ceil($pageRange / 2);
        if ($current - $delta > $pageCount - $pageRange) {
            $pages = range(max(1, $pageCount - $pageRange + 1), $pageCount);
        } else {
            $delta = min($delta, $current);
            $offset = $current - $delta;
            $pages = range($offset + 1, min($offset + $pageRange, $pageCount));
        }

        $firstItemNumber = $this->total > 0 ? (($current - 1) * $this->perPage) + 1 : 0;
        $lastItemNumber = $firstItemNumber + count($this->items) - 1;

        return [
            'last' => $pageCount,
            'current' => $current,
            'numItemsPerPage' => $this->perPage,
            'first' => 1,
            'pageCount' => $pageCount,
            'totalCount' => $this->total,
            'pageRange' => $pageRange,
            'startPage' => min($pages),
            'endPage' => max($pages),
            'previous' => $current > 1 ? $current - 1 : null,
            'next' => $current < $pageCount ? $current + 1 : null,
            'pagesInRange' => $pages,
            'firstPageInRange' => min($pages),
            'lastPageInRange' => max($pages),
            'currentItemCount' => count($this->items),
            'firstItemNumber' => $firstItemNumber,
            'lastItemNumber' => $lastItemNumber,
        ];
    }

    /**
     * Query params for pagination links (e.g. ['page' => 1]).
     */
    public function getParams(): array
    {
        return [$this->pageParam => $this->page];
    }

    public function getRoute(): ?string
    {
        return null;
    }

    public function isSorted(array|string|null $key = null, array $params = []): bool
    {
        return false;
    }

    public function getPaginatorOptions(): ?array
    {
        return $this->paginatorOptions;
    }

    public function getCustomParameters(): ?array
    {
        return $this->customParameters ?: null;
    }

    public function getTemplate(): ?string
    {
        return '@KnpPaginator/Pagination/bootstrap_v5_pagination.html.twig';
    }

    public function getRelLinksTemplate(): ?string
    {
        return null;
    }

    public function getSortableTemplate(): ?string
    {
        return null;
    }

    public function getFiltrationTemplate(): ?string
    {
        return null;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Read-only: no-op
    }

    public function offsetUnset(mixed $offset): void
    {
        // Read-only: no-op
    }
}
