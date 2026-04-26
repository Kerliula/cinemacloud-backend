<?php

declare(strict_types=1);

namespace App\Http\Requests\Movie;

use App\Http\Requests\ApiRequest;

final class MovieCatalogRequest extends ApiRequest
{
    public const string FIELD_PER_PAGE = 'per_page';
    public const string FIELD_SORT_BY = 'sort_by';
    public const string FIELD_SORT_DIR = 'sort_dir';
    public const string FIELD_SEARCH = 'search';

    public function rules(): array
    {
        return [
            self::FIELD_PER_PAGE => $this->perPageRules(),
            self::FIELD_SORT_BY => $this->sortByRules(),
            self::FIELD_SORT_DIR => $this->sortDirRules(),
            self::FIELD_SEARCH => $this->searchRules(),
        ];
    }

    public function perPage(): int
    {
        return $this->validated(self::FIELD_PER_PAGE, config('api.pagination.default_per_page'));
    }

    public function sortBy(): string
    {
        return $this->validated(self::FIELD_SORT_BY, config('api.movies.sort.default'));
    }

    public function sortDirection(): string
    {
        return $this->validated(self::FIELD_SORT_DIR, config('api.movies.sort.direction'));
    }

    public function search(): ?string
    {
        return $this->validated(self::FIELD_SEARCH);
    }

    // ── Rules ────────────────────────────────────────────────────────────────

    private function perPageRules(): array
    {
        return [
            'sometimes',
            'integer',
            'min:1',
            'max:' . config('api.pagination.max_per_page'),
        ];
    }

    private function sortByRules(): array
    {
        $allowed = implode(',', config('api.movies.sort.allowed'));

        return [
            'sometimes',
            'string',
            "in:{$allowed}",
        ];
    }

    private function sortDirRules(): array
    {
        return [
            'sometimes',
            'string',
            'in:asc,desc',
        ];
    }

    private function searchRules(): array
    {
        return [
            'sometimes',
            'string',
            'min:2',
            'max:100',
        ];
    }
}
