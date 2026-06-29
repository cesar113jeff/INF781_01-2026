<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CaptchaAlternativeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_token_valido_con_score_alto_acepta_formulario(): void
    {
        Http::fake([
            'google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score'   => 0.9,
                'action'  => 'contact',
            ]),
        ]);

        $response = $this->post('/contact', [
            'nombre'               => 'Usuario Prueba',
            'email'                => 'test@example.com',
            'mensaje'              => 'Este es un mensaje de prueba para verificar el CAPTCHA.',
            'g-recaptcha-response' => 'token-fake-valido',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function test_score_bajo_rechaza_formulario(): void
    {
        Http::fake([
            'google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score'   => 0.2,
                'action'  => 'contact',
            ]),
        ]);

        $response = $this->post('/contact', [
            'nombre'               => 'Usuario Sospechoso',
            'email'                => 'sospechoso@example.com',
            'mensaje'              => 'Intento de envío con score bajo.',
            'g-recaptcha-response' => 'token-score-bajo',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('g-recaptcha-response');
    }

    /** @test */
    public function test_token_ausente_rechaza_formulario(): void
    {
        $response = $this->post('/contact', [
            'nombre'  => 'Usuario Sin Token',
            'email'   => 'sin-token@example.com',
            'mensaje' => 'Mensaje sin token CAPTCHA.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('g-recaptcha-response');
    }

}
