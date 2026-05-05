<?php

declare(strict_types=1);

namespace App\Http\Requests\Movie;

use App\Http\Requests\ApiRequest;

final class ListMoviesRequest extends ApiRequest
{
    public const string FIELD_PER_PAGE = 'per_page';
    public const string FIELD_PAGE = 'page';
    public const string FIELD_SORT_BY = 'sort_by';
    public const string FIELD_SORT_DIR = 'sort_dir';
    public const string FIELD_SEARCH = 'search';

    public function rules(): array
    {
        return [
            self::FIELD_PER_PAGE => $this->perPageRules(),
            self::FIELD_PAGE => $this->pageRules(),
            self::FIELD_SORT_BY => $this->sortByRules(),
            self::FIELD_SORT_DIR => $this->sortDirRules(),
            self::FIELD_SEARCH => $this->searchRules(),
        ];
    }

    public function perPage(): int
    {
        $defaultPerPage = config('api.pagination.default_per_page');

        return $this->validated(self::FIELD_PER_PAGE, $defaultPerPage);
    }

    public function page(): int
    {
        $defaultPage = 1;

        return $this->validated(self::FIELD_PAGE, $defaultPage);
    }

    public function sortBy(): string
    {
        $defaultSortBy = config('api.movies.sort.default');

        return $this->validated(self::FIELD_SORT_BY, $defaultSortBy);
    }

    public function sortDirection(): string
    {
        $defaultSortDir = config('api.movies.sort.direction');

        return $this->validated(self::FIELD_SORT_DIR, $defaultSortDir);
    }

    public function search(): ?string
    {
        return $this->validated(self::FIELD_SEARCH);
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach ([self::FIELD_PAGE, self::FIELD_PER_PAGE] as $field) {
            $value = $this->input($field);

            if (is_string($value) && is_numeric(trim($value))) {
                $normalized[$field] = (int)trim($value);
            }
        }

        $this->merge($normalized);
    }

    private function perPageRules(): array
    {
        $max = config('api.pagination.max_per_page');

        return [
            'sometimes',
            'integer',
            'min:1',
            "max:{$max}",
        ];
    }

    private function pageRules(): array
    {
        return [
            'sometimes',
            'integer',
            'min:1',
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
