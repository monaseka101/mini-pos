{{-- resources/views/filament/pages/page.blade.php --}}

<x-filament::page>
    <h1>Hello from your custom dashboard page!</h1>

    {{-- Example: Display header widgets --}}
    {{ $this->headerWidgets }}

    {{-- Or add your own HTML here --}}
    <p>Welcome to your custom Filament dashboard.</p>
</x-filament::page>
