<?php

namespace App\Filament\Resources\Layers\Pages;

use App\Filament\Resources\Layers\LayerResource;
use App\Models\Layer;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateLayer extends CreateRecord
{
    protected static string $resource = LayerResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // If we have a GeoJSON file, create single layer with all geometries
        if (isset($data['geojson_file']) && $data['geojson_file']) {
            return $this->createLayerFromFile($data);
        }
        
        // For single layer creation, use the default behavior
        return parent::handleRecordCreation($data);
    }

    protected function createLayerFromFile(array $data): Layer
    {
        try {
            // Find the uploaded file
            $file1 = storage_path('app/livewire-tmp/' . $data['geojson_file']);
            $file2 = storage_path('app/private/' . $data['geojson_file']);
            
            $file = file_exists($file1) ? $file1 : $file2;
            
            if (!file_exists($file)) {
                throw new \Exception('Arquivo enviado não encontrado');
            }

            // Create single layer with all geometries
            $layerName = $data['name'] ?? 'Camada';
            $layer = Layer::createLayerFromGeoJSONFile($file, $layerName);
            $layer->save();

            // Show success notification
            Notification::make()
                ->title('Camada Criada com Sucesso')
                ->body("Camada '{$layer->name}' criada com {$layer->geometry_count} geometrias dos tipos: " . implode(', ', $layer->geometry_types))
                ->success()
                ->send();

            return $layer;

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao Criar Camada')
                ->body('Falha ao processar arquivo GeoJSON: ' . $e->getMessage())
                ->danger()
                ->send();
            
            // Return a dummy layer to prevent errors
            return new Layer();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
