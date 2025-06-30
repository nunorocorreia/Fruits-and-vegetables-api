<?php

namespace App\Trait;

use Doctrine\ORM\QueryBuilder;

trait SortableTrait
{
    /**
     * Apply sorting to a QueryBuilder
     */
    protected function applySorting(QueryBuilder $queryBuilder, array $sorts, string $alias = 'i'): void
    {
        foreach ($sorts as $field => $direction) {
            $direction = strtoupper($direction);
            if (!in_array($direction, ['ASC', 'DESC'])) {
                $direction = 'ASC'; // Default to ASC if invalid direction
            }

            $allowedFields = $this->getAllowedSortFields();
            if (in_array($field, $allowedFields)) {
                $queryBuilder->addOrderBy($alias . '.' . $field, $direction);
            }
        }

        // Default sorting if no sorts specified
        if (empty($sorts)) {
            $queryBuilder->orderBy($alias . '.' . $this->getDefaultSortField(), 'ASC');
        }
    }

    /**
     * Get the allowed fields for sorting
     */
    protected function getAllowedSortFields(): array
    {
        return ['id', 'name', 'quantity', 'date_add', 'date_upd'];
    }

    /**
     * Get the default sort field
     */
    protected function getDefaultSortField(): string
    {
        return 'id';
    }
}
