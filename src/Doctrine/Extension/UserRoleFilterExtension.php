<?php

// src/Doctrine/Extension/UserRoleFilterExtension.php

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;

class UserRoleFilterExtension implements QueryCollectionExtensionInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        // Only apply this extension to the User entity
        if ($resourceClass !== User::class) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user) {
            return;
        }

        // If the user has ROLE_COMPANY_ADMIN, filter users by the company they belong to
        if (in_array('ROLE_COMPANY_ADMIN', $user->getRoles())) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->andWhere("$rootAlias.company = :company")
                ->setParameter('company', $user->getCompany());
        }

        // Super Admins (ROLE_SUPER_ADMIN) can see all users, no filtering needed
    }
}
