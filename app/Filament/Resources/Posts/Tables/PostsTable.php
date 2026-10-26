<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PostStatus;
use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->limit(30)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('link')
                    ->limit(30)
                    ->url(fn ($record) => $record->link, shouldOpenInNewTab: true),
                TextColumn::make('source')
                    ->searchable(),
                TextColumn::make('pub_date')
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('interest_score')
                    ->label('Interest')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->defaultSort('pub_date', 'desc')
            ->filters([
                SelectFilter::make('source')
                    ->options(fn () => Post::query()
                        ->whereNotNull('source')
                        ->distinct()
                        ->orderBy('source')
                        ->pluck('source', 'source')
                        ->toArray()),
                SelectFilter::make('status')
                    ->options(PostStatus::class),
                Filter::make('pub_date_day')
                    ->label('Publication date')
                    ->schema([
                        DatePicker::make('date')->label('Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['date'] ?? null, fn (Builder $q, string $date) => $q->whereRaw("STR_TO_DATE(pub_date, '%a, %d %b %Y') = ?", [$date]));
                    }),
                Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereRaw("STR_TO_DATE(pub_date, '%a, %d %b %Y') = ?", [now()->toDateString()])),
                Filter::make('last_3_days')
                    ->label('Last 3 days')
                    ->query(fn (Builder $query): Builder => $query->whereRaw("STR_TO_DATE(pub_date, '%a, %d %b %Y') >= ?", [now()->subDays(3)->toDateString()])),
                Filter::make('last_7_days')
                    ->label('Last 7 days')
                    ->query(fn (Builder $query): Builder => $query->whereRaw("STR_TO_DATE(pub_date, '%a, %d %b %Y') >= ?", [now()->subDays(7)->toDateString()])),
                Filter::make('last_14_days')
                    ->label('Last 14 days')
                    ->query(fn (Builder $query): Builder => $query->whereRaw("STR_TO_DATE(pub_date, '%a, %d %b %Y') >= ?", [now()->subDays(14)->toDateString()])),
                Filter::make('last_30_days')
                    ->label('Last 30 days')
                    ->query(fn (Builder $query): Builder => $query->whereRaw("STR_TO_DATE(pub_date, '%a, %d %b %Y') >= ?", [now()->subDays(30)->toDateString()])),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
