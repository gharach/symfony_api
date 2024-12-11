<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    /**
     * Create a new user.
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $passwordHasher
     * @param CompanyRepository $companyRepository
     * @param User $currentUser
     * @param LoggerInterface $logger
     *
     * @return Response
     */
    #[Route('/api/users', name: 'create_user', methods: ['POST'])]
    public function createUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        CompanyRepository $companyRepository,
        #[CurrentUser] User $currentUser,
        LoggerInterface $logger
    ): Response {
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['error' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setRole($data['role']);

        // Hash the password
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Set company for ROLE_COMPANY_ADMIN
        if ($currentUser->getRole() === 'ROLE_COMPANY_ADMIN') {
            if ($data['role'] !== 'ROLE_USER') {
                return $this->json(['error' => 'ROLE_COMPANY_ADMIN can only create users with ROLE_USER.'], Response::HTTP_FORBIDDEN);
            }
            $user->setCompany($currentUser->getCompany());
        } elseif ($currentUser->getRole() === 'ROLE_SUPER_ADMIN') {
            if (!empty($data['company_id'])) {
                $company = $companyRepository->find($data['company_id']);
                if (!$company) {
                    return $this->json(['error' => 'Invalid company ID'], Response::HTTP_BAD_REQUEST);
                }
                $user->setCompany($company);
            }
        } else {
            return $this->json(['error' => 'Access denied.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $this->validateUser($user, $currentUser, $logger);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->json(['message' => 'User created successfully'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $logger->error('User creation failed', ['error' => $e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    /**
     * Get all users.
     *
     * @param UserRepository $userRepository
     * @param User $currentUser
     *
     * @return Response
     */
    #[Route('/api/users', name: 'get_users', methods: ['GET'])]
    public function getUsers(
        UserRepository $userRepository,
                       #[CurrentUser] User $currentUser
    ): Response {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $users = $userRepository->findAllUsers();
        } elseif ($this->isGranted('ROLE_COMPANY_ADMIN')) {
            $users = $userRepository->findUsersByCompany($currentUser->getCompany()->getId());
        } elseif ($this->isGranted('ROLE_USER')) {
            $users = [$userRepository->findUserById($currentUser->getId())];
        } else {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        return $this->json($users, Response::HTTP_OK);
    }

    /**
     * Get a specific user by ID.
     *
     * @param int $id
     * @param UserRepository $userRepository
     * @param User $currentUser
     *
     * @return Response
     */
    #[Route('/api/users/{id}', name: 'get_user_by_id', methods: ['GET'])]
    public function getUserById(
        int $id,
        UserRepository $userRepository,
        #[CurrentUser] User $currentUser
    ): Response {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // ROLE_SUPER_ADMIN can view all users
        if ($currentUser->getRole() === 'ROLE_SUPER_ADMIN') {
            return $this->json($this->transformUser($user), Response::HTTP_OK);
        }

        // ROLE_COMPANY_ADMIN can view users within their company
        if ($currentUser->getRole() === 'ROLE_COMPANY_ADMIN') {
            if ($user->getCompany() === $currentUser->getCompany()) {
                return $this->json($this->transformUser($user), Response::HTTP_OK);
            } else {
                return new JsonResponse(
                    ['error' => 'You do not have permission to view users from other companies.'],
                    Response::HTTP_FORBIDDEN
                );
            }
        }

        // ROLE_USER can only view their own data
        if ($currentUser->getRole() === 'ROLE_USER' && $currentUser->getId() === $user->getId()) {
            return $this->json($this->transformUser($user), Response::HTTP_OK);
        }

        throw new AccessDeniedException('You do not have permission to view this user.');
    }
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'company' => $user->getCompany() ? [
                'id' => $user->getCompany()->getId(),
                'name' => $user->getCompany()->getName(),
            ] : null,
        ]);
    }
    /**
     * Transform the User entity into an array with the necessary fields.
     *
     * @param User $user
     * @return array
     */
    private function transformUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'company' => $user->getCompany() ? [
                'id' => $user->getCompany()->getId(),
                'name' => $user->getCompany()->getName()
            ] : null,
        ];
    }
    /**
     * Delete an existing user.
     *
     * @param int $id
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    #[Route('/api/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }

    /**
     * Validate user constraints.
     *
     * @param User $user
     * @param User $currentUser
     * @param LoggerInterface $logger
     *
     * @throws \Exception
     */
    private function validateUser(User $user, User $currentUser, LoggerInterface $logger): void
    {
        $logger->info('validateUser called', ['current_user' => $currentUser->getEmail()]);

        // Role constraints
        if ($currentUser->getRole() === 'ROLE_COMPANY_ADMIN' && $user->getRole() !== 'ROLE_USER') {
            $logger->error('ROLE_COMPANY_ADMIN can only assign ROLE_USER.');
            throw new \Exception('ROLE_COMPANY_ADMIN can only assign ROLE_USER.');
        }

        // Company constraints
        if (in_array($user->getRole(), ['ROLE_USER', 'ROLE_COMPANY_ADMIN']) && !$user->getCompany()) {
            $logger->error('Company is required for ROLE_USER and ROLE_COMPANY_ADMIN.');
            throw new \Exception('Company is required for ROLE_USER and ROLE_COMPANY_ADMIN.');
        }

        if ($user->getRole() === 'ROLE_SUPER_ADMIN' && $user->getCompany()) {
            $logger->error('ROLE_SUPER_ADMIN cannot have a company.');
            throw new \Exception('ROLE_SUPER_ADMIN cannot have a company.');
        }
    }
}
