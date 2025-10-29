<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GeoJSONValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If value is null or empty, it's valid (optional field)
        if (empty($value)) {
            return;
        }

        // If value is a file path, read the file content
        if (is_string($value) && file_exists($value)) {
            $content = file_get_contents($value);
            $geojson = json_decode($content, true);
        } else {
            // If value is already an array or JSON string
            $geojson = is_array($value) ? $value : json_decode($value, true);
        }

        // Check if JSON is valid
        if (json_last_error() !== JSON_ERROR_NONE) {
            $fail('The :attribute must be a valid JSON file.');
            return;
        }

        // Validate GeoJSON structure
        if (!$this->isValidGeoJSON($geojson)) {
            $fail('The :attribute must be a valid GeoJSON FeatureCollection or Feature.');
            return;
        }
    }

    /**
     * Validate GeoJSON structure
     */
    private function isValidGeoJSON(array $geojson): bool
    {
        // Check if it's a FeatureCollection
        if (isset($geojson['type']) && $geojson['type'] === 'FeatureCollection') {
            return $this->validateFeatureCollection($geojson);
        }

        // Check if it's a single Feature
        if (isset($geojson['type']) && $geojson['type'] === 'Feature') {
            return $this->validateFeature($geojson);
        }

        // Check if it's a raw geometry
        if (isset($geojson['type']) && $this->isValidGeometryType($geojson['type'])) {
            return $this->validateGeometry($geojson);
        }

        return false;
    }

    /**
     * Validate FeatureCollection
     */
    private function validateFeatureCollection(array $geojson): bool
    {
        if (!isset($geojson['features']) || !is_array($geojson['features'])) {
            return false;
        }

        foreach ($geojson['features'] as $feature) {
            if (!$this->validateFeature($feature)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate Feature
     */
    private function validateFeature(array $feature): bool
    {
        if (!isset($feature['type']) || $feature['type'] !== 'Feature') {
            return false;
        }

        if (!isset($feature['geometry'])) {
            return false;
        }

        return $this->validateGeometry($feature['geometry']);
    }

    /**
     * Validate Geometry
     */
    private function validateGeometry(array $geometry): bool
    {
        if (!isset($geometry['type']) || !isset($geometry['coordinates'])) {
            return false;
        }

        $type = $geometry['type'];
        $coordinates = $geometry['coordinates'];

        if (!$this->isValidGeometryType($type)) {
            return false;
        }

        return $this->validateCoordinates($type, $coordinates);
    }

    /**
     * Check if geometry type is valid
     */
    private function isValidGeometryType(string $type): bool
    {
        $validTypes = [
            'Point',
            'LineString',
            'Polygon',
            'MultiPoint',
            'MultiLineString',
            'MultiPolygon',
            'GeometryCollection'
        ];

        return in_array($type, $validTypes);
    }

    /**
     * Validate coordinates based on geometry type
     */
    private function validateCoordinates(string $type, $coordinates): bool
    {
        switch ($type) {
            case 'Point':
                return $this->validatePointCoordinates($coordinates);
            case 'LineString':
                return $this->validateLineStringCoordinates($coordinates);
            case 'Polygon':
                return $this->validatePolygonCoordinates($coordinates);
            case 'MultiPoint':
                return $this->validateMultiPointCoordinates($coordinates);
            case 'MultiLineString':
                return $this->validateMultiLineStringCoordinates($coordinates);
            case 'MultiPolygon':
                return $this->validateMultiPolygonCoordinates($coordinates);
            case 'GeometryCollection':
                return $this->validateGeometryCollectionCoordinates($coordinates);
            default:
                return false;
        }
    }

    /**
     * Validate Point coordinates [lng, lat]
     */
    private function validatePointCoordinates($coordinates): bool
    {
        return is_array($coordinates) && 
               count($coordinates) === 2 && 
               is_numeric($coordinates[0]) && 
               is_numeric($coordinates[1]);
    }

    /**
     * Validate LineString coordinates [[lng, lat], [lng, lat], ...]
     */
    private function validateLineStringCoordinates($coordinates): bool
    {
        if (!is_array($coordinates) || count($coordinates) < 2) {
            return false;
        }

        foreach ($coordinates as $point) {
            if (!$this->validatePointCoordinates($point)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate Polygon coordinates [[[lng, lat], [lng, lat], ...], ...]
     */
    private function validatePolygonCoordinates($coordinates): bool
    {
        if (!is_array($coordinates) || empty($coordinates)) {
            return false;
        }

        foreach ($coordinates as $ring) {
            if (!$this->validateLineStringCoordinates($ring)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate MultiPoint coordinates [[lng, lat], [lng, lat], ...]
     */
    private function validateMultiPointCoordinates($coordinates): bool
    {
        if (!is_array($coordinates)) {
            return false;
        }

        foreach ($coordinates as $point) {
            if (!$this->validatePointCoordinates($point)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate MultiLineString coordinates [[[lng, lat], [lng, lat], ...], ...]
     */
    private function validateMultiLineStringCoordinates($coordinates): bool
    {
        if (!is_array($coordinates)) {
            return false;
        }

        foreach ($coordinates as $lineString) {
            if (!$this->validateLineStringCoordinates($lineString)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate MultiPolygon coordinates [[[[lng, lat], [lng, lat], ...], ...], ...]
     */
    private function validateMultiPolygonCoordinates($coordinates): bool
    {
        if (!is_array($coordinates)) {
            return false;
        }

        foreach ($coordinates as $polygon) {
            if (!$this->validatePolygonCoordinates($polygon)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate GeometryCollection coordinates [geometry, geometry, ...]
     */
    private function validateGeometryCollectionCoordinates($coordinates): bool
    {
        if (!is_array($coordinates)) {
            return false;
        }

        foreach ($coordinates as $geometry) {
            if (!is_array($geometry) || !$this->validateGeometry($geometry)) {
                return false;
            }
        }

        return true;
    }
}
