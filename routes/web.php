<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BatimentController;
use App\Http\Controllers\ChambreController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ResidentImportController;
use App\Http\Controllers\ResidentArchiveController;
use App\Http\Controllers\FichierController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\PlanningResidentController;
use App\Http\Controllers\ParametreBatimentController;
use App\Http\Controllers\ParametreChambreController;
use App\Http\Controllers\ParametreOccupationController;
use App\Http\Controllers\ParametreSalleController;
use App\Http\Controllers\ParametresImportController;
use App\Http\Controllers\ParametreController;

// Public routes - only login pages
Route::get('/admin/login', function () {
    return view('connexion');
})->name('admin.login');

Route::get('/admin/forgot-password', [App\Http\Controllers\AdminAuthController::class, 'showForgotPasswordForm'])
    ->name('admin.password.request');
    
Route::post('/admin/forgot-password', [App\Http\Controllers\AdminAuthController::class, 'sendResetLinkEmail'])
    ->name('admin.password.email');
    
Route::get('/admin/reset-password/{token}', [App\Http\Controllers\AdminAuthController::class, 'showResetPasswordForm'])
    ->name('admin.password.reset');
    
Route::post('/admin/reset-password', [App\Http\Controllers\AdminAuthController::class, 'resetPassword'])
    ->name('admin.password.update');

Route::post('/admin/login', [App\Http\Controllers\AdminAuthController::class, 'login'])->name('admin.login.submit');    // Protected routes - require authentication
Route::middleware(['admin'])->group(function () {
    // Logout route
    Route::post('/admin/logout', [App\Http\Controllers\AdminAuthController::class, 'logout'])->name('admin.logout');
    
    // Dashboard route
    Route::get('/', function () {
        return view('tableauDeBord');
    })->name('tableauDeBord');
    
    // All other existing routes
    Route::get('/ChambreLibre', function () {
        return redirect()->route('filterDepartingResidents', [
            'month' => now()->month,
            'year' => now()->year
        ]);
    })->name('chambreLibre');
    Route::get('/parametres', [App\Http\Controllers\ParametreController::class, 'index'])->name('parametres');
    
    // Routes pour l'importation Excel des résidents
    Route::get('/parametres/import-excel', [ParametresImportController::class, 'index'])->name('parametres.import-excel');
    Route::post('/parametres/import-excel', [ParametresImportController::class, 'processImport'])->name('parametres.import-excel.process');
    Route::get('/parametres/import-excel/template', [ParametresImportController::class, 'downloadTemplate'])->name('parametres.import-excel.template');
    
    // Routes pour la gestion des groupes d'événements
    Route::get('/parametres/groupes', [EvenementController::class, 'index'])->name('parametres.groupes');
    Route::put('/parametres/groupes/{id}', [EvenementController::class, 'update'])->name('parametres.groupes.update');
    Route::delete('/parametres/groupes/{id}', [EvenementController::class, 'destroy'])->name('parametres.groupes.destroy');
    
    // Routes pour la gestion des groupes de résidents
    Route::get('/groupes', [App\Http\Controllers\GroupController::class, 'index'])->name('groups.index');
    Route::get('/groupes/creer', [App\Http\Controllers\GroupController::class, 'create'])->name('groups.create');
    Route::post('/groupes', [App\Http\Controllers\GroupController::class, 'store'])->name('groups.store');
    Route::get('/groupes/{id}', [App\Http\Controllers\GroupController::class, 'show'])->name('groups.show');
    Route::put('/groupes/{id}', [App\Http\Controllers\GroupController::class, 'update'])->name('groups.update');
    Route::delete('/groupes/{id}', [App\Http\Controllers\GroupController::class, 'destroy'])->name('groups.destroy');
    Route::delete('/groupes/{id}/chambres/{roomId}', [App\Http\Controllers\GroupController::class, 'removeFromRoom'])->name('groups.rooms.remove');
    
    // Routes pour la gestion des bâtiments
    Route::get('/parametres/batiments', [ParametreBatimentController::class, 'index'])->name('parametres.batiments');
    Route::post('/parametres/batiments', [ParametreBatimentController::class, 'store'])->name('parametres.batiments.store');
    Route::delete('/parametres/batiments/{id}', [ParametreBatimentController::class, 'destroy'])->name('parametres.batiments.destroy');
    
    // Routes pour la gestion des chambres
    Route::get('/parametres/batiments/{batimentId}/chambres', [ParametreChambreController::class, 'index'])->name('parametres.chambres');
    Route::post('/parametres/batiments/{batimentId}/chambres', [ParametreChambreController::class, 'store'])->name('parametres.chambres.store');
    Route::delete('/parametres/chambres/{id}', [ParametreChambreController::class, 'destroy'])->name('parametres.chambres.destroy');
    
    // Routes pour la gestion des salles
    Route::get('/parametres/salles', [ParametreSalleController::class, 'index'])->name('parametres.salles');
    Route::post('/parametres/salles', [ParametreSalleController::class, 'store'])->name('parametres.salles.store');
    Route::put('/parametres/salles/{id}', [ParametreSalleController::class, 'update'])->name('parametres.salles.update');
    Route::delete('/parametres/salles/{id}', [ParametreSalleController::class, 'destroy'])->name('parametres.salles.destroy');
    
    // Routes pour la gestion des paramètres admin
    Route::get('/parametres/admin', [App\Http\Controllers\ParametreAdminController::class, 'index'])->name('parametres.admin');
    Route::put('/parametres/admin/update-email', [App\Http\Controllers\ParametreAdminController::class, 'updateEmail'])->name('parametres.admin.updateEmail');
    
    // Routes pour la gestion des utilisateurs (par l'admin)
    Route::post('/parametres/admin/users', [App\Http\Controllers\ParametreAdminController::class, 'createUser'])->name('parametres.admin.users.create');
    Route::put('/parametres/admin/users/{id}', [App\Http\Controllers\ParametreAdminController::class, 'updateUser'])->name('parametres.admin.users.update');
    Route::delete('/parametres/admin/users/{id}', [App\Http\Controllers\ParametreAdminController::class, 'deleteUser'])->name('parametres.admin.users.delete');
    
    // Routes pour l'optimisation des occupations
    Route::get('/parametres/optimisation-occupation', [ParametreOccupationController::class, 'index'])->name('parametres.optimisation-occupation');
    Route::post('/parametres/optimisation-occupation', [ParametreOccupationController::class, 'optimiserOccupations'])->name('parametres.optimisation-occupation.optimiser');

    Route::get("/ViewFile/{idFichier}", [FichierController::class, "viewFile"])->name('viewFile');

    Route::get('/ChambreLibre', [ChambreController::class, 'showDepartingResidents'])->name('chambreLibre');
    Route::get("/Batiment", [BatimentController::class, "index"])->name('batiment');
    Route::get("/Batiment/{IdBatiment}", [ChambreController::class, "index"])->name('chambre');
    Route::get("/Salle", [SalleController::class, "index"])->name('salle');
    Route::get("/DetailSalle/{IdSalle}", [SalleController::class, "getSalle"])->name('detailSalle');
    Route::get('/Salle/{IdSalle}/{weekOffset?}', [SalleController::class, 'getSalle'])->name('getSalle');
    Route::get('/les-salles', [SalleController::class, 'lesSalles'])->name('lesSalles');
    Route::get('/occupations/export', [SalleController::class, 'exportOccupationsExcel'])->name('occupations.export');
    Route::get("/archive", [ResidentArchiveController::class, "index"])->name('archive');
    Route::get('/resident-archive/{idResidentA}', [ResidentArchiveController::class, "getResidentArchive"])->name('resident-archive');
    Route::get('/archives/export', [ResidentArchiveController::class, 'exportExcel'])->name('archives.export');
    Route::get("/SupprimerResidentArchive/{idResidentArchive}", [ResidentArchiveController::class, "deleteResidentArchive"])->name('supprimerResidentArchive');
    Route::get('/planning-resident', [PlanningResidentController::class, 'index'])->name('planning.resident');
    Route::get('/planning/resident/export', [App\Http\Controllers\PlanningResidentController::class, 'exportExcel'])->name('planning.resident.export');
    Route::get('/residents/export', [ResidentController::class, 'exportExcel'])->name('residents.export');
    Route::get('/filter-departing-residents', [ChambreController::class, 'filterDepartingResidents'])->name('filterDepartingResidents');
    Route::get("/RechercherResident", [ResidentController::class, "search"])->name('residents.search');
    Route::get("/Chambre/{IdBatiment}/{NumChambre}", [ResidentController::class, "index"])->name('resident');
    Route::get("/resident/{IdResident}", [ResidentController::class, "getResident"])->name('getResident');
    Route::get("/Modifier-Resident/{idResident}", [ResidentController::class, "getModif"])->name('modifierResident');
    Route::get("/NouveauResident/{IdBatiment}/{NumChambre}", [ResidentController::class, "nouveauResident"])->name('nouveauResident');
    Route::get("/SupprimerResident/{idResident}", [ResidentController::class, "supprimerResident"])->name('supprimerResident');
    Route::get("/LesResidents", [ResidentController::class, "getAllResident"])->name('allResident');
    Route::put("/Modifier-Resident/{idResident}", [ResidentController::class, "modifierResident"])->name('resident.update');
    Route::post("/NouveauResident/{IdBatiment}/{NumChambre}", [ResidentController::class, "store"])->name('resident.store');
    Route::post("/ImporterResidents/{IdBatiment}/{NumChambre}", [ResidentImportController::class, "importExcel"])->name('resident.import');
    Route::get("/TelechargerModeleImport", [ResidentImportController::class, "downloadTemplate"])->name('resident.template');
    Route::post("/UploadFichier/{idResident}", [FichierController::class, "uploadFichier"])->name('uploadFichier');
    Route::delete("/SupprimerFichier/{idFichier}", [FichierController::class, "supprimerFichier"])->name('supprimerFichier');
    Route::get("/TelechargerTousFichiers/{idResident}", [FichierController::class, "telechargerTousFichiers"])->name('telechargerTousFichiers');
    Route::post('/NouvelEvenement', [EvenementController::class, 'store'])->name('nouvelEvenement');
    Route::post('/NouvelleOccupation', [SalleController::class, 'nouvelleOccupation'])->name('nouvelleOccupation');
    Route::post('/gererOccupation', [SalleController::class, 'gererOccupation'])->name('gererOccupation');
    Route::post('/resident/planifier-depart', [App\Http\Controllers\ResidentController::class, 'planifierDepart'])->name('planifierDepart');
    Route::post('/update-future-resident-dates', [App\Http\Controllers\ResidentController::class, 'updateFutureResidentDates'])->name('updateFutureResidentDates');
    Route::get('/resident/{idResident}/solde-tout-compte-pdf', [App\Http\Controllers\ResidentController::class, 'generateSoldeToutComptePdf'])->name('resident.solde-tout-compte-pdf');
    Route::post('/resident/{idResident}/solde-tout-compte-pdf', [App\Http\Controllers\ResidentController::class, 'generateSoldeToutComptePdf'])->name('resident.solde-tout-compte-pdf.post');
});

// Redirect all undefined routes to the login page
Route::fallback(function () {
    return redirect()->route('admin.login');
});