<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Admin;


class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        
        $admin = Admin::where('NOMUTILISATEUR', $credentials['username'])->first();
        
        // Check if admin exists and password matches
        if ($admin && Hash::check($credentials['password'], $admin->MOTDEPASSE)) {
            $request->session()->put('admin_logged_in', true);
            $request->session()->put('admin_id', $admin->IDADMIN);
            return redirect()->route('tableauDeBord');
        }
        
        return back()->with('error', 'Les identifiants sont incorrects.');
    }
    
    public function logout(Request $request)
    {
        $request->session()->forget(['admin_logged_in', 'admin_id']);
        cookie()->queue(cookie()->forget('admin_remember'));
        return redirect()->route('admin.login');
    }

    /**
     * Affiche le formulaire d'oubli de mot de passe
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }
    
    /**
     * Envoie un lien de réinitialisation par email
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Trouve l'admin avec cet email
        $admin = Admin::where('EMAIL', $request->email)->first();
        
        if (!$admin) {
            return back()->with('error', 'Aucun compte trouvé avec cette adresse email.');
        }

        // Crée un token de réinitialisation
        $token = Str::random(64);
        $expiresAt = Carbon::now()->addMinutes(60);
        
        // Stocke le token directement dans la table ADMIN
        $admin->TOKEN_RESET_PASSWORD = $token;
        $admin->TOKEN_EXPIRES_AT = $expiresAt;
        $admin->save();

        // Envoie l'email avec le token
        Mail::send('emails.reset-password', ['token' => $token, 'email' => $request->email], function($message) use($request){
            $message->to($request->email);
            $message->subject('Réinitialisation du mot de passe');
        });

        return back()->with('status', 'Un lien de réinitialisation a été envoyé à votre adresse email.');
    }
    
    /**
     * Affiche le formulaire de réinitialisation du mot de passe
     */
    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }
    
    /**
     * Réinitialise le mot de passe de l'utilisateur
     */
    public function resetPassword(Request $request)
    {
        $messages = [
            'password.required' => 'Le mot de passe est requis.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
            'email.required' => 'L\'adresse e-mail est requise.',
            'email.email' => 'Veuillez entrer une adresse e-mail valide.',
            'token.required' => 'Le jeton de réinitialisation est requis.'
        ];
    
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
        ], $messages);

         // Trouve l'admin avec l'email et le token valide
         $admin = Admin::where('EMAIL', $request->email)
         ->where('TOKEN_RESET_PASSWORD', $request->token)
         ->first();

        // Vérifie si l'admin existe et si le token n'est pas expiré
        if (!$admin || Carbon::parse($admin->TOKEN_EXPIRES_AT)->isPast()) {
            return back()->withErrors(['email' => 'Le lien de réinitialisation est invalide ou expiré.']);
        }
        
        // Met à jour le mot de passe et supprime le token
        $admin->MOTDEPASSE = Hash::make($request->password); 
        $admin->TOKEN_RESET_PASSWORD = null;
        $admin->TOKEN_EXPIRES_AT = null;
        $admin->save();

        

        return redirect()->route('admin.login')->with('status', 'Votre mot de passe a été réinitialisé avec succès!');
    }
}