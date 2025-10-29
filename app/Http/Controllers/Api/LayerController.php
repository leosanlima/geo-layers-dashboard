<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LayerController extends Controller
{
    public function index(): JsonResponse
    {
        $layers = Layer::all();
        
        $geojson = [
            'type' => 'FeatureCollection',
            'features' => []
        ];

        foreach ($layers as $layer) {
            $hasGeometry = $layer->geometry || $layer->getRawOriginal('geometry');
            
            if ($hasGeometry) {
                $feature = $layer->toGeoJSON();
                
                if (!empty($feature) && isset($feature['geometry'])) {
                    $feature['properties']['created_at'] = $layer->created_at->toISOString();
                    $feature['properties']['updated_at'] = $layer->updated_at->toISOString();
                    $feature['properties']['geojson_file'] = $layer->geojson_file;
                    $feature['properties']['geometry_type'] = $layer->geometry_type;
                    $feature['properties']['geometry_count'] = $layer->geometry_count;
                    $feature['properties']['geometry_types'] = $layer->geometry_types;
                    
                    if ($feature['geometry']['type'] === 'GeometryCollection') {
                        foreach ($feature['geometry']['geometries'] as $index => $geometry) {
                            $individualFeature = [
                                'type' => 'Feature',
                                'geometry' => $geometry,
                                'properties' => array_merge($feature['properties'], [
                                    'geometry_type' => $geometry['type'],
                                    'original_id' => $layer->id,
                                    'geometry_index' => $index
                                ])
                            ];
                            $geojson['features'][] = $individualFeature;
                        }
                    } else {
                        $geojson['features'][] = $feature;
                    }
                }
            }
        }

        return response()->json($geojson)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }



}
