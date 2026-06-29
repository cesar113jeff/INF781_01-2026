<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Clase RecaptchaV3Rule
 * 
 * Implementa la interfaz ValidationRule de Laravel 13 para realizar
 * la verificación de seguridad de reCAPTCHA v3 mediante una solicitud HTTP POST.
 */
class RecaptchaV3Rule implements ValidationRule
{
    /**
     * Ejecuta la validación de la regla de reCAPTCHA v3.
     *
     * @param  string  $attribute Nombre del campo que se valida (p.ej. 'g-recaptcha-response').
     * @param  mixed  $value El valor enviado desde el frontend (el token de reCAPTCHA).
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 1. Obtener la clave secreta desde config/services.php
        // Esto permite mantener la credencial segura fuera de los archivos del repositorio.
        $secret = config('services.recaptcha.secret');

        // 2. Realizar POST con el cliente HTTP nativo de Laravel.
        // Google requiere que los datos se envíen codificados como formulario (x-www-form-urlencoded),
        // por lo que usamos ->asForm() y definimos un límite de tiempo de espera (timeout) de 5 segundos
        // para prevenir que una falla de red con los servidores de Google cuelgue nuestra aplicación.
        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret'   => $secret,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);
        } catch (\Exception $e) {
            // Decisión de diseño: Si ocurre un timeout o error de red (excepción),
            // registramos el log de error internamente para depuración técnica y llamamos
            // a $fail con un mensaje amigable al usuario que no exponga detalles del backend.
            Log::error('Error de red al conectar con Google reCAPTCHA: ' . $e->getMessage());
            $fail('No se pudo verificar el CAPTCHA. Intenta de nuevo.');
            return;
        }

        // 3. Verificar si el servidor de Google retornó un código de respuesta HTTP fallido (no-2xx)
        if ($response->failed()) {
            Log::error('Google reCAPTCHA retornó código de estado HTTP no exitoso: ' . $response->status());
            $fail('No se pudo verificar el CAPTCHA. Intenta de nuevo.');
            return;
        }

        // Obtener el cuerpo de la respuesta en formato JSON decodificado.
        $result = $response->json();

        // 4. Evaluar la propiedad 'success' devuelta por la API de Google.
        // Si 'success' es false, significa que el token no es válido, expiró o ya fue usado.
        if (!isset($result['success']) || $result['success'] === false) {
            Log::warning('Verificación del token reCAPTCHA denegada por Google.', [
                'error-codes' => $result['error-codes'] ?? []
            ]);
            $fail('Verificación CAPTCHA fallida.');
            return;
        }

        // 5. Recuperar la puntuación (score) calculada por Google (rango de 0.0 a 1.0).
        // Compararla contra el puntaje mínimo configurado en services.recaptcha.min_score.
        $minScore = (float) config('services.recaptcha.min_score', 0.5);
        $score = isset($result['score']) ? (float) $result['score'] : 1.0;

        // Si el score es inferior al umbral, se presume comportamiento de bot/automatizado.
        if ($score < $minScore) {
            Log::warning('Token reCAPTCHA con score inferior al umbral mínimo permitido.', [
                'score'     => $score,
                'min_score' => $minScore,
                'ip'        => request()->ip()
            ]);
            $fail('Actividad sospechosa detectada. Intenta más tarde.');
        }
    }
}
