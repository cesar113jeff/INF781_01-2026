<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaptchaProtectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_register_falla_sin_recaptcha_token(): void
    {
        $response = $this->post('/register', [
            'name' => 'Cesar Test',
            'email' => 'cesar@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('g-recaptcha-response');
        $this->assertDatabaseMissing('users', ['email' => 'cesar@example.com']);
    }

    /** @test */
    public function test_login_falla_sin_captcha(): void
    {
        $response = $this->post('/login', [
            'email' => 'estudiante@potosi.com',
            'password' => 'secret_pass',
        ]);

        $response->assertSessionHasErrors('captcha');
    }

    /** @test */
    public function test_contacto_honeypot_descarta_envio_silenciosamente(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Spam Bot Malicioso',
            'email' => 'bot@ataque.com',
            'message' => 'Venta de software publicitario masivo sin control.',
            'website' => 'http://spam-atacker.ru',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseCount('contacts', 0);
    }
}
