<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('link')
                    ->required()
                    ->url()
                    ->unique(ignoreRecord: true),
                Textarea::make('description'),
                TextInput::make('pub_date'),
                TextInput::make('image')
                    ->url(),
                TextInput::make('source'),
                Select::make('status')
                    ->options(PostStatus::class)
                    ->required(),
            ]);
    }
}
