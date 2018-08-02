<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    /**
     * app版本信息
     * @return array
     */
    public function chk_version()
    {
        return ["app_version" => config('api.version') ];
    }
}