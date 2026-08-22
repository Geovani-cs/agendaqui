<?php

use Illuminate\Support\Facades\Route;

// Serve o frontend (SPA) para a raiz e para os caminhos de tenant (ex.: /jv, /gcs).
// A API fica em /api/* (nao conflita). Exclui rotas reservadas (up, api, storage).
$serveApp = fn () => response()->file(resource_path('frontend/index.html'));

Route::get('/', $serveApp);
Route::get('/{tenant}', $serveApp)->where('tenant', '^(?!up$|api$|storage$).+$');
