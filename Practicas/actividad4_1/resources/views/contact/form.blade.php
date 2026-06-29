@php
    $siteKey = config('services.recaptcha.site_key');
@endphp

<x-guest-layout>
    <div class="max-w-md mx-auto sm:max-w-lg mt-6 p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Contacto</h2>

        @if (session('success'))
            <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <form id="contact-form" method="POST" action="{{ route('contact.store') }}">
            @csrf

            {{-- reCAPTCHA v2 --}}
            <div class="g-recaptcha" data-sitekey="{{ $siteKey }}"></div>

            {{-- Nombre --}}
            <div>
                <x-input-label for="nombre" value="Nombre" />
                <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required />
                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
            </div>

            {{-- Email --}}
            <div class="mt-4">
                <x-input-label for="email" value="Correo Electrónico" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Mensaje --}}
            <div class="mt-4">
                <x-input-label for="mensaje" value="Mensaje" />
                <textarea id="mensaje" name="mensaje" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('mensaje') }}</textarea>
                <x-input-error :messages="$errors->get('mensaje')" class="mt-2" />
            </div>

            {{-- Error del reCAPTCHA v3 --}}
            <div class="mt-4">
                @error('g-recaptcha-response')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Botón de envío --}}
            <div class="flex items-center justify-end mt-4">
                <x-primary-button>Enviar Mensaje</x-primary-button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endpush
</x-guest-layout>
