<?php

use core\library\Request;

return [
    Request::class => function () {
        return Request::create();
    }
];