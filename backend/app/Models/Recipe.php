<?php

class Recipe
{
    private $name;
    private $feeds;
    private $ingredients;

    public function __construct($name, $feeds, array $ingredients)
    {
        $this->name = $name;
        $this->feeds = $feeds;
        $this->ingredients = $ingredients;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFeeds()
    {
        return $this->feeds;
    }

    public function getIngredients()
    {
        return $this->ingredients;
    }
}
