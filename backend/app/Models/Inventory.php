<?php

class Inventory {
    private $quantities;

    public function __construct(array $quantities) {
        $this->quantities = $quantities;
    }

    public function getQuantity($ingredient) {
        return $this->quantities[$ingredient];
    }

    public function getQuantities() {
        return $this->quantities;
    }
}
