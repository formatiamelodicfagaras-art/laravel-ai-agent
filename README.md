# Laravel AI Agent - Style Advertising

Aplicație Laravel cu autentificare basic și un agent AI powered by Claude API care citește date dintr-un fișier Excel și răspunde la întrebări.

## Cerințe de Sistem

- **PHP:** >= 8.2
- **Composer:** Latest version
- **Extensions PHP necesare:**
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - Fileinfo
  - zip

## Instalare

### 1. Instalare Composer (dacă nu este instalat)

**Windows:**
- Descarcă Composer de la: https://getcomposer.org/download/
- Rulează installer-ul și urmează pașii

**Verificare instalare:**
```bash
composer --version
```

### 2. Instalare Dependențe

Navighează în directorul proiectului și instalează dependențele:

```bash
cd laravel-ai-agent
composer install
```

### 3. Configurare Fișier .env

Fișierul `.env` a fost deja creat. Trebuie să editezi următoarele:

1. Deschide fișierul `.env`
2. Înlocuiește `PUNE_CHEIA_TA_AICI` cu cheia ta API de la Anthropic:

```env
ANTHROPIC_API_KEY=sk-ant-api03-YOUR_REAL_KEY_HERE
```

**Cum obții cheia API Anthropic:**
- Accesează: https://console.anthropic.com/
- Creează un cont sau loghează-te
- Mergi la Settings → API Keys
- Generează o nouă cheie API

### 4. Generare Application Key

```bash
php artisan key:generate
```

### 5. Pornește Serverul

```bash
php artisan serve
```

Aplicația va fi disponibilă la: **http://127.0.0.1:8000**

## Utilizare

### Login

1. Accesează: `http://127.0.0.1:8000/login`
2. Credențiale default:
   - **Username:** admin
   - **Parola:** parola123

*Poți schimba credențialele în fișierul `.env` (AUTH_USER și AUTH_PASS)*

### Upload Excel

1. După login, vei fi redirecționat la pagina de Upload
2. Selectează un fișier Excel (.xlsx sau .xls)
3. Click pe "Încarcă"
4. Vei vedea un tabel preview cu datele încărcate

**Format Excel necesar:**
- 4 coloane: Client | Produs | Cantitate | Preț unitar
- Prima linie = header
- Datele încep de la linia 2

**Exemplu:**
```
Client          | Produs              | Cantitate | Preț unitar
KUKA România    | Tricouri logo       | 50        | 12.50
Style Ads SRL   | Mape personalizate  | 20        | 8.00
KUKA România    | Pixuri branded      | 100       | 2.75
```

### AI Agent

1. Navighează la pagina "AI Agent" din meniul de sus
2. Scrie o întrebare în limbă naturală, exemple:
   - "Ce a cumpărat KUKA România?"
   - "Care este valoarea totală a vânzărilor către Style Ads SRL?"
   - "Arată-mi toate produsele vândute în cantitate mai mare de 30"
   - "Ce este prețul unitar al Tricurilor logo?"
3. Click pe "Întreabă"
4. Răspunsul Claude va apărea în chat
5. Istoricul ultimelor 10 conversații este păstrat în sesiune
6. Click "Șterge istoricul" pentru a reseta chat-ul

## Structura Proiectului

```
laravel-ai-agent/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php      # Login/Logout
│   │   │   ├── UploadController.php    # Upload Excel + Preview
│   │   │   └── AgentController.php     # Chat AI
│   │   └── Middleware/
│   │       └── AuthMiddleware.php      # Verificare autentificare
│   └── Services/
│       ├── ExcelService.php            # Citire Excel
│       └── ClaudeService.php           # API Claude
├── resources/views/
│   ├── layout.blade.php                # Template de bază
│   ├── login.blade.php                 # Pagina login
│   ├── upload.blade.php                # Pagina upload
│   └── agent.blade.php                 # Pagina AI agent
├── routes/
│   └── web.php                         # Toate rutele
├── storage/app/uploads/
│   └── data.xlsx                       # Excel uploadat
└── .env                                # Configurare
```

## Tehnologii Utilizate

- **Laravel 11** - Framework PHP
- **PhpOffice/PhpSpreadsheet** - Citire fișiere Excel
- **Anthropic Claude API** - AI Agent
- **Blade Templates** - Frontend (fără framework JS)

## Depanare

### Eroare: "composer: command not found"
- Asigură-te că Composer este instalat și adăugat în PATH
- Reinstalează Composer de la https://getcomposer.org

### Eroare: "Class not found"
```bash
composer dump-autoload
```

### Eroare: "Permission denied" la upload
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Eroare API Claude
- Verifică că `ANTHROPIC_API_KEY` în `.env` este corect
- Verifică că ai credite în contul Anthropic
- Verifică conexiunea la internet

### Session nu funcționează
```bash
# Șterge cache-ul
rm -rf storage/framework/sessions/*
```

## Securitate

- **IMPORTANT:** Nu expune fișierul `.env` public (conține cheia API)
- Credentialele de login sunt stocate în `.env` (nu în baza de date)
- Session-based authentication (fără JWT)
- CSRF protection activat pentru toate formularele

## Licență

Proprietate Style Advertising

## Contact

Pentru suport, contactează echipa de dezvoltare.