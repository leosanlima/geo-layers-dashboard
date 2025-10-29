<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizador GeoJSON - API de Camadas</title>
    
    <!-- ArcGIS CSS -->
    <link rel="stylesheet" href="https://js.arcgis.com/4.28/esri/themes/light/main.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 100;
            position: relative;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header h1 {
            margin: 0;
            flex: 1;
        }
        
        .header p {
            margin: 0;
            margin-top: 0.25rem;
        }
        
        .btn-admin {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: #ffc107;
            color: #000;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        
        .btn-admin:hover {
            background: #ffb300;
        }
        
        .container {
            display: flex;
            flex: 1;
            height: calc(100vh - 80px);
        }
        
        #mapView {
            flex: 1;
            height: 100%;
        }
        
        .sidebar {
            width: 350px;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            padding: 1rem;
            overflow-y: auto;
            z-index: 50;
        }
        
        .info-panel {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        
        .feature-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-height: 400px;
            overflow-y: auto;
        }
        
        .feature-item {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .feature-item:hover {
            background-color: #e9ecef;
        }
        
        .feature-item.active {
            background-color: #007bff;
            color: white;
        }
        
        .geometry-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-right: 0.5rem;
        }
        
        .point { background: #dc3545; color: white; }
        .linestring { background: #fd7e14; color: white; }
        .polygon { background: #198754; color: white; }
        
        .coordinates {
            font-family: monospace;
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        
        .properties {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .controls {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: #198754;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 1rem;
            color: #6c757d;
        }
        
        .error {
            display: none;
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .status {
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .refresh-info {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }

        .esri-popup__header-title {
            font-size: 1.1rem;
            font-weight: bold;
        }

        .legend-panel {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            z-index: 100;
            max-width: 200px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border-radius: 50%;
            border: 2px solid white;
        }

        .legend-line {
            width: 20px;
            height: 4px;
            margin-right: 10px;
        }

        .legend-polygon {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div>
                <h1>Visualizador GeoJSON</h1>
                <p>ArcGIS Maps SDK</p>
            </div>
            <a href="/painel" class="btn-admin">Admin</a>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <div class="info-panel">
                <h3>Controles do Mapa</h3>
                <div class="controls">
                    <button class="btn btn-success" id="load-btn">
                        Carregar Camadas
                    </button>
                    <button class="btn btn-primary" id="zoom-btn">
                        Zoom
                    </button>
                    <button class="btn btn-danger" id="clear-map-btn">
                        Limpar Mapa
                    </button>
                </div>
                
                <div class="status">
                    Status: <span id="status">Pronto para carregar</span>
                </div>
                
                <div class="refresh-info">
                    Última atualização: <span id="last-update">Nunca</span>
                </div>
            </div>
            
            <div class="info-panel">
                <h3>Estatísticas</h3>
                <p>Total de Features: <span id="feature-count">0</span></p>
                <p>Geometrias: 
                    <span id="point-count" class="geometry-badge point">0 Pontos</span>
                    <span id="linestring-count" class="geometry-badge linestring">0 Linhas</span>
                    <span id="polygon-count" class="geometry-badge polygon">0 Polígonos</span>
                </p>
            </div>
            
            <div class="loading" id="loading">
                <p>Carregando dados da API...</p>
            </div>
            
            <div class="error" id="error">
                <p id="error-message">Erro ao carregar dados</p>
            </div>
            
            <div class="feature-list" id="feature-list">
                <h4>Features Carregadas:</h4>
                <p id="no-features" style="text-align: center; color: #6c757d; padding: 1rem;">
                    Nenhuma feature carregada. Clique em "Carregar Camadas".
                </p>
            </div>
        </div>
        
        <div id="mapView"></div>
    </div>

    <!-- Legend Panel -->
    <div class="legend-panel">
        <h4>Legenda</h4>
        <div class="legend-item">
            <div class="legend-color" style="background: #dc3545;"></div>
            <span>Ponto</span>
        </div>
        <div class="legend-item">
            <div class="legend-line" style="background: #fd7e14;"></div>
            <span>Linha</span>
        </div>
        <div class="legend-item">
            <div class="legend-polygon" style="background: #198754;"></div>
            <span>Polígono</span>
        </div>
    </div>

    <!-- ArcGIS JS -->
    <script src="https://js.arcgis.com/4.28/"></script>
    
    <script>
        // Variáveis globais
        let map, view, graphicsLayer;
        let featureGraphics = [];
        const API_URL = '/api/layers';

        // Módulos ArcGIS
        require([
            "esri/Map",
            "esri/views/MapView",
            "esri/layers/GraphicsLayer",
            "esri/Graphic",
            "esri/geometry/Point",
            "esri/geometry/Polyline",
            "esri/geometry/Polygon",
            "esri/symbols/SimpleMarkerSymbol",
            "esri/symbols/SimpleLineSymbol",
            "esri/symbols/SimpleFillSymbol",
            "esri/Color",
            "esri/widgets/Legend",
            "esri/geometry/Extent",
            "esri/geometry/support/webMercatorUtils"
        ], function(
            Map, MapView, GraphicsLayer, Graphic, 
            Point, Polyline, Polygon,
            SimpleMarkerSymbol, SimpleLineSymbol, SimpleFillSymbol,
            Color, Legend, Extent, webMercatorUtils
        ) {

            // Elementos DOM
            const elements = {
                loading: document.getElementById('loading'),
                error: document.getElementById('error'),
                errorMessage: document.getElementById('error-message'),
                status: document.getElementById('status'),
                lastUpdate: document.getElementById('last-update'),
                featureCount: document.getElementById('feature-count'),
                pointCount: document.getElementById('point-count'),
                linestringCount: document.getElementById('linestring-count'),
                polygonCount: document.getElementById('polygon-count'),
                featureList: document.getElementById('feature-list'),
                noFeatures: document.getElementById('no-features')
            };

            // Símbolos para diferentes tipos de geometria
            const symbols = {
                point: new SimpleMarkerSymbol({
                    color: new Color([220 , 53, 69, 0.8]),
                    outline: {
                        color: new Color([255, 255, 255]),
                        width: 2
                    },
                    size: 12
                }),
                linestring: new SimpleLineSymbol({
                    color: new Color([253, 126, 20, 0.8]),
                    width: 4
                }),
                polygon: new SimpleFillSymbol({
                    color: new Color([25, 135, 84, 0.5]),
                    outline: {
                        color: new Color([20, 83, 45]),
                        width: 2
                    }
                }),
                highlighted: {
                    point: new SimpleMarkerSymbol({
                        color: new Color([255, 107, 122, 0.9]),
                        outline: {
                            color: new Color([255, 255, 255]),
                            width: 3
                        },
                        size: 16
                    }),
                    linestring: new SimpleLineSymbol({
                        color: new Color([255, 140, 0, 0.9]),
                        width: 6
                    }),
                    polygon: new SimpleFillSymbol({
                        color: new Color([32, 201, 151, 0.7]),
                        outline: {
                            color: new Color([13, 110, 253]),
                            width: 3
                        }
                    })
                }
            };

            // Inicializar mapa
            function initMap() {
                // Criar mapa
                map = new Map({
                    basemap: "streets-vector"
                });

                // Criar camada de gráficos
                graphicsLayer = new GraphicsLayer();
                map.add(graphicsLayer);

                // Criar view
                view = new MapView({
                    container: "mapView",
                    map: map,
                    center: [-47, -15], // Centro do Brasil
                    zoom: 4
                });

                // Adicionar legenda
                const legend = new Legend({
                    view: view
                });
                view.ui.add(legend, "bottom-left");

                // Event listener para clique no mapa
                view.on("click", function(event) {
                    view.hitTest(event).then(function(response) {
                        if (response.results.length > 0) {
                            const graphic = response.results[0].graphic;
                            if (graphic && graphic.attributes) {
                                highlightFeature(graphic, true);
                                const featureIndex = graphic.attributes.originalIndex;
                                if (featureIndex !== undefined) {
                                    const feature = featureGraphics[featureIndex]?.feature;
                                    if (feature) {
                                        updateFeatureListSelection(feature);
                                    }
                                }
                            }
                        }
                    });
                });

                console.log("Mapa ArcGIS inicializado com sucesso");
            }

            // Função para carregar GeoJSON da API
            async function loadGeoJSON() {
                showLoading();
                hideError();
                updateStatus('Conectando à API...');

                try {
                    console.log('Fazendo requisição para:', API_URL);
                    const response = await fetch(API_URL);
                    
                    if (!response.ok) {
                        throw new Error(`Erro HTTP: ${response.status} - ${response.statusText}`);
                    }
                    
                    const geoJsonData = await response.json();
                    console.log('Dados recebidos:', geoJsonData);
                    updateStatus('Dados carregados com sucesso!');
                    
                    // Processar os dados
                    processGeoJSONData(geoJsonData);
                    
                } catch (error) {
                    console.error('Erro ao carregar GeoJSON:', error);
                    showError(`Falha ao carregar dados: ${error.message}`);
                    updateStatus('Erro ao carregar dados');
                } finally {
                    hideLoading();
                }
            }

            function processGeoJSONData(geoJsonData) {

                clearMap();
                
                // Verificar estrutura do GeoJSON
                if (!geoJsonData) {
                    throw new Error('Nenhum dado recebido da API');
                }

                if (!geoJsonData.features || !Array.isArray(geoJsonData.features)) {
                    throw new Error('Formato GeoJSON inválido: falta array de features');
                }

                // Processar cada feature
                let validFeaturesCount = 0;
                geoJsonData.features.forEach((feature, index) => {
                    if (isValidGeometry(feature.geometry)) {
                        addFeatureToMap(feature, index);
                        validFeaturesCount++;
                    } else {
                        console.warn('Geometria inválida ignorada:', feature);
                    }
                });

                console.log(`Processadas ${validFeaturesCount} features válidas de ${geoJsonData.features.length} totais`);

                // Atualizar interface
                updateCounters(geoJsonData);
                updateFeatureList(geoJsonData);
                
                // Só faz zoom se houver features válidas
                if (validFeaturesCount > 0) {
                    zoomToAll();
                } else {
                    updateStatus('Nenhuma geometria válida encontrada');
                }
                
                // Atualizar timestamp
                if (elements.lastUpdate) {
                    elements.lastUpdate.textContent = new Date().toLocaleTimeString();
                }
            }

            // Validar geometria
            function isValidGeometry(geometry) {
                if (!geometry || !geometry.type || !geometry.coordinates) {
                    return false;
                }

                try {
                    switch(geometry.type) {
                        case 'Point':
                            return Array.isArray(geometry.coordinates) && 
                                   geometry.coordinates.length >= 2 &&
                                   !isNaN(geometry.coordinates[0]) && 
                                   !isNaN(geometry.coordinates[1]);
                        case 'LineString':
                            return Array.isArray(geometry.coordinates) && 
                                   geometry.coordinates.length >= 2 &&
                                   geometry.coordinates.every(coord => 
                                       Array.isArray(coord) && coord.length >= 2 &&
                                       !isNaN(coord[0]) && !isNaN(coord[1])
                                   );
                        case 'Polygon':
                            return Array.isArray(geometry.coordinates) && 
                                   geometry.coordinates.length > 0 &&
                                   geometry.coordinates.every(ring => 
                                       Array.isArray(ring) && ring.length >= 4 &&
                                       ring.every(coord => 
                                           Array.isArray(coord) && coord.length >= 2 &&
                                           !isNaN(coord[0]) && !isNaN(coord[1])
                                       )
                                   );
                        default:
                            return false;
                    }
                } catch (error) {
                    console.warn('Erro ao validar geometria:', error);
                    return false;
                }
            }

            // Adicionar feature ao mapa
            function addFeatureToMap(feature, index) {
                let geometry, symbol;

                try {
                    // Criar geometria baseada no tipo
                    switch(feature.geometry.type) {
                        case 'Point':
                            geometry = new Point({
                                x: feature.geometry.coordinates[0],
                                y: feature.geometry.coordinates[1],
                                spatialReference: { wkid: 4326 }
                            });
                            symbol = symbols.point;
                            break;

                        case 'LineString':
                            const paths = feature.geometry.coordinates.map(coord => [coord[0], coord[1]]);
                            geometry = new Polyline({
                                paths: [paths],
                                spatialReference: { wkid: 4326 }
                            });
                            symbol = symbols.linestring;
                            break;

                        case 'Polygon':
                            const rings = feature.geometry.coordinates.map(ring => 
                                ring.map(coord => [coord[0], coord[1]])
                            );
                            geometry = new Polygon({
                                rings: rings,
                                spatialReference: { wkid: 4326 }
                            });
                            symbol = symbols.polygon;
                            break;

                        default:
                            console.warn('Tipo de geometria não suportado:', feature.geometry.type);
                            return;
                    }

                    // Verificar se a geometria é válida
                    if (!geometry || !geometry.type) {
                        console.warn('Geometria inválida criada para feature:', feature);
                        return;
                    }

                    const graphic = new Graphic({
                        geometry: geometry,
                        symbol: symbol,
                        attributes: {
                            id: feature.id || index,
                            name: feature.properties?.name || `Feature ${index + 1}`,
                            type: feature.geometry.type,
                            properties: JSON.stringify(feature.properties, null, 2),
                            originalIndex: index
                        },
                        popupTemplate: {
                            title: "{name}",
                            content: `
                                <div>
                                    <strong>ID:</strong> {id}<br>
                                    <strong>Tipo:</strong> {type}<br>
                                    <strong>Propriedades:</strong><br>
                                    <pre>{properties}</pre>
                                </div>
                            `
                        }
                    });

                    graphicsLayer.add(graphic);
                    featureGraphics.push({
                        graphic: graphic,
                        feature: feature,
                        originalSymbol: symbol
                    });

                } catch (error) {
                    console.error('Erro ao adicionar feature ao mapa:', error, feature);
                }
            }

            // Função para destacar feature
            function highlightFeature(graphic, permanent = false) {
                // Primeiro, limpar todos os highlights
                clearHighlights();

                const featureType = graphic.attributes.type;
                let highlightSymbol;

                switch(featureType) {
                    case 'Point':
                        highlightSymbol = symbols.highlighted.point;
                        break;
                    case 'LineString':
                        highlightSymbol = symbols.highlighted.linestring;
                        break;
                    case 'Polygon':
                        highlightSymbol = symbols.highlighted.polygon;
                        break;
                    default:
                        return;
                }

                // Aplicar highlight
                graphic.symbol = highlightSymbol;

                if (!permanent) {
                    setTimeout(() => {
                        resetFeatureStyle(graphic);
                    }, 3000);
                }
            }

            // Função para resetar estilo da feature
            function resetFeatureStyle(graphic) {
                const featureData = featureGraphics.find(fg => fg.graphic === graphic);
                if (featureData) {
                    graphic.symbol = featureData.originalSymbol;
                }
            }

            // Função para limpar destaques
            function clearHighlights() {
                featureGraphics.forEach(({ graphic, originalSymbol }) => {
                    if (graphic && originalSymbol) {
                        graphic.symbol = originalSymbol;
                    }
                });
                updateFeatureListSelection(null);
            }

            // Função para limpar o mapa
            function clearMap() {
                graphicsLayer.removeAll();
                featureGraphics = [];
                updateCounters({ features: [] });
                updateFeatureList({ features: [] });
                updateStatus('Mapa limpo');
            }

            // Função para dar zoom em todas as features
            function zoomToAll() {
                if (featureGraphics.length === 0) {
                    updateStatus('Nenhuma feature para dar zoom');
                    return;
                }

                try {
                    // Coletar todas as geometrias válidas
                    const validGeometries = featureGraphics
                        .map(({ graphic }) => graphic.geometry)
                        .filter(geom => geom && geom.extent);

                    if (validGeometries.length === 0) {
                        updateStatus('Nenhuma geometria válida para zoom');
                        return;
                    }

                    // Calcular a extensão total
                    let totalExtent = null;
                    
                    validGeometries.forEach(geometry => {
                        if (geometry && geometry.extent) {
                            if (!totalExtent) {
                                totalExtent = geometry.extent.clone();
                            } else {
                                totalExtent = totalExtent.union(geometry.extent);
                            }
                        }
                    });

                    if (totalExtent && totalExtent.xmin !== undefined) {
                        view.goTo(totalExtent).then(() => {
                            updateStatus('Zoom ajustado para todas as features');
                        }).catch(error => {
                            console.warn("Erro ao dar zoom:", error);
                            updateStatus('Erro ao ajustar zoom');
                        });
                    } else {
                        // Fallback: zoom para uma extensão padrão
                        view.goTo({
                            center: [-47, -15],
                            zoom: 4
                        });
                        updateStatus('Zoom ajustado para visualização padrão');
                    }

                } catch (error) {
                    console.error('Erro no zoom automático:', error);
                    // Fallback seguro
                    view.goTo({
                        center: [-47, -15],
                        zoom: 4
                    });
                    updateStatus('Zoom ajustado para visualização padrão');
                }
            }

            // Atualizar contadores
            function updateCounters(geoJsonData) {
                if (!elements.featureCount || !elements.pointCount || 
                    !elements.linestringCount || !elements.polygonCount) {
                    return;
                }

                const counts = {
                    point: 0,
                    linestring: 0,
                    polygon: 0
                };

                geoJsonData.features.forEach(feature => {
                    const type = feature.geometry.type.toLowerCase();
                    if (counts.hasOwnProperty(type)) {
                        counts[type]++;
                    }
                });

                elements.featureCount.textContent = geoJsonData.features.length;
                elements.pointCount.textContent = `${counts.point} Pontos`;
                elements.linestringCount.textContent = `${counts.linestring} Linhas`;
                elements.polygonCount.textContent = `${counts.polygon} Polígonos`;
            }

            // Atualizar lista de features
            function updateFeatureList(geoJsonData) {
                if (!elements.featureList || !elements.noFeatures) {
                    return;
                }

                if (geoJsonData.features.length === 0) {
                    elements.featureList.innerHTML = '<h4>Features Carregadas:</h4>';
                    elements.noFeatures.style.display = 'block';
                    return;
                }
                
                elements.noFeatures.style.display = 'none';
                elements.featureList.innerHTML = '<h4>Features Carregadas:</h4>';
                
                geoJsonData.features.forEach((feature, index) => {
                    const featureItem = document.createElement('div');
                    featureItem.className = 'feature-item';
                    featureItem.dataset.index = index;
                    
                    const geometryType = feature.geometry.type;
                    const name = feature.properties?.name || `Feature ${index + 1}`;
                    const coordinates = JSON.stringify(feature.geometry.coordinates).substring(0, 80) + '...';
                    const properties = JSON.stringify(feature.properties, null, 2);
                    
                    featureItem.innerHTML = `
                        <div>
                            <span class="geometry-badge ${geometryType.toLowerCase()}">${geometryType}</span>
                            <strong>${name}</strong>
                        </div>
                    `;
                    
                    featureItem.addEventListener('click', function() {
                        const graphicData = featureGraphics[index];
                        if (graphicData && graphicData.graphic) {
                            highlightFeature(graphicData.graphic, true);
                            view.goTo(graphicData.graphic.geometry).catch(() => {
                                console.warn('Não foi possível dar zoom na geometria específica');
                            });
                            view.popup.open({
                                features: [graphicData.graphic],
                                location: graphicData.graphic.geometry
                            });
                            updateFeatureListSelection(feature);
                        }
                    });
                    
                    elements.featureList.appendChild(featureItem);
                });
            }

            // Atualizar seleção na lista
            function updateFeatureListSelection(selectedFeature) {
                const featureItems = document.querySelectorAll('.feature-item');
                featureItems.forEach(item => {
                    item.classList.remove('active');
                });
                
                if (selectedFeature) {
                    const index = featureGraphics.findIndex(({feature}) => feature === selectedFeature);
                    if (index !== -1) {
                        const item = document.querySelector(`.feature-item[data-index="${index}"]`);
                        if (item) {
                            item.classList.add('active');
                        }
                    }
                }
            }

            // Funções de UI
            function showLoading() {
                if (elements.loading) {
                    elements.loading.style.display = 'block';
                }
            }

            function hideLoading() {
                if (elements.loading) {
                    elements.loading.style.display = 'none';
                }
            }

            function showError(message) {
                if (elements.error && elements.errorMessage) {
                    elements.errorMessage.textContent = message;
                    elements.error.style.display = 'block';
                }
            }

            function hideError() {
                if (elements.error) {
                    elements.error.style.display = 'none';
                }
            }

            function updateStatus(message) {
                if (elements.status) {
                    elements.status.textContent = message;
                }
            }


            document.addEventListener('DOMContentLoaded', function() {

                initMap();
                
                // Adicionar event listeners aos botões
                document.getElementById('load-btn').addEventListener('click', loadGeoJSON);
                document.getElementById('zoom-btn').addEventListener('click', zoomToAll);
                document.getElementById('clear-map-btn').addEventListener('click', clearMap);
                
                updateStatus('Pronto para carregar dados da API');
                
                setTimeout(() => {
                    loadGeoJSON();
                }, 2000);
            });

        });
    </script>
</body>
</html>
