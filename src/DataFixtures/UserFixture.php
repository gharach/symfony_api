<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create 5 companies
        $companies = [];
        for ($i = 1; $i <= 5; $i++) {
            $company = new Company();
            $company->setName($faker->unique()->company());
            $manager->persist($company);
            $companies[] = $company; // Keep track of the created companies
        }

        // Create 1 Super Admin (No company assigned)
        $superAdmin = new User();
        $superAdmin->setName('Super Admin');
        $superAdmin->setRole('ROLE_SUPER_ADMIN');
        $superAdmin->setCompany(null); // Super Admin has no company assigned
        $superAdmin->setEmail('superadmin@admin.com');
        $hashedPassword = $this->passwordHasher->hashPassword($superAdmin, 'admin123');
        $superAdmin->setPassword($hashedPassword);
        $manager->persist($superAdmin);

        // Create 1 Company Admin (Assigned to the first company)
        $companyAdmin = new User();
        $companyAdmin->setName('Company Admin');
        $companyAdmin->setRole('ROLE_COMPANY_ADMIN');
        $companyAdmin->setCompany($companies[0]); // Assigned to the first company
        $companyAdmin->setEmail('companyadmin@admin.com');
        $hashedPassword = $this->passwordHasher->hashPassword($companyAdmin, 'admin123');
        $companyAdmin->setPassword($hashedPassword);
        $manager->persist($companyAdmin);

        // Create 10 Users with different company assignments
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setName($faker->firstName() . ' ' . $faker->lastName());
            $user->setRole('ROLE_USER');
            $user->setEmail($faker->unique()->email());
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);
            // Randomly assign the user to one of the companies
            $user->setCompany($companies[array_rand($companies)]);
            $manager->persist($user);
        }

        // Save all entities to the database
        $manager->flush();
    }
}
