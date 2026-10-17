<?php

namespace App\Filament\Resources\BitResource\Pages;

use App\Filament\Resources\BitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBits extends ListRecords
{
    protected static string $resource = BitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
