<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class RouteController extends BaseController
{
    public function index()
    {
        $data = [
            'page_title' => 'Route Planning',
        ];
        return view('route/index', $data);
    }
}
