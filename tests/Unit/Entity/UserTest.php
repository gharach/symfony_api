<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Company;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserEmailValidation()
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testUserRoleAssignment()
    {
        $user = new User();
        $user->setRole('ROLE_USER');

        $this->assertEquals('ROLE_USER', $user->getRole());
    }

    public function testUserCompanyAssignment()
    {
        $company = new Company();
        $company->setName('Test Company');

        $user = new User();
        $user->setCompany($company);

        $this->assertSame($company, $user->getCompany());
    }
}
