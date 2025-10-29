<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Layer;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\GeometryCollection;

class LayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a layer with multiple geometry types (GeometryCollection)
        $layer1 = new Layer();
        $layer1->name = 'Teste 2';
        $layer1->geojson_file = '01K8Q4SG8ZZQ6W015WXD57A0EQ.json';
        
        // Create individual geometries
        $point = new Point(0.5, 102.0); // lat, lng
        $lineString = new LineString([
            new Point(0.0, 102.0),
            new Point(1.0, 103.0),
            new Point(0.0, 104.0),
            new Point(1.0, 105.0)
        ]);
        $polygon = new Polygon([
            new LineString([
                new Point(0.0, 100.0),
                new Point(0.0, 101.0),
                new Point(1.0, 101.0),
                new Point(1.0, 100.0),
                new Point(0.0, 100.0)
            ])
        ]);
        
        // Create GeometryCollection
        $geometryCollection = new GeometryCollection([$point, $lineString, $polygon]);
        
        $layer1->geometry = $geometryCollection;
        $layer1->geometry_count = 3;
        $layer1->geometry_types = ['Point', 'LineString', 'Polygon'];
        $layer1->save();
        
        // Create a layer with just a point
        $layer2 = new Layer();
        $layer2->name = 'Ponto Simples';
        $layer2->geojson_file = 'point_simple.json';
        $layer2->geometry = new Point(0.0, 100.0);
        $layer2->geometry_count = 1;
        $layer2->geometry_types = ['Point'];
        $layer2->save();
        
        // Create a layer with just a line
        $layer3 = new Layer();
        $layer3->name = 'Linha Simples';
        $layer3->geojson_file = 'line_simple.json';
        $layer3->geometry = new LineString([
            new Point(0.0, 100.0),
            new Point(1.0, 101.0),
            new Point(2.0, 102.0)
        ]);
        $layer3->geometry_count = 1;
        $layer3->geometry_types = ['LineString'];
        $layer3->save();
        
        // Create a layer with just a polygon
        $layer4 = new Layer();
        $layer4->name = 'Polígono Simples';
        $layer4->geojson_file = 'polygon_simple.json';
        $layer4->geometry = new Polygon([
            new LineString([
                new Point(0.0, 100.0),
                new Point(0.0, 101.0),
                new Point(1.0, 101.0),
                new Point(1.0, 100.0),
                new Point(0.0, 100.0)
            ])
        ]);
        $layer4->geometry_count = 1;
        $layer4->geometry_types = ['Polygon'];
        $layer4->save();
    }
}
