<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Find all users visible to ROLE_SUPER_ADMIN.
     *
     * @return User[]
     */
    public function findAllUsers(): array
    {
        return $this->findAll();
    }

    /**
     * Find a user by their ID for ROLE_USER.
     *
     * @param int $userId
     * @return User|null
     */
    public function findUserById(int $userId): ?User
    {
        return $this->find($userId);
    }

    /**
     * Find all users within the same company for ROLE_COMPANY_ADMIN.
     *
     * @param int $companyId
     * @return User[]
     */
    public function findUsersByCompany(int $companyId): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.company = :companyId')
            ->setParameter('companyId', $companyId)
            ->getQuery()
            ->getResult();
    }
}
