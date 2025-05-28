<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ParametreAdminController extends Controller
{
    public function index()
    {
        // Récupérer l'admin connecté
        $adminId = session('admin_id');
        $admin = Admin::findOrFail($adminId);
        
        return view('parametres.admin', ['admin' => $admin]);
    }
    
    public function updateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required',
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',
            'password.required' => 'Le mot de passe est requis pour confirmer le changement.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Récupérer l'admin connecté
        $adminId = session('admin_id');
        $admin = Admin::findOrFail($adminId);
        
        // Vérifier si le mot de passe est correct
        if (!Hash::check($request->input('password'), $admin->MODEPASSE)) {
            return redirect()->back()
                ->with('error', 'Le mot de passe est incorrect.')
                ->withInput();
        }
        
        // Mettre à jour l'email
        $admin->EMAIL = $request->input('email');
        $admin->save();
        
        return redirect()->route('parametres.admin')
            ->with('success', 'Adresse email mise à jour avec succès.');
    }
}
