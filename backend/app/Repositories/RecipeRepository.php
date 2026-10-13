<?php

class RecipeRepository
{
    public function getAll()
    {
        return array(
            new Recipe('burger', 1, array('lettuce' => 1, 'meat' => 1, 'tomato' => 1, 'cheese' => 1, 'dough' => 1)),
            new Recipe('pie', 1, array('meat' => 2, 'dough' => 2)),
            new Recipe('sandwich', 1, array('cucumber' => 1, 'dough' => 1)),
            new Recipe('pasta', 2, array('dough' => 2, 'tomato' => 1, 'cheese' => 2, 'meat' => 1)),
            new Recipe('salad', 3, array('lettuce' => 2, 'tomato' => 2, 'cucumber' => 1, 'cheese' => 2, 'olives' => 1)),
            new Recipe('pizza', 4, array('dough' => 3, 'tomato' => 2, 'cheese' => 3, 'olives' => 1))
        );
    }
}
