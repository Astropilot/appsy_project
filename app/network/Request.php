<?php

namespace Testify\Network;

class Request {

    private $url;
    private $curl_handler;

    private $curl_post_form = [
        CURLOPT_POST => true
    ];
    private $curl_post_json = [
        CURLOPT_POST => false,
        CURLOPT_CUSTOMREQUEST => 'POST'
    ];

    public function __construct(string $url) {
        $this->url = $url;
        $this->curl_handler = curl_init();
        $opts = [
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
        ];

        curl_setopt_array($this->curl_handler, $opts);
    }

    public function get(): string {
        $opts = [
            CURLOPT_POST => false,
            CURLOPT_CUSTOMREQUEST => 'GET'
        ];

        curl_setopt_array($this->curl_handler, $opts);

        return curl_exec($this->curl_handler);
    }

    public function post($params, string $type = 'form'): string {
        $opts = [];

        switch ($type) {
            case 'json':
                $opts = [
                    CURLOPT_POSTFIELDS => $params,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($params)
                    ]
                ];
                curl_setopt_array($this->curl_handler, $this->curl_post_json);
                break;
            case 'form':
            default:
                $params_string = http_build_query($params);

                $opts = [
                    CURLOPT_POSTFIELDS => $params_string,
                ];
                curl_setopt_array($this->curl_handler, $this->curl_post_form);
        }

        curl_setopt_array($this->curl_handler, $opts);

        return curl_exec($this->curl_handler);
    }

    function __destruct() {
        curl_close($this->curl_handler);
    }
}
