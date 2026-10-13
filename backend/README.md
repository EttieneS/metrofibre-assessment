# MetroFibre backend

This is a dependency-free PHP 5.5.38 implementation of the MetroFibre recipe optimisation problem.

## Layer responsibilities

- `public/index.php` is the front controller. It bootstraps concrete dependencies, reads the HTTP request, and delegates routing.
- `app/Models` contains domain entities (`Recipe` and `Inventory`) without HTTP behavior.
- `app/Repositories` provides recipe definitions, ingredient names, and the default inventory. There is no database because the assessment data is fixed.
- `app/Services` contains the optimisation business logic. `OptimisationService` receives its recipe repository through constructor injection and has no HTTP dependencies, so it can be tested directly.
- `app/Controllers` validates the JSON request and maps the service result to a consistent `JsonResponse`. It contains no optimisation logic.
- `app/Http` contains the lightweight router and response value object.
- `tests` contains the executable backend test suite.

The dependency direction is Controller → Service → Repository. The controller also receives the inventory repository to validate the supported input keys. No framework, Composer package, database, authentication, factory, or unnecessary abstraction is used.

## API

`POST /api/optimise` is the logical route. The front controller also accepts `POST /` and `POST /index.php` so it works with the current Apache document root without changing Apache configuration.

```json
{
  "ingredients": {
    "cucumber": 2,
    "olives": 2,
    "lettuce": 3,
    "meat": 6,
    "tomato": 6,
    "cheese": 8,
    "dough": 10
  }
}
```

Successful responses contain `success: true` and `data.people` plus all optimal combinations. Invalid JSON or ingredient data returns HTTP 400; unsupported content types return 415; unsupported methods return 405; unknown routes return 404.

## Tests

Run with the Laragon PHP 5.5.38 binary:

```text
C:\laragon\bin\php\php-5.5.38\php.exe tests/run.php
```
