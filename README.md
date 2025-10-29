# Gestão de Dados Georreferenciados

Sistema de gestão e visualização de dados georreferenciados desenvolvido com Laravel 12 e Filament 4, utilizando PostGIS para armazenamento espacial e ArcGIS Maps SDK para visualização interativa.

## Funcionalidades

- **Painel Administrativo** (`/painel`): Interface CRUD segura para gerenciar camadas geográficas
- **Mapa Público** (`/`): Mapa interativo exibindo todas as camadas registradas usando ArcGIS Maps SDK v4
- **Integração PostGIS**: Armazenamento e indexação de dados espaciais
- **Suporte Docker**: Configuração completa containerizada
- **API REST**: Endpoints para integração com outros sistemas
- **Validação GeoJSON**: Validação automática de arquivos GeoJSON

## Stack Tecnológico

- **Backend**: Laravel 12 com Filament 4
- **Banco de Dados**: PostgreSQL 15 com extensão PostGIS
- **Frontend Mapa**: ArcGIS Maps SDK for JavaScript v4
- **Autenticação**: Sistema de autenticação integrado do Filament
- **Containerização**: Docker Compose
- **Validação**: Regras customizadas para validação de GeoJSON

## Pré-requisitos

- Docker e Docker Compose
- Git

## Instalação e Configuração

### 1. Clonar o Repositório

```bash
git clone https://github.com/leosanlima/geo-layers-dashboard.git
cd geo-layers-dashboard
```

### 2. Configuração do Ambiente

O arquivo `.env.example` já está configurado com as configurações corretas. Ele será copiado automaticamente para `.env` durante a inicialização do container.

**Configurações incluídas:**
- Nome da aplicação: "Gestão de Dados Georreferenciados"
- Banco PostgreSQL com PostGIS
- Chave de aplicação gerada
- Configurações de produção

### 3. Executar com Docker

Iniciar a aplicação:

```bash
docker-compose up -d
```

Isso irá:
- Iniciar PostgreSQL 15 com extensão PostGIS
- Construir e iniciar a aplicação Laravel
- Executar migrações e seeders automaticamente
- Criar um usuário administrador
- Copiar `.env.example` para `.env` automaticamente

### 4. Acessar a Aplicação

- **Mapa Público**: http://localhost:8000
- **Painel Administrativo**: http://localhost:8000/painel

### 5. Credenciais do Administrador

Usuário administrador padrão:
- **Email**: admin@example.com
- **Senha**: password

## Usage

### Admin Panel (`/painel`)

1. Login with the admin credentials
2. Navigate to "Layers" in the sidebar
3. Click "Create" to add a new layer
4. Fill in the layer name
5. Upload a GeoJSON file containing geometry data
6. Save the layer

### Public Map (`/`)

The public map automatically loads and displays all registered layers from the database. Features include:

- Interactive map with zoom and pan
- Legend showing layer information
- Popup details for each layer
- Responsive design

## API Endpoints

- `GET /api/layers` - Returns all layers as GeoJSON FeatureCollection

## Project Structure

```
geo-layers-dashboard/
├── app/
│   ├── Filament/Resources/Layers/     # Filament admin resources
│   ├── Http/Controllers/Api/          # API controllers
│   └── Models/                        # Eloquent models
├── database/
│   ├── migrations/                    # Database migrations
│   └── seeders/                       # Database seeders
├── resources/views/                   # Blade templates
├── routes/                            # Route definitions
├── docker-compose.yml                 # Docker orchestration
├── Dockerfile                         # Application container
└── README.md                          # This file
```

## Database Schema

### Layers Table

- `id`: Auto-increment primary key
- `name`: String (max 100 characters)
- `geometry`: PostGIS geometry column (indexed)
- `created_at`, `updated_at`: Timestamps

## GeoJSON Format

The application accepts GeoJSON files in the following formats:

1. **Feature**: Single geometry with properties
2. **FeatureCollection**: Multiple features (uses first feature's geometry)

Example GeoJSON:

```json
{
  "type": "Feature",
  "properties": {
    "name": "Sample Layer"
  },
  "geometry": {
    "type": "Polygon",
    "coordinates": [[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]
  }
}
```

## Development

### Running Migrations

```bash
docker-compose exec app php artisan migrate
```

### Running Seeders

```bash
docker-compose exec app php artisan db:seed
```

### Accessing the Container

```bash
docker-compose exec app bash
```

### Viewing Logs

```bash
docker-compose logs -f app
```

## Troubleshooting

### Database Connection Issues

1. Ensure PostgreSQL container is running: `docker-compose ps`
2. Check database logs: `docker-compose logs postgres`
3. Verify environment variables in `.env`

### Filament Admin Panel Issues

1. Clear application cache: `docker-compose exec app php artisan cache:clear`
2. Clear config cache: `docker-compose exec app php artisan config:clear`
3. Check file permissions: `docker-compose exec app chmod -R 755 storage bootstrap/cache`

### Map Not Loading Layers

1. Check API endpoint: `curl http://localhost:8000/api/layers`
2. Verify GeoJSON format in uploaded files
3. Check browser console for JavaScript errors


