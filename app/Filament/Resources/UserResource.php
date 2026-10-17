<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(),
                Forms\Components\TextInput::make('username'),
                Forms\Components\Textarea::make('bio')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('avatar_url')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('timezone')
                    ->required(),
                Forms\Components\TextInput::make('week_starts_on')
                    ->required(),
                Forms\Components\TextInput::make('theme')
                    ->required(),
                Forms\Components\TextInput::make('language')
                    ->required(),
                Forms\Components\Toggle::make('notifications_push_enabled')
                    ->required(),
                Forms\Components\TextInput::make('default_reminder_time'),
                Forms\Components\Toggle::make('notifications_weekly_summary')
                    ->required(),
                Forms\Components\Toggle::make('notifications_product_updates')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\DateTimePicker::make('onboarding_completed_at'),
                Forms\Components\DateTimePicker::make('archived_at'),
                Forms\Components\Toggle::make('auto_sync')
                    ->required(),
                Forms\Components\TextInput::make('referral_code'),
                Forms\Components\TextInput::make('referred_by_user_id')
                    ->numeric(),
                Forms\Components\TextInput::make('premium_tier'),
                Forms\Components\TextInput::make('business_name'),
                Forms\Components\TextInput::make('ad_tagline'),
                Forms\Components\TextInput::make('ad_link'),
                Forms\Components\Toggle::make('is_admin')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('timezone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('week_starts_on')
                    ->searchable(),
                Tables\Columns\TextColumn::make('theme')
                    ->searchable(),
                Tables\Columns\TextColumn::make('language')
                    ->searchable(),
                Tables\Columns\IconColumn::make('notifications_push_enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('default_reminder_time')
                    ->searchable(),
                Tables\Columns\IconColumn::make('notifications_weekly_summary')
                    ->boolean(),
                Tables\Columns\IconColumn::make('notifications_product_updates')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('onboarding_completed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('archived_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('auto_sync')
                    ->boolean(),
                Tables\Columns\TextColumn::make('referral_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('referred_by_user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('premium_tier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('business_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ad_tagline')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ad_link')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_admin')
                    ->boolean(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
