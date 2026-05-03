# VisitLog

A digital visitor tracking system for events and venues. Configurable time slots, role-based access and analytics — built with Laravel, Inertia.js and React.

---

## Requirements

- PHP 8.3+
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

Starts the Laravel server, queue worker, log viewer and Vite dev server concurrently. The app is available at `http://localhost:8000`.

---

## Testing

```bash
composer run test
```

Runs PHP linting (Pint) and the PHPUnit test suite.

---

## Code Quality

```bash
composer run lint
```

Auto-fixes PHP code style issues via Laravel Pint.

```bash
composer run lint:check
```

Checks PHP code style without making changes — used in CI.

---

## Tech Stack

- [Laravel 13](https://laravel.com)
- [Inertia.js](https://inertiajs.com)
- [React](https://react.dev) + TypeScript
- [Tailwind CSS](https://tailwindcss.com)
- [shadcn/ui](https://ui.shadcn.com)
- [Recharts](https://recharts.org) (dashboard)
- [Vite](https://vitejs.dev)