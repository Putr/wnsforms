<?php

namespace App\Filament\Resources\FormResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('data')
                    ->label('Form Data')
                    ->disabled()
                    ->columnSpanFull()
                    ->formatStateUsing(fn($state) => json_encode($state, JSON_PRETTY_PRINT)),
                Forms\Components\TextInput::make('ip_address')
                    ->disabled(),
                Forms\Components\TextInput::make('user_agent')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('referrer')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address'),
                Tables\Columns\TextColumn::make('referrer'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // No create action as submissions are created via the API
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
