<x-guest-layout>
    <div class="max-w-md mx-auto sm:max-w-lg mt-6 p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Contacto de Soporte</h2>

        @if (session('status'))
            <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}">
            @csrf

            <div style="position: absolute; left: -9999px; top: -9999px;" aria-hidden="true">
                <label for="website">Sitio web corporativo (No llenar)</label>
                <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
            </div>

            <div>
                <x-input-label for="name" value="Nombre" />
                <x-text-input id="name" class="block mt-1 w-full" name="name" type="text" :value="old('name')" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="email" value="Correo Electrónico" />
                <x-text-input id="email" class="block mt-1 w-full" name="email" type="email" :value="old('email')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="message" value="Mensaje" />
                <textarea id="message" name="message" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('message') }}</textarea>
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="captcha" value="Código de verificación" />
                <div class="flex items-center gap-3 mt-1 mb-2">
                    <img src="{{ captcha_src('default') }}" id="contact-captcha-img" class="border rounded" />
                    <button type="button"
                            onclick="document.getElementById('contact-captcha-img').src='{{ captcha_src('default') }}?'+Math.random()"
                            class="text-sm text-indigo-600 underline">
                        Recargar código
                    </button>
                </div>
                <x-text-input id="captcha" class="block w-full" name="captcha" type="text" required autocomplete="off" />
                <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button>Enviar Mensaje</x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
