<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'a@example.com']);

        $this->post('/login', ['email' => 'a@example.com', 'password' => 'salah-salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_correct_password_logs_in(): void
    {
        User::factory()->create(['email' => 'a@example.com', 'password' => 'password123']);

        $this->post('/login', ['email' => 'a@example.com', 'password' => 'password123'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_password_is_stored_hashed(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->assertNotSame('password123', $user->password);
    }
}
