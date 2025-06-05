<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;

class ParametreController extends Controller
{
    public function index()
    {
        // Récupérer l'admin connecté pour vérifier son statut
        $adminId = session('admin_id');
        $admin = Admin::findOrFail($adminId);
        
        // Vérifier si c'est l'admin principal (ID = 1 et nom = "admin")
        $isMainAdmin = ($admin->IDADMIN == 1 && $admin->NOMUTILISATEUR === 'admin');
        
        return view('parametre', ['admin' => $admin, 'isMainAdmin' => $isMainAdmin]);
    }
}
