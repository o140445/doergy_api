<?php

namespace App\Filament\Resources\PlatformUserResource\Pages;

use App\Filament\Resources\PlatformUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlatformUser extends EditRecord
{
    protected static string $resource = PlatformUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
