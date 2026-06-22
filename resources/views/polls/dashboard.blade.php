<x-vue-app-layout>
    <x-slot:scripts>
        @vite(['resources/js/poll-dashboard.js'])
    </x-slot>
    <x-slot:title>
        Sondages
    </x-slot>
    <div id="app" data-props="{{ $props }}"></div> <!-- JSON props -->
</x-vue-app-layout>
