<?php

namespace view;

class Json {
    
    private $json;
    
    /**
     * JSON encode option(s)
     */
    private $options = JSON_FORCE_OBJECT|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES;
    
    /**
     * Encode data as json
     */
    public function __construct($data) {
        $this->json = json_encode($data, $this->options);
    }
    
    /**
     * Send content as response 
     */
    public function out(int $response_code = 200) {
        http_response_code($response_code);
        return $this->json;
    }
}
?>