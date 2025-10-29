<?php

namespace App\Filament\Resources\Layers;

use App\Filament\Resources\Layers\Pages\CreateLayer;
use App\Filament\Resources\Layers\Pages\EditLayer;
use App\Filament\Resources\Layers\Pages\ListLayers;
use App\Models\Layer;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LayerResource extends Resource
{
    protected static ?string $model = Layer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Camadas';

    protected static ?string $modelLabel = 'Camada';

    protected static ?string $pluralModelLabel = 'Camadas';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informações da Camada')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome da Camada')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Digite o nome da camada'),
                        
                        FileUpload::make('geojson_file')
                            ->label('Arquivo GeoJSON')
                            ->acceptedFileTypes(['application/json', 'application/geo+json'])
                            ->required()
                            ->helperText('Faça upload de um arquivo GeoJSON válido. Múltiplas geometrias criarão camadas separadas.')
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    // Try both possible paths
                                    $file1 = storage_path('app/livewire-tmp/' . $state);
                                    $file2 = storage_path('app/private/' . $state);
                                    
                                    $file = file_exists($file1) ? $file1 : $file2;
                                    
                                    if (file_exists($file)) {
                                        $content = file_get_contents($file);
                                        $geojson = json_decode($content, true);
                                        
                                        if ($geojson && isset($geojson['type'])) {
                                            if ($geojson['type'] === 'FeatureCollection' && isset($geojson['features'])) {
                                                // Count geometries and show info
                                                $geometryCount = count($geojson['features']);
                                                $geometryTypes = [];
                                                
                                                foreach ($geojson['features'] as $feature) {
                                                    if (isset($feature['geometry']['type'])) {
                                                        $geometryTypes[] = $feature['geometry']['type'];
                                                    }
                                                }
                                                
                                                $uniqueTypes = array_unique($geometryTypes);
                                                $set('geometry_count', $geometryCount);
                                                $set('geometry_types', implode(', ', $uniqueTypes));
                                                
                                                // Set the full GeoJSON for processing
                                                $set('geometry', json_encode($geojson));
                                            } elseif ($geojson['type'] === 'Feature' && isset($geojson['geometry'])) {
                                                // Handle single Feature
                                                $set('geometry_count', 1);
                                                $set('geometry_types', $geojson['geometry']['type']);
                                                $set('geometry', json_encode($geojson));
                                            } elseif (isset($geojson['coordinates']) && isset($geojson['type'])) {
                                                // Handle direct geometry object
                                                $set('geometry_count', 1);
                                                $set('geometry_types', $geojson['type']);
                                                $set('geometry', json_encode($geojson));
                                            }
                                        }
                                    }
                                }
                            }),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                TextColumn::make('name')
                    ->label('Nome da Camada')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('geometry_count')
                    ->label('Quantidade de Geometrias')
                    ->sortable(),
                
                TextColumn::make('geometry_types')
                    ->label('Tipos de Geometria')
                    ->getStateUsing(function ($record) {
                        if ($record->geometry_types && is_array($record->geometry_types)) {
                            return implode(', ', $record->geometry_types);
                        }
                        return 'Desconhecido';
                    }),
                
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLayers::route('/'),
            'create' => CreateLayer::route('/create'),
            'edit' => EditLayer::route('/{record}/edit'),
        ];
    }
}