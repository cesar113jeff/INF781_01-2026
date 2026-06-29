<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

/**
 * Regla de validación para Google reCAPTCHA v3.
 *
 * Verifica el token del lado del servidor contra la API de Google
 * y evalúa el score devuelto contra el umbral configurado.
 *
 * ── ¿Por qué una clase dedicada en app/Rules?
 *    Separa la lógica de verificación del controlador, permitiendo
 *    reutilización en otros formularios y tests unitarios sin tocar
 *    la lógica HTTP.
 *
 * ── ¿Por qué reCAPTCHA v3 en lugar de v2 o mews/captcha?
 *    v3 no interrumpe al usuario con desafíos visuales. Asigna un
 *    score (0.0–1.0) basado en el comportamiento, y el servidor
 *    decide si acepta o rechaza. Mejor experiencia UX.
 */
class RecaptchaV3Rule implements ValidationRule
{
    /**
     * Validate the attribute.
     *
     * @param  string  $attribute  Nombre del campo (g-recaptcha-response)
     * @param  mixed   $value      Token generado por reCAPTCHA v3 en el cliente
     * @param  Closure $fail       Callback de Laravel para añadir errores de validación
     *
     * Pasos:
     *   1. Validar que el token no esté vacío (seguridad ante olvidos JS).
     *   2. POST a Google siteverify con secret + token + IP remota.
     *   3. Manejar errores de red (timeout, DNS, etc.).
     *   4. Validar success booleano de Google.
     *   5. Validar score numérico contra umbral configurable.
     *   6. Silencio → éxito.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // ─── 1. Token ausente ──────────────────────────────────────────
        // Si el frontend no ejecutó grecaptcha.execute() o algo falló
        // en el cliente, no hay token que verificar. Rechazamos rápido
        // sin llamar a Google.
        if (empty($value) || !is_string($value)) {
            $fail('El token CAPTCHA no está presente. Recarga la página e inténtalo de nuevo.');
            return;
        }

        // ─── 2. Llamada a la API de Google ────────────────────────────
        // Enviamos secret, response (token) y remoteip.
        // remoteip es opcional pero recomendado por Google para ligar
        // el token a la IP que generó la solicitud.
        $httpResponse = Http::asForm()->post(config('services.recaptcha.verify_url'), [
            'secret'   => config('services.recaptcha.secret_key'),
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        // ─── 3. Error de red / timeout ────────────────────────────────
        // Si el servidor no puede contactar a Google (firewall, DNS,
        // caída temporal), no podemos verificar. Mejor fallar con
        // mensaje amigable que aceptar tráfico no verificado.
        if ($httpResponse->failed() || $httpResponse->serverError()) {
            $fail('No se pudo verificar el CAPTCHA. Intenta de nuevo.');
            return;
        }

        $data = $httpResponse->json();

        // Si la respuesta no es un array válido, algo raro ocurrió.
        if (!is_array($data)) {
            $fail('No se pudo verificar el CAPTCHA. Intenta de nuevo.');
            return;
        }

        // ─── 4. Validación de Google falló (success = false) ──────────
        // Google devuelve success:false cuando el token es inválido,
        // expiró, o ya fue usado. El usuario debe regenerar el token.
        if (!($data['success'] ?? false)) {
            $fail('Verificación CAPTCHA fallida.');
            return;
        }

        // ─── 5. Score por debajo del umbral ───────────────────────────
        // reCAPTCHA v3 devuelve un score 0.0–1.0:
        //   1.0 → humano seguro
        //   0.0 → bot seguro
        // El umbral por defecto es 0.5. Se puede ajustar en .env:
        //   RECAPTCHA_MIN_SCORE=0.3  (más permisivo)
        //   RECAPTCHA_MIN_SCORE=0.7  (más restrictivo)
        // La decisión de aceptar/rechazar es SERVER-SIDE. El cliente
        // jamás decide si pasó o falló.
        $score = (float) ($data['score'] ?? 0.0);
        $minScore = config('services.recaptcha.min_score', 0.5);

        if ($score < $minScore) {
            $fail('Actividad sospechosa detectada. Intenta más tarde.');
            return;
        }

        // ─── 6. Todo OK ───────────────────────────────────────────────
        // Si llegamos aquí: HTTP 200, success=true, score >= umbral.
        // La validación pasa silenciosamente.
    }
}
