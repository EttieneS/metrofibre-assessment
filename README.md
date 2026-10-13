# MetroFibre recipe optimiser

This repository contains the PHP 5.5 REST API and Angular 16 frontend for the recipe allocation assessment.

## Architecture

The backend is dependency-free and follows Controller → Service → Repository:

- `backend/public/index.php` bootstraps the API and Apache front controller.
- `backend/app/Controllers` validates HTTP requests.
- `backend/app/Services/OptimisationService.php` performs bounded exhaustive integer optimisation and returns every optimum.
- `backend/app/Repositories` contains the fixed assessment recipes and default inventory.
- `frontend/src/app/services/optimisation.service.ts` calls the same-origin API; `AppComponent` owns the inventory/results page.

The optimiser maximises people fed subject to shared ingredient quantities. Recipes are whole integer units and may be repeated while inventory remains. The default inventory produces 12 people fed and five optimal combinations.

## API

`POST /api/optimise` requires `Content-Type: application/json` and this body:

```json
{"ingredients":{"cucumber":2,"olives":2,"lettuce":3,"meat":6,"tomato":6,"cheese":8,"dough":10}}
```

Successful responses contain `success: true` and `data.people`, plus each combination's `recipes`, `used`, and `remaining` quantities. Invalid input returns a structured 400 response.

## Build and tests

From the repository root:

```text
C:\laragon\bin\php\php-5.5.38\php.exe backend\tests\run.php
cd frontend
npm ci
npm run build
    npm test -- --watch=false --browsers=ChromeHeadlessCI
```

The production build writes to `backend/public/app`, which is ignored because it is generated output. Angular Material 16.2.14 and CDK 16.2.14 are installed. The existing Laragon vhost serves that directory through HTTPS and runs PHP requests with PHP 5.5.38. `backend/public/.htaccess` routes `/api/*` to the PHP front controller and other routes to the Angular shell.

## Hosting

Use the existing Laragon vhost `metrofibre-assessment.test`, enable Apache, and build the frontend. Browse to `https://metrofibre-assessment.test/`; the existing Laragon certificate is used. HTTP redirects to HTTPS. No `ng serve` process is required.

See [INSTALLATION.txt](INSTALLATION.txt) for the complete local setup and smoke-test commands.
