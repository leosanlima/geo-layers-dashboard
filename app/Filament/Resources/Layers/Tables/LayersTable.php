<?php

namespace App\Filament\Resources\Layers\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;

class LayersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                TextColumn::make('name')
                    ->label('Layer Name')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('geometry')
                    ->label('Geometry Type')
                    ->getStateUsing(function ($record) {
                        if ($record->geometry) {
                            $geometry = json_decode($record->geometry, true);
                            return $geometry['type'] ?? 'Unknown';
                        }
                        return 'No geometry';
                    }),
                
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                // Add bulk actions if needed
            ]);
    }
}