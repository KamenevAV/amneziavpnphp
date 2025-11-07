# Changelog

All notable changes to Amnezia VPN Web Panel will be documented in this file.

## [1.1.0] - 2025-11-06

### Added
- **Traffic Statistics Tracking**
  - Real-time bandwidth monitoring (upload/download)
  - Last handshake tracking
  - Online/offline status detection
  - Formatted stats display (B, KB, MB, GB, TB)
  - Manual sync stats button on server and client pages
  - Batch stats sync for all clients on server
  
- **Client Access Control**
  - Revoke client access (temporary disable)
  - Restore revoked clients
  - Improved delete with proper cleanup
  - Status badges (Active/Disabled)
  
- **Enhanced UI**
  - Traffic columns in client list table
  - Last seen timestamp display
  - Revoke/Restore/Delete action buttons
  - Real-time stats refresh (AJAX)
  - Online status indicator (green dot)
  - Improved client view page with stats panel
  
- **API Endpoints**
  - `GET /api/clients/{id}` - Get client with stats
  - `POST /api/clients/{id}/revoke` - Revoke client access
  - `POST /api/clients/{id}/restore` - Restore client access
  - `GET /api/servers/{id}/clients` - Get all clients with stats
  - `POST /servers/{id}/sync-stats` - Batch sync server clients
  - `POST /clients/{id}/sync-stats` - Sync single client stats

### Technical
- Database migration `002_add_traffic_stats.sql`
- New columns: `bytes_sent`, `bytes_received`, `last_handshake`, `last_sync_at`
- WireGuard stats parsing from `wg show` output
- Peer removal from wg0.conf using sed
- `wg syncconf` for live config updates

### Documentation
- Created `TRAFFIC_STATS.md` - Complete traffic stats guide
- API usage examples
- Troubleshooting section

## [1.0.0] - 2024-11-05

### Added
- Initial release of Amnezia VPN Web Management Panel
- Full VPN server deployment via SSH
- AmneziaWG container management on remote servers
- Client configuration creation and management
- QR code generation compatible with Amnezia VPN apps
- User authentication system (login/register/logout)
- Role-based access control (admin/user)
- Modern responsive UI with Tailwind CSS
- Dashboard with server and client overview
- Server CRUD operations (Create, Read, Update, Delete)
- Client management with download and QR code features
- Docker Compose deployment setup
- Database migrations system
- Twig template engine integration
- REST API foundation for future Telegram bot integration

### Technical Details
- PHP 8.2 backend
- MySQL 8.0 database
- Endroid QR Code library v5.x integration
- Qt/QDataStream compatible QR encoding (tested with Amnezia apps)
- AWG obfuscation parameters support (Jc, Jmin, Jmax, S1, S2, H1-H4)
- Secure password hashing with bcrypt
- PDO prepared statements for SQL injection prevention
- XSS protection via Twig auto-escaping

### Security
- Default admin account: admin@amnez.ia / admin123 (change immediately!)
- Bcrypt password hashing
- SQL injection prevention
- XSS protection
- Session-based authentication

### Known Issues
- QR code library updated to v5.x with API compatibility fixes
- Server deletion not yet removing Docker containers from remote servers
- Client deletion not yet updating server wg0.conf file
- API authentication (JWT) not yet implemented
- Rate limiting not yet implemented

### Infrastructure
- Docker container with PHP 8.2 Apache
- MySQL 8.0 container
- Docker Compose orchestration
- Volume persistence for database
- Composer dependency management

## [Unreleased]

### Planned Features
- JWT authentication for REST API
- Complete Telegram bot integration
- Server monitoring and health checks
- Bandwidth usage statistics
- Client traffic analysis
- Email notifications
- Two-factor authentication (2FA)
- Multi-language support
- Dark mode UI theme
- Automated backups
- Rate limiting for API endpoints
- Export/import configurations
- Server templates for quick deployment
- Client groups and tagging
- Advanced logging and audit trail
- User management admin panel

### Improvements Planned
- Better error handling and user feedback
- Real-time deployment progress updates
- Server resource monitoring (CPU, RAM, bandwidth)
- Client connection status tracking
- Automatic Let's Encrypt SSL setup
- Database connection pooling
- Caching layer (Redis)
- WebSocket support for real-time updates
- Mobile-responsive improvements
- Accessibility enhancements

---

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
