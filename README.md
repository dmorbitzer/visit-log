# VisitLog

A digital visitor tracking system for events and venues. Configurable time slots, role-based access and real-time tracking — built with Laravel, Inertia.js and React.

---

## Requirements

- PHP 8.4+
- Composer
- Node.js 20+
- npm

---

## Installation

**1. Clone the repository**

```bash
git clone git@github.com:dmorbitzer/visit-log.git
cd visit-log
```

**2. Run the setup script**

```bash
composer run setup
```

This installs all dependencies, sets up the `.env` file, generates the app key, runs migrations and builds the frontend.

---

## Development

```bash
composer run dev
```

Starts the Laravel server, queue worker, log viewer, Vite dev server and Reverb WebSocket server concurrently. The app is available at `http://localhost:8000`.

---

## Testing

```bash
# PHP: lint + PHPUnit
composer run test

# PHP only
php artisan test

# Frontend: Vitest
npm run test
```

---

## Code Quality

```bash
# PHP
composer run lint          # Pint auto-fix
composer run lint:check    # Pint check only (CI)

# TypeScript
npm run types:check        # tsc --noEmit

# ESLint
npm run lint:check         # ESLint check

# Prettier
npm run format:check       # Prettier check
```

---

## API

The REST API uses Sanctum token authentication. Use the Bruno collection to explore and test all endpoints.

**Documentation:**

```bash
# Generate / update docs
php artisan scribe:generate

# View interactive docs
open http://localhost:8000/docs

# OpenAPI spec
http://localhost:8000/docs.openapi
```

---

## Bruno Collection

Import the API into [Bruno](https://www.usebruno.com):

1. `php artisan scribe:generate` (falls noch nicht generiert)
2. Bruno öffnen → **Import Collection** → **OpenAPI**
3. URL eingeben: `http://localhost:8000/docs.openapi`

Die Collection enthält alle Endpunkte mit Auth-Schema und Beispiel-Requests.

---

## Tech Stack

- [Laravel 13](https://laravel.com)
- [Inertia.js](https://inertiajs.com)
- [React](https://react.dev) + TypeScript
- [Tailwind CSS](https://tailwindcss.com)
- [shadcn/ui](https://ui.shadcn.com)
- [Laravel Reverb](https://reverb.laravel.com) (WebSockets)
- [Vite](https://vitejs.dev)
