<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Formulario de Contacto</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Envíanos tus comentarios de forma segura.
        </p>
    </div>

    @if (session('status'))
        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-100" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form id="contact-form" method="POST" action="{{ route('contact.store') }}" class="space-y-4">
        @csrf

        <input type="hidden" id="recaptcha-token" name="g-recaptcha-response">

        <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
            <label for="website">Sitio web (no llenar)</label>
            <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
        </div>

        <div>
            <x-input-label for="name" value="Nombre Completo" />
            <x-text-input id="name" class="block mt-1 w-full" name="name" type="text" :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="Correo Electrónico" />
            <x-text-input id="email" class="block mt-1 w-full" name="email" type="email" :value="old('email')" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="message" value="Mensaje" />
            <textarea id="message" name="message" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('message') }}</textarea>
            <x-input-error :messages="$errors->get('message')" class="mt-2" />
        </div>

        @error('g-recaptcha-response')
            <div class="p-3 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-100" role="alert">
                {{ $message }}
            </div>
        @enderror

        <div class="flex items-center justify-end mt-6">
            <x-primary-button id="submit-btn" class="w-full justify-center py-2.5">
                Enviar Mensaje
            </x-primary-button>
        </div>
    </form>

    @push('scripts')
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    <script>
        document.getElementById('contact-form').addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Verificando seguridad...';

            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'contact'})
                    .then(function(token) {
                        document.getElementById('recaptcha-token').value = token;
                        document.getElementById('contact-form').submit();
                    })
                    .catch(function(error) {
                        console.error('Error durante la ejecución de reCAPTCHA:', error);
                        alert('Ocurrió un error al verificar reCAPTCHA. Inténtalo de nuevo.');
                        submitBtn.disabled = false;
                        submitBtn.innerText = 'Enviar Mensaje';
                    });
            });
        });
    </script>
    @endpush
</x-guest-layout>
