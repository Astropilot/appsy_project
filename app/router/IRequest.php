<?php

namespace Testify\Router;

use Testify\Router\RequestData;

interface IRequest {

    public function getData(): RequestData;
}
