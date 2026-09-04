<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $titulo = 'Sistema de Almacen | TechNova - Dasboard';

        return view('admin.dasboard', compact('titulo'));
    }
}
