<?php

class OptimisationController
{
    private $optimisationService;
    private $inventoryRepository;

    public function __construct(OptimisationService $optimisationService, InventoryRepository $inventoryRepository)
    {
        $this->optimisationService = $optimisationService;
        $this->inventoryRepository = $inventoryRepository;
    }

    public function optimise($body, $contentType)
    {
        if (!$this->isJsonContentType($contentType)) {
            return $this->error(415, 'Content-Type must be application/json.');
        }
        $request = json_decode($body, true);
        if (!is_array($request) || json_last_error() !== JSON_ERROR_NONE) {
            return $this->error(400, 'Request body must be valid JSON.');
        }
        $validation = $this->validate($request);
        if ($validation !== null) {
            return $this->error(400, $validation);
        }

        $result = $this->optimisationService->optimise(new Inventory($request['ingredients']));
        return new JsonResponse(200, array('success' => true, 'data' => $result));
    }

    private function validate($request)
    {
        if (count($request) !== 1 || !array_key_exists('ingredients', $request) || !is_array($request['ingredients'])) {
            return 'Request must contain only an ingredients object.';
        }
        $ingredients = $request['ingredients'];
        $expected = $this->inventoryRepository->getIngredientNames();
        if (count($ingredients) !== count($expected)) {
            return 'Ingredients must contain every supported ingredient exactly once.';
        }
        foreach ($expected as $ingredient) {
            if (!array_key_exists($ingredient, $ingredients)) {
                return 'Missing ingredient: ' . $ingredient . '.';
            }
            if (!is_int($ingredients[$ingredient]) || $ingredients[$ingredient] < 0) {
                return 'Ingredient quantities must be non-negative integers.';
            }
        }
        foreach ($ingredients as $ingredient => $quantity) {
            if (!in_array($ingredient, $expected, true)) {
                return 'Unknown ingredient: ' . $ingredient . '.';
            }
        }
        return null;
    }

    private function isJsonContentType($contentType)
    {
        return preg_match('/^application\/json(?:\s*;\s*charset=[^;]+)?$/i', trim($contentType)) === 1;
    }

    private function error($status, $message)
    {
        return new JsonResponse($status, array('success' => false, 'error' => array('message' => $message)));
    }
}
