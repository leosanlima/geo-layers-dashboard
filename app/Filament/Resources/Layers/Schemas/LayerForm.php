<?php

namespace App\Filament\Resources\Layers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use App\Rules\GeoJSONValidation;
use App\Models\Layer;

class LayerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Layer Configuration')
                    ->tabs([
                        Tabs\Tab::make('Basic Information')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Layer Name')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Enter layer name'),
                                
                                Select::make('geometry_type')
                                    ->label('Geometry Type')
                                    ->options([
                                        'Point' => 'Point',
                                        'LineString' => 'LineString',
                                        'Polygon' => 'Polygon',
                                        'MultiPoint' => 'MultiPoint',
                                        'MultiLineString' => 'MultiLineString',
                                        'MultiPolygon' => 'MultiPolygon',
                                        'GeometryCollection' => 'GeometryCollection',
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $set('geometry_preview', '');
                                        }
                                    }),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('GeoJSON Upload')
                            ->schema([
                                FileUpload::make('geojson_file')
                                    ->label('GeoJSON File')
                                    ->acceptedFileTypes(['application/json', 'application/geo+json', '.json'])
                                    ->required()
                                    ->helperText('Upload a valid GeoJSON file (FeatureCollection, Feature, or raw geometry)')
                                    ->validationMessages([
                                        'acceptedFileTypes' => 'The file must be a valid JSON file.',
                                    ])
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if ($state) {
                                            $file = storage_path('app/livewire-tmp/' . $state);
                                            if (file_exists($file)) {
                                                $content = file_get_contents($file);
                                                $geojson = json_decode($content, true);
                                                
                                                if ($geojson) {
                                                    // Set geometry type based on the uploaded file
                                                    if (isset($geojson['type'])) {
                                                        if ($geojson['type'] === 'FeatureCollection' && isset($geojson['features'][0]['geometry'])) {
                                                            $set('geometry_type', $geojson['features'][0]['geometry']['type']);
                                                            $set('geometry', json_encode($geojson['features'][0]['geometry']));
                                                        } elseif ($geojson['type'] === 'Feature' && isset($geojson['geometry'])) {
                                                            $set('geometry_type', $geojson['geometry']['type']);
                                                            $set('geometry', json_encode($geojson['geometry']));
                                                        } elseif (in_array($geojson['type'], ['Point', 'LineString', 'Polygon', 'MultiPoint', 'MultiLineString', 'MultiPolygon', 'GeometryCollection'])) {
                                                            $set('geometry_type', $geojson['type']);
                                                            $set('geometry', json_encode($geojson));
                                                        }
                                                    }
                                                    
                                                    // Set preview
                                                    $set('geometry_preview', json_encode($geojson, JSON_PRETTY_PRINT));
                                                }
                                            }
                                        }
                                    }),
                            ]),

                        Tabs\Tab::make('Manual GeoJSON Input')
                            ->schema([
                                Textarea::make('geometry_preview')
                                    ->label('GeoJSON Preview')
                                    ->rows(10)
                                    ->helperText('This shows the parsed GeoJSON structure')
                                    ->disabled(),
                                
                                Textarea::make('geometry')
                                    ->label('Raw Geometry JSON')
                                    ->rows(8)
                                    ->helperText('Raw geometry data in JSON format')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $geometry = json_decode($state, true);
                                            if ($geometry && isset($geometry['type'])) {
                                                $set('geometry_type', $geometry['type']);
                                            }
                                        }
                                    }),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}