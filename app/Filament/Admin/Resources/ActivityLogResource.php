<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Log aktivitas';

    protected static ?string $modelLabel = 'Log aktivitas';

    protected static ?string $pluralModelLabel = 'Log aktivitas';

    protected static ?int $navigationSort = 7;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('actor.name')->label('Admin')->placeholder('Akun dihapus')->searchable(),
                Tables\Columns\TextColumn::make('event')->label('Aksi')->badge()->searchable(),
                Tables\Columns\TextColumn::make('description')->label('Ringkasan')->wrap()->searchable(),
                Tables\Columns\TextColumn::make('subject_type')->label('Jenis data')->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—'),
                Tables\Columns\TextColumn::make('ip_address')->label('Alamat IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')->label('Aksi')->options(fn () => ActivityLog::query()->distinct()->orderBy('event')->pluck('event', 'event')),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('actor');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
