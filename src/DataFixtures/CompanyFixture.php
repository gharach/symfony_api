<?php

namespace App\DataFixtures;

use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CompanyFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $manager->flush();
    }
}
