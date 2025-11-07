# Testing Guide

This document describes how to test the Amnezia VPN Web Panel.

## Prerequisites

- Docker and Docker Compose installed
- Test VPS server with SSH access (for full deployment testing)
- Amnezia VPN mobile app (Android/iOS) for QR code testing

## Quick Test Setup

### 1. Start the Application

```bash
cd amnezia-web-panel
docker compose up -d
```

### 2. Access the Panel

Open browser: `http://localhost:8082`

### 3. Login

Default credentials:
- Email: `admin@amnez.ia`
- Password: `admin123`

## Unit Tests

### Test QR Code Generation

```bash
docker compose exec web php test_qr.php
```

Expected output:
```
✅ Success! QR code generation working correctly.
```

This creates `test_qr.png` in the project root.

### Verify QR Code Payload

```bash
# Compare payload with original implementation
php /tmp/test_compare_qr.php
```

The payload should match exactly with the original Amnezia QR format.

## Integration Tests

### Test 1: User Registration

1. Logout from admin account
2. Click "Register"
3. Fill in:
   - Name: "Test User"
   - Email: "test@example.com"
   - Password: "testpass123"
4. Click "Register"
5. ✅ Should redirect to dashboard

### Test 2: Server Creation (Without Deployment)

1. Go to "Servers" → "Add Server"
2. Fill in:
   - Name: "Test Server"
   - Host: "192.168.1.100"
   - Port: 22
   - Username: "root"
   - Password: "dummy"
3. Click "Add Server" (will fail at deployment, but server record created)
4. ✅ Should see server in list with "pending" status

### Test 3: Full Server Deployment (Requires Real VPS)

**Prerequisites**: Remote Linux server with SSH access

1. Go to "Servers" → "Add Server"
2. Fill in real server credentials:
   - Name: "Production Server 1"
   - Host: "your.server.ip"
   - Port: 22
   - Username: "root"
   - Password: "your_ssh_password"
3. Click "Add Server"
4. Wait for deployment (5-10 minutes)
5. ✅ Server status should change to "active"
6. ✅ Server should show public key and VPN port

### Test 4: Client Creation

**Prerequisites**: Active server from Test 3

1. Click on active server
2. In "Create Client" section, enter name: "test-client-1"
3. Click "Create"
4. ✅ Should redirect to client view page
5. ✅ Should see QR code displayed
6. ✅ "Download Config" button should work

### Test 5: QR Code Scanning

**Prerequisites**: Amnezia VPN app installed on phone

1. Create a client (Test 4)
2. Open Amnezia VPN app
3. Tap "Add server" → "Scan QR code"
4. Scan the QR code from web panel
5. ✅ Configuration should be imported successfully
6. ✅ Connect to VPN should work
7. ✅ Check IP address changed (e.g., whatismyip.com)

### Test 6: Configuration Download

1. Go to client details page
2. Click "Download Config"
3. ✅ Should download `.conf` file
4. Open file in text editor
5. ✅ Should contain valid WireGuard config with:
   - [Interface] section with PrivateKey, Address, DNS
   - AWG parameters (Jc, Jmin, Jmax, S1, S2, H1-H4)
   - [Peer] section with PublicKey, PresharedKey, Endpoint
6. Import manually into Amnezia VPN app
7. ✅ Should work same as QR code

### Test 7: Multiple Clients

1. Create 5 clients on same server
2. ✅ Each should get unique IP (10.8.1.2, 10.8.1.3, etc.)
3. ✅ Each should have unique keys
4. ✅ All QR codes should scan successfully
5. Test connections from multiple devices
6. ✅ All should connect simultaneously

### Test 8: Client Deletion

1. Go to client details
2. Click "Delete"
3. ✅ Client should be removed from database
4. ⚠️ **Known Issue**: Not yet removed from server wg0.conf

### Test 9: Server Deletion

1. Go to server list
2. Click "Delete" on a server
3. ✅ Server should be removed from database
4. ⚠️ **Known Issue**: Docker container not removed from remote server

### Test 10: Access Control

1. Create new user account
2. Login as new user
3. Create a server
4. Logout and login as admin
5. ✅ Admin should see all servers (including user's)
6. Login as regular user
7. ✅ Regular user should only see their own servers

## Security Tests

### Test 11: SQL Injection Protection

Try creating server with malicious name:
```
Name: Test'; DROP TABLE vpn_servers; --
```

✅ Should be safely escaped, no SQL error

### Test 12: XSS Protection

Try creating client with script tag:
```
Name: <script>alert('XSS')</script>
```

✅ Should be HTML-escaped in output

### Test 13: Authentication

1. Logout
2. Try accessing `/dashboard` directly
3. ✅ Should redirect to login page

### Test 14: Password Security

1. Check database:
```bash
docker compose exec db mysql -u amnezia -pamnezia123 amnezia_panel
SELECT password FROM users LIMIT 1;
```

✅ Password should be bcrypt hash, not plaintext

## Performance Tests

### Test 15: Multiple Concurrent Requests

```bash
# Install Apache Bench
sudo apt install apache2-utils

# Test login endpoint
ab -n 100 -c 10 -p login.txt -T application/x-www-form-urlencoded http://localhost:8082/login
```

✅ Should handle 100 requests without errors

### Test 16: Database Connection Pooling

Create 10 clients rapidly:
```bash
for i in {1..10}; do
  curl -X POST http://localhost:8082/servers/1/clients/create \
    -d "name=client$i" \
    -b cookies.txt
done
```

✅ Should complete without connection errors

## Browser Compatibility

Test in:
- ✅ Chrome/Edge (Chromium)
- ✅ Firefox
- ✅ Safari
- ✅ Mobile browsers (iOS Safari, Chrome Android)

## Docker Tests

### Test 17: Container Health

```bash
docker compose ps
```

✅ Both containers should be "Up" and healthy

### Test 18: Volume Persistence

```bash
# Stop containers
docker compose down

# Start again
docker compose up -d

# Login
```

✅ All data should persist (servers, clients, users)

### Test 19: Logs

```bash
docker compose logs -f web
docker compose logs -f db
```

✅ No errors in logs during normal operation

## Troubleshooting

### QR Code Not Displaying

Check:
```bash
docker compose exec web php test_qr.php
```

If fails, check:
- GD extension installed: `php -m | grep gd`
- Composer dependencies: `composer show endroid/qr-code`

### Can't Connect to Database

Check:
```bash
docker compose exec web php -r "
\$pdo = new PDO('mysql:host=db;dbname=amnezia_panel', 'amnezia', 'amnezia123');
echo 'Connected successfully';
"
```

### SSH Deployment Fails

Test SSH manually:
```bash
sshpass -p 'yourpassword' ssh -o StrictHostKeyChecking=no root@server.ip 'echo OK'
```

## Test Checklist

Before releasing or deploying:

- [ ] All unit tests pass
- [ ] QR code generation works
- [ ] Server deployment works on real VPS
- [ ] Client creation works
- [ ] QR codes scan in Amnezia app
- [ ] VPN connection works
- [ ] Multiple clients work simultaneously
- [ ] Authentication works
- [ ] Access control works (user/admin)
- [ ] SQL injection protected
- [ ] XSS protected
- [ ] CSRF protection (if implemented)
- [ ] Password hashing verified
- [ ] All browsers work
- [ ] Mobile responsive
- [ ] Docker containers healthy
- [ ] Data persists after restart
- [ ] No errors in logs
- [ ] README instructions accurate
- [ ] Default password changed

## Automated Testing (Future)

Consider implementing:
- PHPUnit for unit tests
- Selenium for browser automation
- GitHub Actions for CI/CD
- Code coverage reports
- Automated security scanning

## Reporting Issues

When reporting bugs, include:
1. Steps to reproduce
2. Expected behavior
3. Actual behavior
4. Docker logs: `docker compose logs`
5. Browser console errors
6. PHP version: `docker compose exec web php -v`
7. MySQL version: `docker compose exec db mysql -V`

---

Happy Testing! 🧪
