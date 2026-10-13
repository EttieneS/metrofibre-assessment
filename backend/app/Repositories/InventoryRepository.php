<?php

class InventoryRepository
{
    public function getIngredientNames()
    {
        return array('cucumber', 'olives', 'lettuce', 'meat', 'tomato', 'cheese', 'dough');
    }

    public function getDefault()
    {
        return new Inventory(array('cucumber' => 2, 'olives' => 2, 'lettuce' => 3, 'meat' => 6, 'tomato' => 6, 'cheese' => 8, 'dough' => 10));
    }
}
