<?php

class OptimisationService
{
    private $recipeRepository;
    private $recipes;
    private $ingredientNames;
    private $inventory;
    private $bestPeople;
    private $solutions;

    public function __construct(RecipeRepository $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
        $this->recipes = $recipeRepository->getAll();
        $this->ingredientNames = array('cucumber', 'olives', 'lettuce', 'meat', 'tomato', 'cheese', 'dough');
    }

    public function optimise(Inventory $inventory)
    {
        $this->inventory = $inventory;
        $this->bestPeople = -1;
        $this->solutions = array();
        $this->search(0, array(), array(), 0);

        return array('people' => $this->bestPeople, 'combinations' => array_values($this->solutions));
    }

    private function search($index, $counts, $used, $people)
    {
        if ($index === count($this->recipes)) {
            $this->recordSolution($counts, $used, $people);
            return;
        }

        $recipe = $this->recipes[$index];
        $maximum = $this->maximumRecipeCount($recipe, $used);
        for ($count = 0; $count <= $maximum; $count++) {
            $nextUsed = $used;
            foreach ($recipe->getIngredients() as $ingredient => $quantity) {
                $current = isset($nextUsed[$ingredient]) ? $nextUsed[$ingredient] : 0;
                $nextUsed[$ingredient] = $current + ($quantity * $count);
            }
            $nextCounts = $counts;
            $nextCounts[$recipe->getName()] = $count;
            $this->search($index + 1, $nextCounts, $nextUsed, $people + ($recipe->getFeeds() * $count));
        }
    }

    private function maximumRecipeCount(Recipe $recipe, $used)
    {
        $maximum = PHP_INT_MAX;
        foreach ($recipe->getIngredients() as $ingredient => $quantity) {
            $alreadyUsed = isset($used[$ingredient]) ? $used[$ingredient] : 0;
            $remaining = $this->inventory->getQuantity($ingredient) - $alreadyUsed;
            $maximum = min($maximum, (int) floor($remaining / $quantity));
        }
        return max(0, $maximum);
    }

    private function recordSolution($counts, $used, $people)
    {
        if ($people < $this->bestPeople) {
            return;
        }

        $normalisedCounts = array();
        foreach ($this->recipes as $recipe) {
            $name = $recipe->getName();
            $normalisedCounts[$name] = isset($counts[$name]) ? $counts[$name] : 0;
        }
        $normalisedUsed = array();
        $remaining = array();
        foreach ($this->ingredientNames as $ingredient) {
            $amount = isset($used[$ingredient]) ? $used[$ingredient] : 0;
            $normalisedUsed[$ingredient] = $amount;
            $remaining[$ingredient] = $this->inventory->getQuantity($ingredient) - $amount;
        }

        $solution = array('recipes' => $normalisedCounts, 'used' => $normalisedUsed, 'remaining' => $remaining);
        $key = json_encode($normalisedCounts);
        if ($people > $this->bestPeople) {
            $this->bestPeople = $people;
            $this->solutions = array($key => $solution);
            return;
        }
        $this->solutions[$key] = $solution;
    }
}
