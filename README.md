# UI Cloner

A web application that allows users to input a website URL, clone its UI, and convert it into editable Vue 3 components using AI.

Built with **Laravel** (backend API), **Vue 3** (Options API), and **TailwindCSS**.

## Features

- **URL Cloning**: Enter any website URL to fetch and preview its HTML
- **HTML Sanitization**: Removes scripts, event handlers, and unsafe content
- **AI Conversion**: Converts cloned HTML into clean Vue 3 components (Options API + TailwindCSS)
- **Code Editor**: Monaco editor with syntax highlighting for viewing and editing code
- **Live Preview**: Split-screen layout with HTML preview and code editor
- **Copy to Clipboard**: One-click code copying

## Requirements

- PHP >= 8.1
- Composer
- Node.js >= 18
- NPM
- OpenAI API Key (for AI conversion feature)

## Setup

### 1. Clone the repository

```bash
git clone https://github.com/JCW77485/ui_cloner.git
cd ui_cloner
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node.js dependencies

```bash
npm install
```

### 4. Environment configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and add your OpenAI API key:

```
OPENAI_API_KEY=your-openai-api-key-here
```

### 5. Run the development servers

In one terminal, start the Laravel backend:

```bash
php artisan serve
```

In another terminal, start the Vite dev server:

```bash
npm run dev
```

Visit `http://localhost:8000` in your browser.

## Usage

1. Enter a website URL in the input field (e.g., `https://example.com`)
2. Click **Clone UI** to fetch and preview the website's HTML
3. View the cleaned HTML in the code editor (right panel)
4. Click **Convert to Vue** to convert the HTML into a Vue 3 component using AI
5. Edit the generated code in the Monaco editor
6. Copy the code using the **Copy** button

## API Endpoints

### POST /api/clone

Fetches and sanitizes HTML from a given URL.

**Request:**
```json
{
  "url": "https://example.com"
}
```

**Response:**
```json
{
  "html": "...cleaned html..."
}
```

### POST /api/convert

Converts HTML to a Vue 3 component using OpenAI.

**Request:**
```json
{
  "html": "<div>...</div>"
}
```

**Response:**
```json
{
  "vue_code": "..."
}
```

## Tech Stack

- **Backend**: Laravel 10, Guzzle HTTP
- **Frontend**: Vue 3 (Options API), TailwindCSS, Monaco Editor
- **AI**: OpenAI API (GPT-4o-mini)
- **Security**: DOMPurify (frontend), regex-based sanitization (backend)

## Project Structure

```
├── app/Http/Controllers/Api/
│   ├── CloneController.php      # URL cloning & HTML sanitization
│   └── ConvertController.php    # AI-powered Vue conversion
├── resources/
│   ├── js/
│   │   ├── app.js               # Vue app entry point
│   │   └── components/
│   │       └── AppComponent.vue # Main UI component (Options API)
│   ├── css/
│   │   └── app.css              # TailwindCSS imports
│   └── views/
│       └── app.blade.php        # Laravel blade template
├── routes/
│   ├── api.php                  # API routes
│   └── web.php                  # SPA catch-all route
└── config/
    └── services.php             # OpenAI config
```

## Security

- HTML is sanitized on the backend (removes scripts, event handlers, iframes, etc.)
- Frontend uses DOMPurify with a strict allowlist for rendering HTML
- No `<script>` tags or inline JavaScript is rendered
- OpenAI API key is stored server-side only

## License

MIT
