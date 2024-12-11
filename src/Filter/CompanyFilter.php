<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Metadata\Operation;
use Doctrine\Persistence\ManagerRegistry;
use ApiPlatform\Metadata\FilterInterface;

class CompanyFilter extends AbstractFilter implements FilterInterface
{
    /**
     * SearchFilter constructor.
     * @param array|null $properties
     */
    public function __construct(array $properties = null)
    {
        $this->properties = ['company'];
    }

    protected function filterProperty(
        string $property,
               $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        // Filter by the 'company' property
        if ($property === 'company' && $value !== null) {
            $rootAlias = $queryBuilder->getRootAliases()[0];

            // Apply the filter by matching the company ID
            $queryBuilder
                ->andWhere("{$rootAlias}.company = :company")
                ->setParameter('company', $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {die();
        return [
            'company' => [
                'property' => 'company',
                'type' => 'integer',  // or string depending on the type of the company identifier
                'description' => 'Filter by the company ID the user belongs to.',
                'required' => false,
            ],
        ];
    }
}
