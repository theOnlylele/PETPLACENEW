<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@if(auth()->user()->role === 'vet')
  <h1>Welcome Dr. {{ auth()->user()->first_name }}! 🩺</h1>
  <!-- Vet content here -->
@else
  <h1>Welcome {{ auth()->user()->first_name }}! 🐶</h1>
  <!-- Pet Owner content here -->
@endif
