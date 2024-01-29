<div class="space-y-6">
    <div>
        <form wire:submit="create" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end">
                {{ $this->submitAction }}
            </div>
        </form>

        <x-filament-actions::modals />
    </div>

    <div wire:loading class="w-full">
        <x-filament::section>
            <div class="flex space-x-2 align-middle justify-center">
                <x-filament::loading-indicator class="w-6 h-6"></x-filament::loading-indicator>
                <div>Scrapping in progress</div>
            </div>
        </x-filament::section>
    </div>


    <div x-data="{ show: @entangle('showResponse').live }" x-show="show" wire:loading.remove>

        <x-filament::section>
            <x-slot:heading>
                Status: {{ $this->response['status'] . ' ' . $this->response['reason'] }}
            </x-slot:heading>

            <pre class="overflow-auto">@json($this->response['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)</pre>

        </x-filament::section>
    </div>
</div>
