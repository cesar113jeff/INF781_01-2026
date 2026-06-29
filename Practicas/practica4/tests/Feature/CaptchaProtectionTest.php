<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaptchaProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_falla_sin_recaptcha_token(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('g-recaptcha-response');
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_login_falla_sin_recaptcha_token(): void
    {
        $response = $this->post('/login', [
            'email'    => 'usuario@example.com',
            'password' => 'cualquier_password',
        ]);

        $response->assertSessionHasErrors('g-recaptcha-response');
    }

    public function test_contacto_honeypot_descarta_envio_silenciosamente(): void
    {
        $response = $this->post('/contact', [
            'name'    => 'Bot Automatizado',
            'email'   => 'bot@spam.com',
            'message' => 'Este es un mensaje de spam masivo',
            'website' => 'http://spam-link.com',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseCount('contacts', 0);
    }
}
