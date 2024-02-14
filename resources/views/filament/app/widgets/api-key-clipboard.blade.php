@php
    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget class="fi-api-key-clipboard-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="relative flex justify-center items-center w-10 h-10 bg-gray-100 rounded-full dark:bg-gray-600">
                <x-filament::icon icon="heroicon-o-code-bracket" class="h-4 w-4" />
            </div>

            <div class="flex-1">
                <h2 class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    Your API Key
                </h2>

                <p id="api-key" class="text-sm text-gray-500 dark:text-gray-400 w-80 overflow-ellipsis overflow-hidden">
                    {{ $user->api_key }}
                </p>
            </div>

            <x-filament::button
                color="gray"
                icon="heroicon-o-clipboard-document"
                labeled-from="sm"
                tag="button"
                class="fi-clipboard-btn"
                data-clipboard-text="{{ $user->api_key }}"
            >
                Copy
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

@pushOnce('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>
    <script>
        new ClipboardJS('.fi-clipboard-btn', {
            text: function (trigger) {
                let label = trigger.querySelector('.fi-btn-label');

                label.innerHTML = "Copied!";

                setTimeout(
                    function () {
                        label.innerHTML="Copy";
                    },
                    1000
                );

                return trigger.getAttribute('data-clipboard-text');
            }
        });
    </script>
@endPushOnce
