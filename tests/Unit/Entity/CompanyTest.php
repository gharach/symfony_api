<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Company;
use PHPUnit\Framework\TestCase;

class CompanyTest extends TestCase
{
    public function testCompanyName()
    {
        $company = new Company();
        $company->setName('Example Company');

        $this->assertEquals('Example Company', $company->getName());
    }
}
