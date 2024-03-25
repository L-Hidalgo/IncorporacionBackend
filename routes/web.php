<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ImportarImagesController;
use App\Http\Controllers\IncorporacionesController;
use App\Models\Gerencia;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth:sanctum', 'verified'])->group(function () {
  Route::get('/imagen-persona/{personaId}', [ImportarImagesController::class, 'getImagenPersona'])->name('imagen-persona');
  Route::get('/download-form/{fileName}', [IncorporacionesController::class, 'downloadEvalForm'])->name('download.form')->middleware('auth');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/migraciones', function () {
  return Inertia::render('Migraciones/Index', [
    'gerencias' => Gerencia::select('id', 'nombre')->get()->map(function ($gerencia) {
      return [
        'key' => 'g-' . $gerencia->id,
        'icon' => 'pi pi-building',
        'label' => $gerencia->nombre,
        'children' => $gerencia->departamento->map(function ($dep) {
          return [
            'key' => 'd-' . $dep->id,
            'icon' => 'pi pi-sitemap',
            'label' => $dep->nombre
          ];
        })
      ];
    })
  ]);
})->name('migraciones');

Route::middleware(['auth:sanctum', 'verified'])->get('/incorporaciones', function () {
  return Inertia::render('Incorporaciones/Index');
})->name('incorporaciones');

// Route::middleware(['auth:sanctum', 'verified'])->get('/incorporaciones1', function () {
//   return Inertia::render('Incorporaciones1/Index');
// })->name('incorporaciones1');
