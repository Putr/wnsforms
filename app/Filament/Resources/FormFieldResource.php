<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormFieldResource\Pages;
use App\Models\FormField;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FormFieldResource extends Resource
{
    protected static ?string $model = FormField::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Forms';

    protected static ?string $navigationLabel = 'Form Fields';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('form_id')
                    ->relationship('form', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('The field name used in the form submission (e.g., "email", "phone")')
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255)
                    ->helperText('The label shown to users (e.g., "Email Address", "Phone Number")'),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'text' => 'Text',
                        'email' => 'Email',
                        'phone' => 'Phone',
                        'url' => 'URL',
                        'honeypot' => 'Honeypot',
                    ])
                    ->helperText('Honeypot fields are hidden and used to prevent spam'),
                Forms\Components\Toggle::make('required')
                    ->default(true)
                    ->helperText('Whether this field is required in form submissions'),
                Forms\Components\TagsInput::make('validation_rules')
                    ->helperText('Additional Laravel validation rules (e.g., "min:3", "max:100")')
                    ->placeholder('Type a rule and press Enter'),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Order in which fields appear (lower numbers first)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('form.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'honeypot' => 'danger',
                        'email' => 'success',
                        'phone' => 'warning',
                        'url' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('required')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order')
                    ->sortable(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->filters([
                Tables\Filters\SelectFilter::make('form')
                    ->relationship('form', 'name'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'text' => 'Text',
                        'email' => 'Email',
                        'phone' => 'Phone',
                        'url' => 'URL',
                        'honeypot' => 'Honeypot',
                    ]),
                Tables\Filters\TernaryFilter::make('required'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormFields::route('/'),
            'create' => Pages\CreateFormField::route('/create'),
            'edit' => Pages\EditFormField::route('/{record}/edit'),
        ];
    }
}
