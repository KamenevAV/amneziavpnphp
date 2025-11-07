# Amnezia VPN Web Panel

Web-based management panel for Amnezia AWG (WireGuard) VPN servers.

## Features

- VPN server deployment via SSH
- Client configuration management
- Traffic statistics monitoring
- QR code generation for mobile apps
- Multi-language interface (English, Russian, Spanish, German, French, Chinese)
- REST API with JWT authentication
- User authentication and access control

## Requirements

- Docker
- Docker Compose

## Installation

```bash
git clone https://github.com/infosave2007/amneziavpnphp.git
cd amnezia-web-panel
cp .env.example .env
docker compose up -d
```

Access: http://localhost:8082

Default login: admin@amnez.ia / admin123

## Configuration

Edit `.env`:

```
DB_HOST=db
DB_PORT=3306
DB_DATABASE=amnezia_panel
DB_USERNAME=amnezia
DB_PASSWORD=amnezia123

ADMIN_EMAIL=admin@amnez.ia
ADMIN_PASSWORD=admin123

JWT_SECRET=your-secret-key-change-this
```

## Usage

### Add VPN Server

1. Servers → Add Server
2. Enter: name, host IP, SSH port, username, password
3. Click Deploy Server
4. Wait for deployment

### Create Client

1. Open server details
2. Enter client name
3. Click Create Client
4. Download config or scan QR code

### API Authentication

Get JWT token:
```bash
curl -X POST http://localhost:8082/api/auth/token \
  -d "email=admin@amnez.ia&password=admin123"
```

Use token:
```bash
curl -H "Authorization: Bearer <token>" \
  http://localhost:8082/api/servers
```

## API Endpoints

### Authentication
```
POST   /api/auth/token              - Get JWT token
POST   /api/tokens                  - Create persistent API token
GET    /api/tokens                  - List API tokens
DELETE /api/tokens/{id}             - Revoke token
```

### Servers
```
GET    /api/servers                 - List all servers
POST   /api/servers/create          - Create new server
       Parameters: name, host, port, username, password
DELETE /api/servers/{id}/delete     - Delete server by ID
GET    /api/servers/{id}/clients    - List clients on server
```

### Clients
```
GET    /api/clients                 - List all clients
GET    /api/clients/{id}/details    - Get client details with stats, config and QR code
GET    /api/clients/{id}/qr         - Get client QR code
POST   /api/clients/create          - Create new client (returns config and QR code)
       Parameters: server_id, name
POST   /api/clients/{id}/revoke     - Revoke client access
POST   /api/clients/{id}/restore    - Restore client access
DELETE /api/clients/{id}/delete     - Delete client by ID
```

## Translation

Add OpenRouter API key in Settings, then run:
```bash
docker compose exec app php bin/translate_all.php
```

Or translate via web interface: Settings → Auto-translate

## Structure

```
public/index.php      - Routes
inc/                  - Core classes
  Auth.php           - Authentication
  DB.php             - Database connection
  Router.php         - URL routing
  View.php           - Twig templates
  VpnServer.php      - Server management
  VpnClient.php      - Client management
  Translator.php     - Multi-language
  JWT.php            - Token auth
  QrUtil.php         - QR code generation
templates/           - Twig templates
migrations/          - SQL migrations
```

## Tech Stack

- PHP 8.2
- MySQL 8.0
- Twig 3
- Tailwind CSS
- Docker

## License

MIT
# amneziavpnphp
