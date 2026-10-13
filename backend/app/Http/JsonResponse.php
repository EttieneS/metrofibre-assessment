<?php

class JsonResponse {
    private $status;
    private $headers;
    private $body;

    public function __construct($status, array $body, array $headers = array()) {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getBody() {
        return $this->body;
    }
}
