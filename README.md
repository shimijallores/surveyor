# Surveyor

Surveyor is a compact survey creation platform built with Laravel, Inertia, Vue, and TypeScript. It supports private survey distribution with access codes, public response flows, and creator-side analytics.

## Stack

- Laravel 12
- Inertia.js v2
- Vue 3
- TypeScript
- Tailwind CSS v4
- shadcn-vue

## Features

- Create, edit, publish, close, and delete surveys
- Protect survey access with private codes
- Collect anonymous public responses
- Review response summaries and analytics in the creator dashboard

## Getting Started

### Requirements

- PHP 8.2+
- Composer
- Node.js and npm

### Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
```

### Run Locally

```bash
composer run dev
```

This starts the Laravel server, queue listener, and Vite development server.

## Useful Commands

```bash
npm run dev
npm run build
npm run types:check
php artisan test
vendor/bin/pint --dirty --format agent
```

## License

This project is open-sourced under the MIT license.
