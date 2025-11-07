# Translation Management

## Database Reset & Setup

All translations have been reset and reloaded with complete English keys (79 total).

### Migration Applied
- `migrations/006_full_translations.sql` - Complete translation reset with all 79 English keys

## Translation Keys Summary

### Categories:
- **Authentication** (5 keys): email, login, name, password, register
- **Clients** (17 keys): actions, add, delete, download_config, ip, etc.
- **Dashboard** (5 keys): active_clients, title, total_clients, etc.
- **Forms** (6 keys): cancel, close, loading, processing, save, submit
- **Menu** (6 keys): clients, dashboard, logout, servers, settings, users
- **Messages** (6 keys): confirm, deleted, deployed, error, saved, success
- **Servers** (12 keys): actions, add, clients, delete, deploy, etc.
- **Settings** (17 keys): api_keys, auto_translate, translations, etc.
- **Status** (5 keys): active, deploying, disabled, error, inactive

**Total: 79 keys**

## How to Translate All Languages

### Option 1: Via Web Interface
1. Login as admin: http://localhost:8082/login
2. Go to Settings: http://localhost:8082/settings
3. Add your OpenRouter API key
4. Click "Auto-translate" button for each language

### Option 2: Via Command Line (Recommended)
```bash
# First, add your OpenRouter API key via Settings page

# Then run the auto-translation script
docker compose exec app php bin/translate_all.php
```

This will automatically translate all 5 languages:
- 🇷🇺 Russian (ru)
- 🇪🇸 Spanish (es)
- 🇩🇪 German (de)
- 🇫🇷 French (fr)
- 🇨🇳 Chinese (zh)

### Option 3: Translate Single Language
```bash
# Translate only Russian
docker compose exec app php bin/translate.php ru

# Translate only Spanish
docker compose exec app php bin/translate.php es
```

## Current Status

After migration:
```
+---------------+-------+
| language_code | count |
+---------------+-------+
| en            |    79 |
+---------------+-------+
```

After auto-translation (expected):
```
+---------------+-------+
| language_code | count |
+---------------+-------+
| de            |    79 |
| en            |    79 |
| es            |    79 |
| fr            |    79 |
| ru            |    79 |
| zh            |    79 |
+---------------+-------+
```

## API Rate Limits

OpenRouter free models have rate limits:
- **gemini-2.0-flash-exp:free** - Primary model
- **meta-llama/llama-3.2-3b-instruct:free** - Fallback 1
- **google/gemini-flash-1.5** - Fallback 2

The translation script includes:
- Automatic retries with exponential backoff
- Model fallback on rate limits
- 5-second delay between languages
- Batch translation for efficiency

## Troubleshooting

### Error: "OpenRouter API key not found"
Add your API key via Settings page first:
1. Go to http://localhost:8082/settings
2. Enter your OpenRouter API key (format: `sk-or-v1-...`)
3. Click Save

### Error: "Rate limit exceeded"
Wait a few minutes and try again, or:
- Use the web interface (slower but more controlled)
- Increase delay in `bin/translate_all.php`
- Get a paid OpenRouter API key

### Check Translation Progress
```bash
docker compose exec db sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" amnezia_panel -e "
SELECT 
  l.code,
  l.name,
  COUNT(t.id) as translated,
  (SELECT COUNT(*) FROM translations WHERE language_code = \"en\") as total
FROM languages l
LEFT JOIN translations t ON l.code = t.language_code
GROUP BY l.code
ORDER BY l.code;
"'
```

## Manual Export/Import

### Export translations to JSON
```bash
docker compose exec app php -r "
require 'vendor/autoload.php';
require 'inc/Config.php';
require 'inc/DB.php';
require 'inc/Translator.php';
Config::load('.env');
DB::conn();
echo Translator::exportToJson('ru');
" > translations_ru.json
```

### Import from JSON
```php
Translator::importFromJson('ru', file_get_contents('translations_ru.json'));
```
