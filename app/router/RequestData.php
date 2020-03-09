<?php

namespace Testify\Router;

class RequestData {

    private $dataArray;

    public function __construct(array $data) {
        $this->dataArray = $data;
    }

    public function get(string $key) {
        if (!isset($this->dataArray[$key]))
            throw new \Exception("The data request does not contains $key !");
        return $this->dataArray[$key];
    }

    public function getWithDefault(string $key, $default) {
        if (!isset($this->dataArray[$key]))
            return $default;
        return $this->dataArray[$key];
    }

    public function isEmpty(string $key) {
        return empty($this->dataArray[$key]);
    }

    public function isExist(string $key) {
        return isset($this->dataArray[$key]);
    }

    public function existAndNotEmpty(string $key) {
        return (isset($this->dataArray[$key]) && !empty($this->dataArray[$key]));
    }

    public function existAndEmpty(string $key) {
        return (isset($this->dataArray[$key]) && empty($this->dataArray[$key]));
    }
}
