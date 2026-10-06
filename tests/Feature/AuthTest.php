<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@mantraheal.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function test_user_can_login_with_trimmed_whitespace(): void
    {
        $response = $this->post('/login', [
            'email' => ' admin@mantraheal.com ',
            'password' => 'password123 ',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@mantraheal.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
