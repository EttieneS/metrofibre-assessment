<?php

class Router {
    private $optimisationController;

    public function __construct(OptimisationController $optimisationController) {
        $this->optimisationController = $optimisationController;
    }

    public function dispatch($method, $path, $body, $contentType) {
        if ($method === 'POST' && in_array($path, array('/', '/index.php', '/api/optimise'), true)) {
            return $this->optimisationController->optimise($body, $contentType);
        }
        if ($method !== 'POST' && in_array($path, array('/', '/index.php', '/api/optimise'), true)) {
            return new JsonResponse(405, array('success' => false, 'error' => array('message' => 'Method not allowed.')), array('Allow' => 'POST'));
        }
        return new JsonResponse(404, array('success' => false, 'error' => array('message' => 'Route not found.')));
    }
}
