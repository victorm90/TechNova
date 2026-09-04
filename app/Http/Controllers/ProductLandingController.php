<?php

namespace App\Http\Controllers;

class ProductLandingController extends Controller
{
    public function index(): mixed
    {
        return view('admin.product-landing');
    }
}