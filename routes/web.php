<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ImportarImagesController;
use App\Http\Controllers\dde_incorporacionesController;
use App\Models\Gerencia;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth:sanctum', 'verified'])->group(function () {
  Route::get('/imagen-persona/{personaId}', [ImportarImagesController::class, 'getImagenPersona'])->name('imagen-persona');
  Route::get('/download-form/{fileName}', [dde_incorporacionesController::class, 'downloadEvalForm'])->name('download.form')->middleware('auth');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/migraciones', function () {
  return Inertia::render('Migraciones/Index', [
    'dde_gerencias' => Gerencia::select('id', 'nombre')->get()->map(function ($gerencia) {
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

Route::middleware(['auth:sanctum', 'verified'])->get('/dde_incorporaciones', function () {
  return Inertia::render('dde_incorporaciones/Index');
})->name('dde_incorporaciones');

// Route::middleware(['auth:sanctum', 'verified'])->get('/dde_incorporaciones1', function () {
//   return Inertia::render('dde_incorporaciones1/Index');
// })->name('dde_incorporaciones1');
