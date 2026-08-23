<?php

use Illuminate\Support\Facades\Route;

// Frontend do cliente (SPA) na raiz e nos caminhos de tenant (ex.: /jv, /gcs).
// /painel serve o painel do dono. A API fica em /api/*.
$serveApp = fn () => response()->file(resource_path('frontend/index.html'));

Route::get('/', $serveApp);
Route::get('/painel', fn () => response()->file(resource_path('frontend/painel.html')));
Route::get('/{tenant}', $serveApp)->where('tenant', '^(?!up$|api$|storage$|painel$).+$');
