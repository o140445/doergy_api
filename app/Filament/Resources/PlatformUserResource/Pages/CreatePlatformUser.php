<?php

namespace App\Filament\Resources\PlatformUserResource\Pages;

use App\Filament\Resources\PlatformUserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePlatformUser extends CreateRecord
{
    protected static string $resource = PlatformUserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
