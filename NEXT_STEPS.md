# Pași Următori pentru Instalare

## ⚠️ IMPORTANT: Instalare Composer

Proiectul a fost creat, dar pentru a-l rula ai nevoie de **Composer** instalat.

### Windows - Instalare Composer

1. **Descarcă Composer:**
   - Accesează: https://getcomposer.org/Composer-Setup.exe
   - Rulează installer-ul descărcat

2. **Verifică instalarea:**
   ```bash
   composer --version
   ```
   Ar trebui să vezi: `Composer version 2.x.x`

## După Instalarea Composer

Rulează următoarele comenzi **în ordine**:

```bash
# 1. Navighează în directorul proiectului
cd d:\Programare\laravel-ai-agent

# 2. Instalează dependențele Laravel
composer install

# 3. Generează cheia aplicației
php artisan key:generate

# 4. Editează fișierul .env și adaugă cheia API Anthropic
# Deschide .env și înlocuiește:
# ANTHROPIC_API_KEY=sk-ant-api03-PUNE_CHEIA_TA_AICI
# cu cheia ta reală de la https://console.anthropic.com/

# 5. Pornește serverul
php artisan serve

# 6. Deschide browser-ul la:
# http://127.0.0.1:8000/login
# Username: admin
# Parola: parola123
```

## Obținere Cheie API Anthropic

1. Accesează: https://console.anthropic.com/
2. Creează cont / Login
3. Mergi la **Settings** → **API Keys**
4. Click pe **Create Key**
5. Copiază cheia și pune-o în `.env`

## Verificare Rapidă

După ce ai instalat Composer și dependențele:

```bash
# Verifică că toate fișierele sunt OK
php artisan about

# Rulează serverul
php artisan serve
```

## Fișiere Create

✅ Toate controllerele (Auth, Upload, Agent)
✅ Toate serviciile (Excel, Claude)
✅ Toate view-urile Blade (layout, login, upload, agent)
✅ Rutele configurate
✅ Middleware-ul de autentificare
✅ Fișierele de configurare (.env, config/*)
✅ Directoarele de storage

## Probleme Comune

**"composer: command not found"**
→ Composer nu este instalat sau nu este în PATH. Reinstalează Composer.

**"Class not found"**
→ Rulează: `composer dump-autoload`

**Erori la pornirea serverului**
→ Verifică că PHP >= 8.2 este instalat: `php --version`

## Suport

Consultă [README.md](README.md) pentru documentație completă și depanare.
