<?php

namespace App\Tests\App\Trait;

use App\Trait\SortableTrait;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use PHPUnit\Framework\TestCase;

class SortableTraitTest extends TestCase
{
    use SortableTrait;

    private QueryBuilder $queryBuilder;
    private Expr $expr;

    protected function setUp(): void
    {
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->expr = $this->createMock(Expr::class);
    }

    public function testApplySortingWithValidField(): void
    {
        $sorts = ['name' => 'ASC'];

        $this->queryBuilder->expects($this->once())
            ->method('addOrderBy')
            ->with('i.name', 'ASC');

        $this->applySorting($this->queryBuilder, $sorts);
    }

    public function testApplySortingWithMultipleFields(): void
    {
        $sorts = [
            'name' => 'ASC',
            'quantity' => 'DESC'
        ];

        $this->queryBuilder->expects($this->exactly(2))
            ->method('addOrderBy')
            ->withConsecutive(
                ['i.name', 'ASC'],
                ['i.quantity', 'DESC']
            );

        $this->applySorting($this->queryBuilder, $sorts);
    }

    public function testApplySortingWithInvalidField(): void
    {
        $sorts = ['invalid_field' => 'ASC'];

        // Should not call addOrderBy for invalid fields
        $this->queryBuilder->expects($this->never())
            ->method('addOrderBy');

        $this->applySorting($this->queryBuilder, $sorts);
    }

    public function testApplySortingWithInvalidDirection(): void
    {
        $sorts = ['name' => 'INVALID'];

        // Should call addOrderBy with default ASC direction
        $this->queryBuilder->expects($this->once())
            ->method('addOrderBy')
            ->with('i.name', 'ASC');

        $this->applySorting($this->queryBuilder, $sorts);
    }

    public function testApplySortingWithEmptySorts(): void
    {
        $sorts = [];

        // Should call orderBy with default sorting when no sorts provided
        $this->queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('i.id', 'ASC');

        $this->applySorting($this->queryBuilder, $sorts);
    }

    public function testApplySortingWithMixedValidAndInvalid(): void
    {
        $sorts = [
            'name' => 'ASC',
            'invalid_field' => 'DESC',
            'quantity' => 'INVALID'
        ];

        // Should call addOrderBy for valid fields (name and quantity with default ASC)
        $this->queryBuilder->expects($this->exactly(2))
            ->method('addOrderBy')
            ->withConsecutive(
                ['i.name', 'ASC'],
                ['i.quantity', 'ASC'] // Invalid direction defaults to ASC
            );

        $this->applySorting($this->queryBuilder, $sorts);
    }

    public function testApplySortingWithCaseInsensitiveDirection(): void
    {
        $sorts = [
            'name' => 'asc',
            'quantity' => 'desc'
        ];

        $this->queryBuilder->expects($this->exactly(2))
            ->method('addOrderBy')
            ->withConsecutive(
                ['i.name', 'ASC'],
                ['i.quantity', 'DESC']
            );

        $this->applySorting($this->queryBuilder, $sorts);
    }

    public function testGetSortableFields(): void
    {
        $sortableFields = $this->getAllowedSortFields();

        $this->assertIsArray($sortableFields);
        $this->assertContains('id', $sortableFields);
        $this->assertContains('name', $sortableFields);
        $this->assertContains('quantity', $sortableFields);
        $this->assertContains('date_add', $sortableFields);
        $this->assertContains('date_upd', $sortableFields);
    }

    public function testGetDefaultSortField(): void
    {
        $defaultSortField = $this->getDefaultSortField();

        $this->assertIsString($defaultSortField);
        $this->assertEquals('id', $defaultSortField);
    }
} 