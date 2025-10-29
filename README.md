# Geo Layers Dashboard

A Laravel 11 application with Filament 4 admin panel for managing geographic layers using PostGIS and displaying them on an interactive map with ArcGIS Maps SDK.

## Features

- **Admin Panel** (`/painel`): Secure CRUD interface for managing geographic layers
- **Public Map** (`/`): Interactive map displaying all registered layers using ArcGIS Maps SDK v4
- **PostGIS Integration**: Spatial data storage and indexing
- **Docker Support**: Complete containerized setup

## Tech Stack

- **Backend**: Laravel 11 with Filament 4
- **Database**: PostgreSQL 15 with PostGIS extension
- **Frontend Map**: ArcGIS Maps SDK for JavaScript v4
- **Authentication**: Filament's built-in auth system
- **Containerization**: Docker Compose

## Prerequisites

- Docker and Docker Compose
- Git

## Installation & Setup

### 1. Clone the Repository

```bash
git clone <repository-url>
cd geo-layers-dashboard
```

### 2. Environment Configuration

Copy the environment file:

```bash
cp .env.example .env
```

The `.env` file is already configured for Docker setup with PostgreSQL/PostGIS.

### 3. Run with Docker

Start the application:

```bash
docker-compose up -d
```

This will:
- Start PostgreSQL 15 with PostGIS extension
- Build and start the Laravel application
- Run migrations and seeders automatically
- Create an admin user

### 4. Access the Application

- **Public Map**: http://localhost:8000
- **Admin Panel**: http://localhost:8000/painel

### 5. Admin Credentials

Default admin user:
- **Email**: admin@example.com
- **Password**: password

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


