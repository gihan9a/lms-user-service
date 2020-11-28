<?php

declare(strict_types=1);

namespace Tests\Integration;

use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Models\User;
use Tests\TestCase;

/**
 * @covers App\Console\Kernel
 * @covers App\Exceptions\Handler
 * @covers App\Http\Controllers\Controller
 * @covers App\Http\Controllers\UserController
 * @covers App\Models\User
 * @covers App\Providers\AuthServiceProvider
 */
class UserControllerTest extends TestCase
{
    use DatabaseMigrations;

    private function getUserValidData(int $idx = 1): array
    {
        return [
            'name' => 'User' . $idx,
            'email' => 'user' . $idx . '@example.com',
            'password' => 'user' . $idx . 'pass',
            'passwordConfirm' => 'user' . $idx . 'pass',
            'role' => 'customer',
        ];
    }

    protected function createUserAndVerify(int $idx = 1): \Tests\Integration\UserControllerTest
    {
        $dataOutput = $this->getUserValidData($idx);
        $dataInput = $dataOutput;
        unset($dataOutput['password']);
        unset($dataOutput['passwordConfirm']);
        return $this->post('/', $dataInput)
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'code',
                'data',
                'errors',
            ])
            ->seeJson($dataOutput);
    }

    /**
     *
     * @return void
     */
    public function testWork()
    {
        $this->get('/');
        $this->seeStatusCode(200);
        $this->assertEquals(
            $this->app->version(),
            $this->response->getContent()
        );
    }

    /**
     *
     * @return void
     */
    public function testUserCreate()
    {
        $this->createUserAndVerify(1);

        // validate password has hashed
        $data = $this->getUserValidData(1);
        $password = $data['password'];
        unset($data['password']);
        unset($data['passwordConfirm']);
        $this->seeInDatabase('users', $data);
        $user = User::firstWhere('email', $data['email']);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->password);
        $this->assertNotEquals($password, $user->password);

        // try to create user with required attributes missing
        $data = $this->getUserValidData(3);
        // remove email and validate
        unset($data['email']);
        $this->json('POST', '/', $data)
            ->seeJson([
                'data' => null,
                'errors' => [
                    'email' => ['The email field is required.'],
                ],
                'code' => 400,
            ]);

        // try to create email with same email
        $data = $this->getUserValidData(1);
        $data['name'] = 'User2'; // just change the name
        $this->json('POST', '/', $data)
            ->seeJson([
                'code' => 400,
                'data' => null,
                'errors' => [
                    'email' => ['The email has already been taken.'],
                ],
            ]);
    }

    /**
     *
     * @return void
     */
    public function testUserUpdate(): void
    {
        $this->createUserAndVerify(1);
        $newEmail = 'user11@example.com';
        $user1data1 = [
            'name' => 'User11',
            'email' => $newEmail,
            'role' => 'csr',
        ];
        $this->json('PUT', '/1', $user1data1)
            ->seeJson($user1data1);

        // updating password and name
        $user1data2 = [
            'name' => 'User11',
            'password' => 'pass',
        ];
        $this->json('PUT', '/1', $user1data2)
            ->seeJson([
                'data' => null,
                'code' => 400,
                'errors' => [
                    'password' => [
                        'The password must be at least 6 characters.',
                    ],
                    'passwordConfirm' => [
                        'The password confirm field is required when password is present.',
                    ],
                ],
            ]);
        $user1data3 = [
            'name' => 'User11',
            'password' => 'password',
            'passwordConfirm' => 'password',
        ];
        $this->json('PUT', '/1', $user1data3)
            ->seeJson([
                'name' => 'User11',
            ]);
        // verify from db
        $user = User::find(1);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->password);
        $this->assertNotEquals($user1data3['password'], $user->password);

        $user2data = [
            'name' => 'User22',
        ];
        $this->json('PUT', '/2', $user2data)
            ->seeStatusCode(404)
            ->seeJson([
                'data' => null,
                'code' => 404,
                'errors' => [
                    'error' => ['Unable to find user 2'],
                ]
            ]);

        $this->createUserAndVerify(2);
        // fail updating the email to duplicate
        $this->json('PUT', '/2', [
            'email' => $newEmail,
        ])
            ->seeStatusCode(400)
            ->seeJson([
                'code' => 400,
                'data' => null,
                'errors' => [
                    'email' => ['The email has already been taken.'],
                ]
            ]);
        $this->assertEquals(1, User::where('email', $newEmail)->count());
        // email self updating should work

        $this->json('PUT', '/1', [
            'email' => $newEmail,
        ])
            ->seeStatusCode(200);
    }
}
