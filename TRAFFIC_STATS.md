# Traffic Statistics & Client Management Features

## New Features Added (2025-11-06)

### 1. Traffic Statistics Tracking

**Database Changes:**
- Added `bytes_sent` - Total bytes uploaded by client
- Added `bytes_received` - Total bytes downloaded by client  
- Added `last_handshake` - Last successful WireGuard connection time
- Added `last_sync_at` - Last time stats were synced from server

**Backend Methods:**
- `VpnClient->syncStats()` - Sync single client statistics from server
- `VpnClient::syncAllStatsForServer($serverId)` - Sync all clients on a server
- `VpnClient->getFormattedStats()` - Get human-readable stats (KB, MB, GB)
- `VpnClient::getClientStatsFromServer()` - Parse `wg show` output

**How it works:**
1. Connects to server via SSH
2. Runs `wg show wg0 dump` inside Docker container
3. Parses output to extract transfer statistics
4. Updates database with latest stats
5. Calculates "last seen" based on handshake time

### 2. Client Access Control

**Revoke Access:**
- Temporarily disable client without deleting
- Removes peer from server WireGuard config
- Keeps client record in database with status='disabled'
- Can be restored later

**Restore Access:**
- Re-enable previously revoked client
- Re-adds peer to server WireGuard config
- Changes status back to 'active'

**Delete Client:**
- Permanently removes client
- First revokes access (removes from server)
- Then deletes from database

**Backend Methods:**
- `VpnClient->revoke()` - Disable client access
- `VpnClient->restore()` - Re-enable client access
- `VpnClient->delete()` - Permanently delete client
- `VpnClient::removeClientFromServer()` - Remove peer from wg0.conf
- `VpnClient::removeFromClientsTable()` - Remove from clientsTable JSON

### 3. Web Interface Updates

**Server View Page (`/servers/{id}`):**
- Added "Sync Stats" button - refreshes all client stats
- Enhanced client table with:
  - Status badge (Active/Disabled)
  - Traffic columns (Upload/Download)
  - Last seen timestamp
  - Action buttons (View, Revoke/Restore, Delete)

**Client View Page (`/clients/{id}`):**
- Added traffic statistics panel
- Shows uploaded/downloaded/total bytes
- Displays last handshake time
- "Refresh" button to sync latest stats
- Real-time status indicator (Online if handshake < 5 min)
- Revoke/Restore button based on current status

### 4. API Endpoints

All endpoints require authentication (session-based, JWT planned).

**GET `/api/clients/{id}`**
```json
{
  "success": true,
  "client": {
    "id": 1,
    "name": "client1",
    "server_id": 1,
    "client_ip": "10.8.1.2",
    "status": "active",
    "created_at": "2025-11-06 12:00:00",
    "stats": {
      "sent": "1.5 GB",
      "received": "3.2 GB",
      "total": "4.7 GB",
      "last_seen": "Online",
      "is_online": true
    },
    "bytes_sent": 1610612736,
    "bytes_received": 3435973836,
    "last_handshake": "2025-11-06 14:30:00"
  }
}
```

**POST `/api/clients/{id}/revoke`**
```json
{
  "success": true,
  "message": "Client revoked"
}
```

**POST `/api/clients/{id}/restore`**
```json
{
  "success": true,
  "message": "Client restored"
}
```

**GET `/api/servers/{id}/clients`**
Returns all clients with synced stats for a server.

```json
{
  "success": true,
  "clients": [
    {
      "id": 1,
      "name": "client1",
      "client_ip": "10.8.1.2",
      "status": "active",
      "stats": {...},
      ...
    }
  ]
}
```

**POST `/servers/{id}/sync-stats`**
```json
{
  "success": true,
  "synced": 5
}
```

**POST `/clients/{id}/sync-stats`**
```json
{
  "success": true,
  "stats": {
    "sent": "1.5 GB",
    "received": "3.2 GB",
    "total": "4.7 GB",
    "last_seen": "Online"
  }
}
```

## Usage Examples

### Web Interface

1. **View Client Statistics:**
   - Go to server page
   - Click "Sync Stats" to refresh all clients
   - View traffic in table or click client for details

2. **Revoke Client Access:**
   - In server client list, click "Revoke" next to active client
   - Confirm action
   - Client status changes to "Disabled"
   - Client can no longer connect to VPN

3. **Restore Client Access:**
   - Find disabled client in list
   - Click "Restore"
   - Client status changes to "Active"
   - Client can connect again

### API Usage (for Telegram Bot)

```php
// Get client with stats
$response = $api->get('/api/clients/1');
$client = $response['client'];

echo "Traffic: {$client['stats']['total']}\n";
echo "Status: {$client['stats']['last_seen']}\n";

// Revoke access
$api->post('/api/clients/1/revoke');

// Restore access
$api->post('/api/clients/1/restore');

// Get all server clients with stats
$response = $api->get('/api/servers/1/clients');
foreach ($response['clients'] as $client) {
    echo "{$client['name']}: {$client['stats']['total']}\n";
}
```

## Technical Details

### WireGuard Stats Format

The `wg show wg0 dump` command returns:
```
private_key public_key preshared_key endpoint allowed_ips latest_handshake transfer_rx transfer_tx persistent_keepalive
```

We parse:
- `latest_handshake` - Unix timestamp of last handshake
- `transfer_rx` - Bytes received by server (client sent)
- `transfer_tx` - Bytes sent by server (client received)

### Peer Removal

Removing peer from `wg0.conf`:
```bash
# Find and delete [Peer] block with matching PublicKey
sed -i '/^\[Peer\]/,/^$/{/PublicKey = <key>/,/^$/d}' /opt/amnezia/awg/wg0.conf

# Apply changes without restart
wg syncconf wg0 <(wg-quick strip /opt/amnezia/awg/wg0.conf)
```

### Client Status Logic

- **Online:** Last handshake < 5 minutes ago
- **Recently seen:** Last handshake < 1 hour ago  
- **Offline:** Last handshake > 1 hour ago
- **Never connected:** No handshake recorded

## Database Migration

Migration file: `migrations/002_add_traffic_stats.sql`

To apply manually:
```bash
docker compose exec -T db mysql -u root -prootpassword amnezia_panel < migrations/002_add_traffic_stats.sql
```

## Performance Considerations

- Stats sync requires SSH connection to server
- Each sync runs `wg show wg0 dump` command
- For many clients, use batch sync: `VpnClient::syncAllStatsForServer()`
- Consider caching stats and refreshing periodically (e.g., every 5 minutes)
- Stats updates are logged in `last_sync_at` column

## Future Enhancements

- [ ] Automatic periodic stats sync (cron job)
- [ ] Traffic usage alerts (email/Telegram)
- [ ] Bandwidth limits per client
- [ ] Historical traffic graphs
- [ ] Export stats to CSV
- [ ] Real-time WebSocket updates
- [ ] Client connection notifications

## Troubleshooting

**Stats not syncing:**
1. Check server SSH connection
2. Verify Docker container is running: `docker ps | grep awg`
3. Check `wg show wg0` output inside container
4. Review error logs

**Client still connecting after revoke:**
1. Check if peer was removed from wg0.conf
2. Verify `wg syncconf` was executed
3. Restart WireGuard: `docker exec <container> wg-quick down wg0 && wg-quick up wg0`

**Last handshake not updating:**
1. Ensure client is actually connected
2. Check WireGuard keepalive settings (should be 25 seconds)
3. Verify server time is synchronized (NTP)

---

**Last Updated:** 2025-11-06  
**Version:** 1.1.0
