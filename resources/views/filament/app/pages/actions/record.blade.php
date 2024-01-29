<x-filament::section>
    <x-slot:heading>
        Status: {{ $response->status() . ' ' . $response->reason() }}
    </x-slot:heading>

    <pre class="overflow-auto">@json($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)</pre>

</x-filament::section>
