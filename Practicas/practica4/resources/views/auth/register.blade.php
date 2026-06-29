<x-guest-layout>
    <form id="register-form" method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <input type="hidden" id="recaptcha-token" name="g-recaptcha-response">

        @error('g-recaptcha-response')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4" id="register-btn">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    @push('scripts')
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
        <script>
            document.getElementById('register-form').addEventListener('submit', function (e) {
                e.preventDefault();
                const btn = document.getElementById('register-btn');
                btn.disabled = true;
                btn.innerText = 'Verificando...';

                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'register'})
                        .then(function(token) {
                            document.getElementById('recaptcha-token').value = token;
                            e.target.submit();
                        })
                        .catch(function() {
                            alert('Error al verificar reCAPTCHA. Inténtalo de nuevo.');
                            btn.disabled = false;
                            btn.innerText = 'Register';
                        });
                });
            });
        </script>
    @endpush
</x-guest-layout>
