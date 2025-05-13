<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BatimentController;
use App\Http\Controllers\ChambreController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ResidentArchiveController;
use App\Http\Controllers\FichierController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\PlanningResidentController;




// Add more models as needed

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    return view('tableauDeBord');
})->name('tableauDeBord');
Route::get('/ChambreLibre', function () {
    return redirect()->route('filterDepartingResidents', [
        'month' => now()->month,
        'year' => now()->year
    ]);
})->name('chambreLibre');
Route::get('/ChambreLibre', [ResidentController::class, 'showDepartingResidents'])->name('chambreLibre');


Route::get("/Batiment",[BatimentController::class, "index" ])->name('batiment');
Route::get("/Batiment/{IdBatiment}",[ChambreController::class, "index" ])->name('chambre');
Route::get("/Salle",[SalleController::class, "index" ])->name('salle');
Route::get("/DetailSalle/{IdSalle}",[SalleController::class, "getSalle" ])->name('detailSalle');
Route::get('/Salle/{IdSalle}/{weekOffset?}', [SalleController::class, 'getSalle'])->name('getSalle');
Route::get('/les-salles', [SalleController::class, 'lesSalles'])->name('lesSalles');
Route::get("/archive",[ResidentArchiveController::class, "index" ])->name('archive');
Route::get('/resident-archive/{idResidentA}',[ResidentArchiveController::class,"getResidentArchive" ])->name('resident-archive');
Route::get('/planning-resident', [PlanningResidentController::class, 'index'])->name('planning.resident');

Route::get('/planning/resident/export', [App\Http\Controllers\PlanningResidentController::class, 'exportExcel'])->name('planning.resident.export');

Route::get('/filter-departing-residents', [ResidentController::class, 'filterDepartingResidents'])->name('filterDepartingResidents');
Route::get("/RechercherResident",[ResidentController::class, "search" ])->name('residents.search');
Route::get("/Chambre/{IdBatiment}/{NumChambre}",[ResidentController::class, "index" ])->name('resident');
Route::get("/Modifier-Resident/{idResident}",[ResidentController::class, "getModif" ])->name('modifierResident');
Route::get("/NouveauResident/{IdBatiment}/{NumChambre}",[ResidentController::class, "nouveauResident" ])->name('nouveauResident');
Route::get("/SupprimerResident/{idResident}",[ResidentController::class, "supprimerResident" ])->name('supprimerResident');
Route::get("/LesResidents",[ResidentController::class, "getAllResident" ])->name('allResident');

Route::put("/Modifier-Resident/{idResident}",[ResidentController::class, "modifierResident" ])->name('resident.update');
Route::post("/NouveauResident/{IdBatiment}/{NumChambre}",[ResidentController::class, "store" ])->name('resident.store');
Route::post("/UploadFichier/{idResident}", [FichierController::class, "uploadFichier"])->name('uploadFichier');
Route::delete("/SupprimerFichier/{idFichier}", [FichierController::class, "supprimerFichier"])->name('supprimerFichier');
Route::post('/NouvelEvenement', [EvenementController::class, 'store'])->name('nouvelEvenement');
Route::post('/NouvelleOccupation', [SalleController::class, 'nouvelleOccupation'])->name('nouvelleOccupation');
Route::post('/gererOccupation', [SalleController::class, 'gererOccupation'])->name('gererOccupation');
Route::post('/resident/planifier-depart', [App\Http\Controllers\ResidentController::class, 'planifierDepart'])->name('planifierDepart');