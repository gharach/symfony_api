<?php

namespace App\Tests\Integration\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserApiTest extends ApiTestCase
{
    private ?string $superAdminToken = null;
    private ?string $companyAdminToken = null;
    private ?string $userToken = null;

    protected function setUp(): void
    {
        $this->superAdminToken = $this->getTokenForUser('superadmin@admin.com', 'admin123');
        $this->companyAdminToken = $this->getTokenForUser('companyadmin@admin.com', 'admin123');
        $this->userToken = $this->getTokenForUser('mylene.goodwin@metz.info', 'password123');
    }

    /**
     * Helper method to authenticate a user and retrieve a token.
     */
    private function getTokenForUser(string $email, string $password): string
    {
        $client = static::createClient();

        $response = $client->request('POST', '/api/login_check', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray();
        return $data['token'];
    }

    public function testSuperAdminCanGetAllUsers(): void
    {
        $client = static::createClient();

        $response = $client->request('GET', '/api/users', [
            'headers' => ['Authorization' => 'Bearer ' . $this->superAdminToken],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJson($response->getContent());
    }

    public function testCompanyAdminCanCreateUserWithinCompany(): void
    {
        $client = static::createClient();
        $uniqueEmail = 'newtestuser' . random_int(1000, 9999) . '@company.com';

        // Send the POST request to create a new user
        $response = $client->request('POST', '/api/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->companyAdminToken,
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'name' => 'Ali',
                'email' => $uniqueEmail,
                'role' => 'ROLE_USER',
                'password' => 'P@assword123asdadsasd',
                'company_id' => 74,
            ],
        ]);

        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Decode the response content for further assertions
        $responseContent = $response->toArray();

        // Additional assertions to verify the response contains expected data
        $this->assertArrayHasKey('id', $responseContent);
        $this->assertSame($uniqueEmail, $responseContent['email']);
        $this->assertSame('Ali', $responseContent['name']);
        $this->assertSame('ROLE_USER', $responseContent['role']);

    }



    public function testCompanyAdminCanViewOnlyCompanyUsers(): void
    {
        $client = static::createClient();

        $response = $client->request('GET', '/api/users', [
            'headers' => ['Authorization' => 'Bearer ' . $this->companyAdminToken],
        ]);


        $this->assertResponseIsSuccessful();

        $users = $response->toArray();

        // Ensure $users is an array
        $this->assertIsArray($users, 'The response should be an array.');

        $this->assertArrayHasKey('hydra:member', $users, 'The response should have a hydra:member key.');

        $members = $users['hydra:member'];

        $this->assertIsArray($members, 'The hydra:member key should contain an array of users.');

        foreach ($members as $user) {
            $this->assertArrayHasKey('company', $user, 'Each user should have a company key.');
            $this->assertEquals('/api/companies/69', $user['company'], 'Company Admin should only see users from their company.');
        }
    }


//    public function testCompanyAdminCanViewOnlyCompanyUsers(): void
//    {
//        $client = static::createClient();
//
//        // Fetch the authenticated company admin's details to get their company ID
//        $response = $client->request('GET', '/api/me', [
//            'headers' => ['Authorization' => 'Bearer ' . $this->companyAdminToken],
//        ]);
//
//        $this->assertResponseIsSuccessful();
//
//        $companyAdminData = $response->toArray();
//        $companyId = $companyAdminData['company']['id'];
//
//        // Now fetch the users list
//        $response = $client->request('GET', '/api/users', [
//            'headers' => ['Authorization' => 'Bearer ' . $this->companyAdminToken],
//        ]);
//
//        $this->assertResponseIsSuccessful();
//
//        $users = $response->toArray();
//        foreach ($users as $user) {
//            $this->assertArrayHasKey('company', $user);
//            $this->assertEquals(
//                $companyId,
//                $user['company']['id'],
//                'Company Admin should only see users from their company.'
//            );
//        }
//    }

    public function testCompanyAdminCannotDeleteUser(): void
    {
        $client = static::createClient();

        $response = $client->request('DELETE', '/api/users/5', [
            'headers' => ['Authorization' => 'Bearer ' . $this->companyAdminToken],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUserCannotDeleteOtherUsers(): void
    {
        $client = static::createClient();

        $response = $client->request('DELETE', '/api/users/5', [
            'headers' => ['Authorization' => 'Bearer ' . $this->userToken],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
