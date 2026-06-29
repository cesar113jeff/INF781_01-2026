<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CaptchaAlternativeTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_valido_con_score_alto_acepta_formulario(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score'   => 0.9,
            ], 200),
        ]);

        $response = $this->post('/contact', [
            'name'                 => 'Estudiante UATF',
            'email'                => 'estudiante@uatf.edu.bo',
            'message'              => 'Hola, este mensaje debería pasar sin problemas.',
            'g-recaptcha-response' => 'token-valido-score-alto',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Tu mensaje fue enviado correctamente.');
    }

    public function test_score_bajo_rechaza_formulario(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score'   => 0.2,
            ], 200),
        ]);

        $responseWeb = $this->post('/contact', [
            'name'                 => 'Bot Atacante',
            'email'                => 'bot@spammer.com',
            'message'              => 'Intento de envío automático con un script.',
            'g-recaptcha-response' => 'token-bajo-score',
        ]);
        $responseWeb->assertSessionHasErrors('g-recaptcha-response');

        $responseJson = $this->post('/contact', [
            'name'                 => 'Bot Atacante',
            'email'                => 'bot@spammer.com',
            'message'              => 'Intento de envío automático con un script.',
            'g-recaptcha-response' => 'token-bajo-score',
        ], [
            'Accept' => 'application/json',
        ]);
        $responseJson->assertStatus(422);
    }

    public function test_token_ausente_rechaza_formulario(): void
    {
        $responseWeb = $this->post('/contact', [
            'name'    => 'Usuario Olvidadizo',
            'email'   => 'olvido@example.com',
            'message' => 'Olvidé enviar el token de recaptcha.',
        ]);
        $responseWeb->assertSessionHasErrors('g-recaptcha-response');

        $responseJson = $this->post('/contact', [
            'name'    => 'Usuario Olvidadizo',
            'email'   => 'olvido@example.com',
            'message' => 'Olvidé enviar el token de recaptcha.',
        ], [
            'Accept' => 'application/json',
        ]);
        $responseJson->assertStatus(422);
    }
}
