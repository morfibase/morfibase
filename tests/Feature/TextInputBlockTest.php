<?php

use App\Filament\Resources\Tables\Pages\CreateTable;
use App\Filament\Resources\Tables\Pages\ViewTable;
use App\Filament\Resources\Tables\TableResource;
use App\Models\Table;
use Filament\Forms\Components\Builder;
use Illuminate\Support\Str;

use function Pest\Livewire\livewire;

it('text input block required form settings works correctly', function () {
    $undoBuilderFake = Builder::fake();

    // Setup
    livewire(CreateTable::class)
        ->fillForm([
            'name' => 'table name',
            'display_field' => [
                [
                    'data' => [
                        'label' => 'DisplayFieldName',
                    ],
                    'type' => 'textInput'
                ]
            ],
            'fields' => [
                [
                    'data' => [
                        'label' => 'TestField',
                        'form' => [
                            'required' => true
                        ]
                    ],
                    'type' => 'textInput'
                ]
            ]
        ])
        ->call('create');

        $table = Table::first();
        $data = [];
        
        $data[$table->display_field[0]['data']['db_column_name']] = 'Display column name';

        // Testing form field inside the create new action
        livewire(ViewTable::class, ['record' => $table->id])
            ->callAction('create_new_table_record', data: $data)
            ->assertHasFormErrors([
                $table->fields[0]['data']['db_column_name'] => ['required']
            ]);

        $data[$table->fields[0]['data']['db_column_name']] = Str::random();
        livewire(ViewTable::class, ['record' => $table->id])
            ->callAction('create_new_table_record', data: $data)
            ->assertHasNoFormErrors();

        $undoBuilderFake();
});

it('text input block inputType form settings works correctly', function () {
    $testData = [
        [
            'inputType' => 'email',
            'wrongData' => 'this is not an email',
            'correctData' => fake()->email
        ],
        [
            'inputType' => 'numeric',
            'wrongData' => 'this is not a number',
            'correctData' => 55
        ],
    ];

    foreach($testData as $test) {
        // Reset the db after each iteration
        $dbPath = database_path('database_testing.sqlite');
        touch($dbPath);

        // Set PRAGMA before migrations
        \Illuminate\Support\Facades\DB::statement('PRAGMA busy_timeout = 5000;');
        \Illuminate\Support\Facades\DB::statement('PRAGMA journal_mode = WAL;');
        \Illuminate\Support\Facades\DB::statement('PRAGMA synchronous = NORMAL;');

        // Now migrate
        $this->artisan('migrate:fresh');

        // Create user
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user);
        $undoBuilderFake = Builder::fake();

        livewire(CreateTable::class)
            ->fillForm([
                'name' => 'table name',
                'display_field' => [
                    [
                        'data' => [
                            'label' => 'DisplayFieldName',
                        ],
                        'type' => 'textInput'
                    ]
                ],
                'fields' => [
                    [
                        'data' => [
                            'label' => 'TestField',
                            'form' => [
                                'inputType' => $test['inputType']
                            ]
                        ],
                        'type' => 'textInput'
                    ]
                ]
            ])
            ->call('create');

            $table = Table::first();
            $data = [];
            
            $data[$table->display_field[0]['data']['db_column_name']] = 'Display column name';
            $data[$table->fields[0]['data']['db_column_name']] = $test['wrongData'];
            // Testing form field inside the create new action
            livewire(ViewTable::class, ['record' => $table->id])
                ->callAction('create_new_table_record', data: $data)
                ->assertHasFormErrors([
                    $table->fields[0]['data']['db_column_name'] => [$test['inputType']]
                ]);

            $data[$table->fields[0]['data']['db_column_name']] = $test['correctData'];
            livewire(ViewTable::class, ['record' => $table->id])
                ->callAction('create_new_table_record', data: $data)
                ->assertHasNoFormErrors();

            $undoBuilderFake();
            \Illuminate\Support\Facades\DB::disconnect();
        }
});