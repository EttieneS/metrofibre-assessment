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
$passed = 0;
$failed = 0;

function assertTrue($condition, $message)
{
    global $passed, $failed;
    if ($condition) { $passed++; return; }
    $failed++;
    echo 'FAIL: ' . $message . PHP_EOL;
}

function assertSameValue($expected, $actual, $message)
{
    assertTrue($expected === $actual, $message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true) . '.');
}

function vector($solution)
{
    return array_values($solution['recipes']);
}

function containsVector($solutions, $expected)
{
    foreach ($solutions as $solution) {
        if (vector($solution) === $expected) { return true; }
    }
    return false;
}

function requestBody($inventory)
{
    return json_encode(array('ingredients' => $inventory));
}

function independentMaximum($inventory)
{
    $best = 0;
    for ($burger = 0; $burger <= 3; $burger++) for ($pie = 0; $pie <= 3; $pie++) for ($sandwich = 0; $sandwich <= 2; $sandwich++) for ($pasta = 0; $pasta <= 6; $pasta++) for ($salad = 0; $salad <= 1; $salad++) for ($pizza = 0; $pizza <= 2; $pizza++) {
        $used = array(
            'cucumber' => $sandwich + $salad,
            'olives' => $salad + $pizza,
            'lettuce' => $burger + (2 * $salad),
            'meat' => $burger + (2 * $pie) + $pasta,
            'tomato' => $burger + $pasta + (2 * $salad) + (2 * $pizza),
            'cheese' => $burger + (2 * $pasta) + (2 * $salad) + (3 * $pizza),
            'dough' => $burger + (2 * $pie) + $sandwich + (2 * $pasta) + (3 * $pizza)
        );
        $feasible = true;
        foreach ($used as $ingredient => $quantity) {
            if ($quantity > $inventory[$ingredient]) { $feasible = false; break; }
        }
        if ($feasible) $best = max($best, $burger + $pie + $sandwich + (2 * $pasta) + (3 * $salad) + (4 * $pizza));
    }
    return $best;
}

$defaultInventory = $inventoryRepository->getDefault();
$default = $service->optimise($defaultInventory);
assertSameValue(12, $default['people'], 'Default inventory must feed 12 people.');
assertSameValue(12, independentMaximum($defaultInventory->getQuantities()), 'Independent calculation must find 12 people.');
assertSameValue(5, count($default['combinations']), 'Default inventory must have five optimal combinations.');
foreach (array(array(0, 0, 2, 1, 0, 2), array(0, 2, 1, 1, 1, 1), array(1, 1, 1, 1, 1, 1), array(1, 2, 0, 1, 1, 1), array(2, 0, 2, 0, 0, 2)) as $expected) {
    assertTrue(containsVector($default['combinations'], $expected), 'Expected optimal vector was not returned.');
}
foreach ($default['combinations'] as $solution) foreach ($defaultInventory->getQuantities() as $ingredient => $quantity) assertTrue($solution['used'][$ingredient] <= $quantity, 'Solution exceeds shared ' . $ingredient . ' inventory.');

$zero = new Inventory(array_fill_keys($inventoryRepository->getIngredientNames(), 0));
$zeroResult = $service->optimise($zero);
assertSameValue(0, $zeroResult['people'], 'Zero inventory must feed zero people.');
assertSameValue(1, count($zeroResult['combinations']), 'Zero inventory must have one zero-count optimum.');

$sharedQuantities = $defaultInventory->getQuantities();
$sharedQuantities['cucumber'] = 1;
$sharedQuantities['olives'] = 0;
$sharedResult = $service->optimise(new Inventory($sharedQuantities));
assertTrue($sharedResult['people'] < 12, 'Shared ingredient limits must affect the optimum.');
foreach ($sharedResult['combinations'] as $solution) { assertTrue($solution['used']['cucumber'] <= 1, 'Cucumber is shared across recipes.'); assertTrue($solution['used']['olives'] === 0, 'Olives are shared across recipes.'); }

$valid = $router->dispatch('POST', '/api/optimise', requestBody($defaultInventory->getQuantities()), 'application/json');
assertSameValue(200, $valid->getStatus(), 'Valid API request must return 200.');
assertTrue($valid->getBody()['success'] === true, 'Valid API response must be successful.');
assertSameValue(12, $valid->getBody()['data']['people'], 'API must return the optimiser result.');
assertSameValue(5, count($valid->getBody()['data']['combinations']), 'API must return all optimal combinations.');

foreach (array('', '{"ingredients":{}}', '{"ingredients":{"cucumber":2,"olives":2,"lettuce":3,"meat":6,"tomato":6,"cheese":8,"dough":-1}}', '{"ingredients":{"cucumber":2,"olives":2,"lettuce":3,"meat":6,"tomato":6,"cheese":8,"dough":1.5}}', '{"ingredients":{"cucumber":2,"olives":2,"lettuce":3,"meat":6,"tomato":6,"cheese":8,"dough":10},"extra":true}', '{"ingredients":{"cucumber":2,"olives":2,"lettuce":3,"meat":6,"tomato":6,"cheese":8,"dough":10,"salt":1}}') as $body) {
    $response = $router->dispatch('POST', '/api/optimise', $body, 'application/json');
    assertSameValue(400, $response->getStatus(), 'Invalid request must return 400.');
    assertTrue($response->getBody()['success'] === false, 'Invalid request must return a failure response.');
}
assertSameValue(415, $router->dispatch('POST', '/api/optimise', requestBody($defaultInventory->getQuantities()), 'text/plain')->getStatus(), 'Unsupported content type must return 415.');
assertSameValue(405, $router->dispatch('GET', '/api/optimise', '', 'application/json')->getStatus(), 'Unsupported method must return 405.');
assertSameValue(404, $router->dispatch('POST', '/missing', '', 'application/json')->getStatus(), 'Unknown route must return 404.');

$greedyPeople = 1 + 2 + 8;
assertSameValue(11, $greedyPeople, 'Greedy counterexample must feed 11 people.');
assertTrue($default['people'] > $greedyPeople, 'Exhaustive optimum must beat the greedy counterexample.');

echo 'Passed: ' . $passed . PHP_EOL;
echo 'Failed: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
