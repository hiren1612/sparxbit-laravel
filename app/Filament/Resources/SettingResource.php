<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationLabel = 'App content';

    protected static ?string $navigationGroup = 'Content';

    // Fixed content snippets — editable, not creatable/deletable.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('label')
                ->label('Setting')
                ->content(fn ($record) => $record?->label),
            Forms\Components\Textarea::make('value')
                ->label('Text')
                ->rows(fn ($record) => $record?->type === 'textarea' ? 4 : 2)
                ->required()
                ->columnSpanFull()
                ->helperText('This text appears in the app (e.g. the About screen).'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')->searchable(),
                Tables\Columns\TextColumn::make('group')->badge(),
                Tables\Columns\TextColumn::make('value')->limit(50)->label('Current text'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->label('Last updated'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
