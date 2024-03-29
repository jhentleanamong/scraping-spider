<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\ScraperService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RequestBuilder extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    // Indicates if the response should be shown
    public bool $showResponse = false;

    // Stores the response data
    public ?array $data = [];

    // Stores the response data
    public array $response = [
        'data' => null,
        'status' => null,
        'reason' => null,
    ];

    /**
     * Initialize the form with default values.
     */
    public function mount(): void
    {
        $this->form->fill();
    }

    /**
     * Define the structure and fields of the form.
     *
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    ToggleButtons::make('async')
                        ->label('Method Type')
                        ->options([
                            false => 'Synchronous',
                            true => 'Asynchronous',
                        ])
                        ->colors([
                            false => 'danger',
                            true => 'success',
                        ])
                        ->default(false)
                        ->required()
                        ->inline(),

                    // Textarea::make('urls')
                    //     ->label('Input URLs')
                    //     ->helperText(
                    //         "Insert one or more URLs and separate each one by pressing 'Enter' or 'Return'."
                    //     )
                    //     ->required()
                    //     ->default(
                    //         "https://toscrape.com/\nhttps://quotes.toscrape.com/\nhttps://books.toscrape.com/"
                    //     )
                    //     ->rows(4)
                    //     ->autosize(),

                    TextInput::make('url')
                        ->label('URL')
                        ->default('https://quotes.toscrape.com')
                        ->required(),

                    MarkdownEditor::make('extract_rules')
                        ->label('Extract Rules (JSON)')
                        ->default(
                            json_encode(
                                [
                                    'title' => [
                                        'selector' => 'h1',
                                        'output' => 'text',
                                        'type' => 'item',
                                    ],
                                    // 'tags' => [
                                    //     'selector' => '.tag',
                                    //     'output' => 'text',
                                    //     'type' => 'list',
                                    // ],
                                    // 'quotes' => [
                                    //     'selector' => '.quote',
                                    //     'type' => 'list',
                                    //     'output' => [
                                    //         'author' => [
                                    //             'selector' => '.author',
                                    //             'output' => 'text',
                                    //             'type' => 'item',
                                    //         ],
                                    //         'quote' => [
                                    //             'selector' => '.text',
                                    //             'output' => 'text',
                                    //             'type' => 'item',
                                    //         ],
                                    //     ],
                                    // ],
                                ],
                                JSON_PRETTY_PRINT
                            )
                        )
                        ->helperText(
                            'JSON rules allowing to extract data from CSS selectors.'
                        )
                        ->disableAllToolbarButtons(),

                    Checkbox::make('screenshot')->default(false),
                ]),
            ])
            ->statePath('data');
    }

    /**
     * Define the action to be taken when submitting the form.
     *
     * @return Action
     */
    public function submitAction(): Action
    {
        return Action::make('submit')
            ->label('Try it')
            ->icon('heroicon-o-play-circle')
            ->action(fn() => $this->submit());
    }

    /**
     * Handles the form submission, sending the request and capturing the response.
     */
    public function submit(): void
    {
        $this->showResponse = false;

        $validated = $this->form->getState();

        $response = Http::get(route('api.scraper'), [
            'api_key' => $this->user->api_key,
            'url' => $validated['url'],
            'extract_rules' => json_encode(
                json_decode($validated['extract_rules'])
            ),
            'screenshot' => $validated['screenshot'],
            'async' => $validated['async'],
        ]);

        $this->response = [
            'data' => $response->json()['data'],
            'status' => $response->status(),
            'reason' => $response->reason(),
        ];

        $this->showResponse = true;
    }

    /**
     * Initialize user computed property.
     *
     * @return User
     */
    #[Computed]
    public function user(): User
    {
        return Auth::user();
    }

    /**
     * Render the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('livewire.request-builder');
    }
}
