<?php

declare(strict_types=1);

/**
 * @covers App\Http\Controllers\Controller
 * @covers App\Http\Controllers\UserController
 * @covers App\Models\User
 * @covers App\Providers\AuthServiceProvider
 */
class UserTest extends TestCase
{
    /**
     *
     * @return void
     */
    public function testWork()
    {
        $this->get('/');
        $this->seeStatusCode(200);
        $this->assertEquals(
            $this->app->version(), $this->response->getContent()
        );
    }

    /**
     *
     * @return void
     */
    public function testUserCreate()
    {
        $this->post('/', [
            'name' => 'User1',
            'email' => 'user1@example.com',
            'password' => 'user1',
            'passwordConfirm' => 'user1',
            'role' => 'customer',
        ]);
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'role',
                'created_at',
                'updated_at',
            ],
            'error',
        ]);
    }

    /**
     * @depends testUserCreate
     *
     * @return void
     */
    public function testUserUpdate(): void
    {
        $this->json('PUT', '/1', [
            'name' => 'User2',
            'email' => 'user2@example.com',
            'role' => 'csr',
        ])
        ->seeJson([
            'name' => 'User2',
            'email' => 'user2@example.com',
            'role' => 'csr',
        ]);
    }
}
