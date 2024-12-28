<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Welcome to EncryptGo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold mb-4">Welcome to EncryptGo!</h3>
                    <p class="text-lg mb-4">We provide secure file storage with AES-CBC encryption to ensure your data stays safe.</p>
                    <p class="mb-4">You can upload your files, and we'll encrypt them using advanced AES-CBC encryption before storing them securely.</p>
                    <p class="mb-4">We also allow you to decrypt your files whenever you need them, ensuring full control over your sensitive information.</p>
                    <div class="mt-6 space-x-4">
                        <a href="/upload/encrypt" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-300">Encrypt File</a>
                        <a href="/upload/decrypt" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 transition duration-300">Decrypt File</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
