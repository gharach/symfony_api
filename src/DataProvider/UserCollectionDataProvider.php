<?php

namespace App\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class UserCollectionDataProvider implements ProviderInterface
{
    private ManagerRegistry $managerRegistry;
    private Security $security;

    public function __construct(ManagerRegistry $managerRegistry, Security $security)
    {die();
        $this->managerRegistry = $managerRegistry;
        $this->security = $security;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {die();
        $userRepository = $this->managerRegistry->getRepository(User::class);

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return $userRepository->findAll();
        }

        if ($this->security->isGranted('ROLE_COMPANY_ADMIN')) {
            $userCompany = $this->security->getUser()->getCompany();
            return $userRepository->findBy(['company' => $userCompany]);
        }

        return [];
    }
}
