<?php

namespace App\Controllers;

class Terms extends BaseController
{
    public function index()
    {
                $data['page_title'] = 'Terms and Conditions';
        $data['page_class'] = 'bg-gray-900 text-slate-200';
        return view('front-end/terms', $data);
    }
}
