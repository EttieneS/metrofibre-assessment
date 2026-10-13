<?php

require_once dirname(__DIR__) . '/app/Models/Recipe.php';
require_once dirname(__DIR__) . '/app/Models/Inventory.php';
require_once dirname(__DIR__) . '/app/Repositories/RecipeRepository.php';
require_once dirname(__DIR__) . '/app/Repositories/InventoryRepository.php';
require_once dirname(__DIR__) . '/app/Services/OptimisationService.php';
require_once dirname(__DIR__) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__) . '/app/Controllers/OptimisationController.php';
require_once dirname(__DIR__) . '/app/Http/Router.php';

$recipeRepository = new RecipeRepository();
$inventoryRepository = new InventoryRepository();
$service = new OptimisationService($recipeRepository);
$controller = new OptimisationController($service, $inventoryRepository);
$router = new Router($controller);
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : (isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : '');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$response = $router->dispatch($_SERVER['REQUEST_METHOD'], $path, file_get_contents('php://input'), $contentType);

http_response_code($response->getStatus());
header('Content-Type: application/json');
foreach ($response->getHeaders() as $name => $value) {
    header($name . ': ' . $value);
}
echo json_encode($response->getBody());
