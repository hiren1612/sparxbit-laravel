<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BitResource\Pages;
use App\Filament\Resources\BitResource\RelationManagers;
use App\Models\Bit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BitResource extends Resource
{
    protected static ?string $model = Bit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\TextInput::make('why'),
                Forms\Components\TextInput::make('description'),
                Forms\Components\TextInput::make('goal_per_week')
                    ->required()
                    ->numeric()
                    ->default(7),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date'),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name'),
                Forms\Components\Select::make('goal_id')
                    ->relationship('goal', 'id'),
                Forms\Components\TextInput::make('icon_key')
                    ->required(),
                Forms\Components\Toggle::make('reminder_enabled')
                    ->required(),
                Forms\Components\TextInput::make('reminder_time'),
                Forms\Components\Textarea::make('reminder_days')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_system')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('why')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('goal_per_week')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('goal.id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('icon_key')
                    ->searchable(),
                Tables\Columns\IconColumn::make('reminder_enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('reminder_time')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_system')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBits::route('/'),
            'create' => Pages\CreateBit::route('/create'),
            'edit' => Pages\EditBit::route('/{record}/edit'),
        ];
    }
}
