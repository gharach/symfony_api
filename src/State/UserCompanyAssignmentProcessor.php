<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UserCompanyAssignmentProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CompanyRepository $companyRepository,
        private TokenStorageInterface $tokenStorage,
                                       #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof User) {
            $currentUser = $this->tokenStorage->getToken()->getUser();

            if (!$currentUser instanceof User) {
                throw new \LogicException('Invalid user instance.');
            }

            if (in_array('ROLE_COMPANY_ADMIN', $currentUser->getRoles(), true)) {
                $company = $currentUser->getCompany();
                $data->setCompany($company);
            }

            if ($data->getPassword()) {
                $hashedPassword = $this->passwordHasher->hashPassword($data, $data->getPassword());
                $data->setPassword($hashedPassword);
            }
        }

        // Use the persist processor to persist the data
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
