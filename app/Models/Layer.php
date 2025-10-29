<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Geometry;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\MultiPoint;
use MatanYadaev\EloquentSpatial\Objects\MultiLineString;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use MatanYadaev\EloquentSpatial\Objects\GeometryCollection;

class Layer extends Model
{
    use HasSpatial;

    protected $fillable = [
        'name',
        'geojson_file',
        'geometry',
        'geometry_count',
        'geometry_types',
    ];

    protected $casts = [
        'geometry_types' => 'array',
        'geometry_count' => 'integer',
    ];

    public function setGeometryAttribute($value)
    {
        if (is_string($value)) {
            $geometryData = json_decode($value, true);
            if ($geometryData) {
                $this->processGeometryData($geometryData);
            }
        } elseif (is_array($value)) {
            $this->processGeometryData($value);
        } else {
            $this->attributes['geometry'] = $value;
        }
    }

    private function processGeometryData(array $data): void
    {
        if (isset($data['type'])) {
            if ($data['type'] === 'FeatureCollection' && isset($data['features'])) {
                // Process FeatureCollection - create GeometryCollection with all geometries
                $geometries = [];
                $geometryTypes = [];
                
                foreach ($data['features'] as $feature) {
                    if (isset($feature['geometry'])) {
                        $geometry = self::createGeometryFromGeoJSON($feature['geometry']);
                        $geometries[] = $geometry;
                        $geometryTypes[] = $feature['geometry']['type'];
                    }
                }
                
                if (!empty($geometries)) {
                    $this->attributes['geometry'] = new GeometryCollection($geometries);
                    $this->attributes['geometry_count'] = count($geometries);
                    $this->attributes['geometry_types'] = array_unique($geometryTypes);
                }
            } elseif ($data['type'] === 'Feature' && isset($data['geometry'])) {
                // Process single Feature
                $geometry = self::createGeometryFromGeoJSON($data['geometry']);
                $this->attributes['geometry'] = $geometry;
                $this->attributes['geometry_count'] = 1;
                $this->attributes['geometry_types'] = [$data['geometry']['type']];
            } elseif (isset($data['coordinates'])) {
                // Process direct geometry object
                $geometry = self::createGeometryFromGeoJSON($data);
                $this->attributes['geometry'] = $geometry;
                $this->attributes['geometry_count'] = 1;
                $this->attributes['geometry_types'] = [$data['type']];
            }
        }
    }


    public function getGeometryAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // If it's already a Geometry object, return it
        if ($value instanceof Geometry) {
            return $value;
        }

        // If it's a string, check if it's WKB or JSON
        if (is_string($value)) {
            // Check if it looks like WKB (starts with '0' and contains binary data)
            $jsonDecoded = json_decode($value, true);
            if (strlen($value) > 0 && $value[0] === '0' && !$jsonDecoded) {
                try {
                    return Geometry::fromWkb($value);
                } catch (\Exception $e) {
                    // If WKB conversion fails, try JSON
                }
            }
            
            // Try to decode as JSON
            if ($jsonDecoded) {
                return self::createGeometryFromGeoJSON($jsonDecoded);
            }
        }

        // If it's an array, create Geometry object
        if (is_array($value)) {
            return self::createGeometryFromGeoJSON($value);
        }

        return null;
    }

    public function getGeometryTypeAttribute(): ?string
    {
        if (!$this->geometry) {
            return null;
        }

        // Get geometry type from the geometry object
        try {
            $geometryArray = $this->geometry->toArray();
            return $geometryArray['type'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function toGeoJSON(): array
    {
        $geometryArray = null;

        // Try to get geometry from the spatial object
        if ($this->geometry) {
            try {
                $geometryArray = $this->geometry->toArray();
            } catch (\Exception $e) {
                // If the geometry object fails, return empty array
                return [];
            }
        }

        if (!$geometryArray) {
            return [];
        }

        return [
            'type' => 'Feature',
            'geometry' => $geometryArray,
            'properties' => [
                'id' => $this->id,
                'name' => $this->name,
                'geojson_file' => $this->geojson_file,
                'geometry_count' => $this->geometry_count,
                'geometry_types' => $this->geometry_types,
            ]
        ];
    }


    public function toFeatureCollection(): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => [$this->toGeoJSON()]
        ];
    }


    public static function createLayerFromGeoJSONFile(string $filePath, string $name): Layer
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('File not found: ' . $filePath);
        }

        $content = file_get_contents($filePath);
        $geojson = json_decode($content, true);
        
        if (!$geojson) {
            throw new \InvalidArgumentException('Invalid JSON file');
        }

        $layer = new self();
        $layer->name = $name;
        $layer->geojson_file = basename($filePath);

        // Process geometries manually to avoid setGeometryAttribute issues
        if (isset($geojson['type']) && $geojson['type'] === 'FeatureCollection' && isset($geojson['features'])) {
            $geometries = [];
            $geometryTypes = [];
            
            foreach ($geojson['features'] as $feature) {
                if (isset($feature['geometry'])) {
                    $geometry = self::createGeometryFromGeoJSON($feature['geometry']);
                    $geometries[] = $geometry;
                    $geometryTypes[] = $feature['geometry']['type'];
                }
            }
            
            if (!empty($geometries)) {
                $geometryCollection = new GeometryCollection($geometries);
                $layer->attributes['geometry'] = $geometryCollection;
                $layer->geometry_count = count($geometries);
                $layer->geometry_types = array_unique($geometryTypes);
            }
        } elseif (isset($geojson['type']) && $geojson['type'] === 'Feature' && isset($geojson['geometry'])) {
            $geometry = self::createGeometryFromGeoJSON($geojson['geometry']);
            $layer->attributes['geometry'] = $geometry;
            $layer->geometry_count = 1;
            $layer->geometry_types = [$geojson['geometry']['type']];
        } elseif (isset($geojson['coordinates']) && isset($geojson['type'])) {
            $geometry = self::createGeometryFromGeoJSON($geojson);
            $layer->attributes['geometry'] = $geometry;
            $layer->geometry_count = 1;
            $layer->geometry_types = [$geojson['type']];
        }
        
        return $layer;
    }


    public static function createGeometryFromGeoJSON(array $geometry): Geometry
    {
        $type = $geometry['type'] ?? '';
        $coordinates = $geometry['coordinates'] ?? [];

        switch ($type) {
            case 'Point':
                return new Point($coordinates[1], $coordinates[0]); // lat, lng
                
            case 'LineString':
                $points = array_map(function($coord) {
                    return new Point($coord[1], $coord[0]);
                }, $coordinates);
                return new LineString($points);
                
            case 'Polygon':
                $rings = array_map(function($ring) {
                    $points = array_map(function($coord) {
                        return new Point($coord[1], $coord[0]);
                    }, $ring);
                    return new LineString($points);
                }, $coordinates);
                return new Polygon($rings);
                
            case 'MultiPoint':
                $points = array_map(function($coord) {
                    return new Point($coord[1], $coord[0]);
                }, $coordinates);
                return new MultiPoint($points);
                
            case 'MultiLineString':
                $lineStrings = array_map(function($lineString) {
                    $points = array_map(function($coord) {
                        return new Point($coord[1], $coord[0]);
                    }, $lineString);
                    return new LineString($points);
                }, $coordinates);
                return new MultiLineString($lineStrings);
                
            case 'MultiPolygon':
                $polygons = array_map(function($polygon) {
                    $rings = array_map(function($ring) {
                        $points = array_map(function($coord) {
                            return new Point($coord[1], $coord[0]);
                        }, $ring);
                        return new LineString($points);
                    }, $polygon);
                    return new Polygon($rings);
                }, $coordinates);
                return new MultiPolygon($polygons);
                
            case 'GeometryCollection':
                $geometries = array_map(function($geom) {
                    return self::createGeometryFromGeoJSON($geom);
                }, $coordinates);
                return new GeometryCollection($geometries);
                
            default:
                throw new \InvalidArgumentException("Unsupported geometry type: {$type}");
        }
    }
}
