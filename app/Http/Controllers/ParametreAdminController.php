<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ParametreAdminController extends Controller
{
    /**
     * Vérifier si l'utilisateur connecté est l'admin principal
     */
    private function checkMainAdminAccess()
    {
        $adminId = session('admin_id');
        $admin = Admin::findOrFail($adminId);
        
        // Vérifier si c'est l'admin principal (ID = 1 et nom = "admin")
        if ($admin->IDADMIN != 1 || $admin->NOMUTILISATEUR !== 'admin') {
            abort(403, 'Accès refusé. Seul l\'administrateur principal peut accéder à cette page.');
        }
        
        return $admin;
    }
    
    public function index()
    {
        // Vérifier l'accès admin principal
        $admin = $this->checkMainAdminAccess();
        
        // Récupérer tous les admins
        $users = Admin::all();
        
        return view('parametres.admin', ['admin' => $admin, 'users' => $users]);
    }
    
    public function updateEmail(Request $request)
    {
        // Vérifier l'accès admin principal
        $admin = $this->checkMainAdminAccess();
        
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
        
        // Vérifier si le mot de passe est correct
        if (!Hash::check($request->input('password'), $admin->MOTDEPASSE)) {
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

    /**
     * Créer un nouveau compte utilisateur
     */
    public function createUser(Request $request)
    {
        // Vérifier l'accès admin principal
        $this->checkMainAdminAccess();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:ADMIN,EMAIL|max:255',
            'password' => 'required|min:6|confirmed',
        ], [
            'name.required' => 'Le nom est requis.',
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Créer le nouvel admin
        $admin = new Admin();
        $admin->NOMUTILISATEUR = $request->input('name');
        $admin->EMAIL = $request->input('email');
        $admin->MOTDEPASSE = Hash::make($request->input('password'));
        $admin->save();

        return redirect()->route('parametres.admin')
            ->with('success', 'Compte administrateur créé avec succès.');
    }

    /**
     * Modifier un compte utilisateur
     */
    public function updateUser(Request $request, $id)
    {
        // Vérifier l'accès admin principal
        $this->checkMainAdminAccess();
        
        $admin = Admin::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:ADMIN,EMAIL,' . $id . ',IDADMIN',
            'password' => 'nullable|min:6|confirmed',
        ], [
            'name.required' => 'Le nom est requis.',
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Mettre à jour l'admin
        $admin->NOMUTILISATEUR = $request->input('name');
        $admin->EMAIL = $request->input('email');
        
        // Mettre à jour le mot de passe seulement si fourni
        if ($request->filled('password')) {
            $admin->MOTDEPASSE = Hash::make($request->input('password'));
        }
        
        $admin->save();

        return redirect()->route('parametres.admin')
            ->with('success', 'Compte administrateur modifié avec succès.');
    }

    /**
     * Supprimer un compte utilisateur
     */
    public function deleteUser($id)
    {
        // Vérifier l'accès admin principal
        $this->checkMainAdminAccess();
        
        $admin = Admin::findOrFail($id);
        
        // Empêcher la suppression de l'admin principal (ID = 1)
        if ($admin->IDADMIN == 1) {
            return redirect()->route('parametres.admin')
                ->with('error', 'Impossible de supprimer le compte administrateur principal.');
        }
        
        // Empêcher la suppression si c'est le seul admin
        $adminCount = Admin::count();
        if ($adminCount <= 1) {
            return redirect()->route('parametres.admin')
                ->with('error', 'Impossible de supprimer le dernier compte administrateur.');
        }
        
        $admin->delete();

        return redirect()->route('parametres.admin')
            ->with('success', 'Compte administrateur supprimé avec succès.');
    }
}
