<?php

use Illuminate\Support\Facades\Route;

// Frontend do cliente (SPA) na raiz e nos caminhos de tenant (ex.: /jv, /gcs).
// /painel serve o painel do dono. A API fica em /api/*.
// Sem cache: o HTML muda a cada deploy, entao forcamos revalidacao (evita versao velha em cache de borda).
$noCache = ['Cache-Control' => 'no-cache, no-store, must-revalidate', 'Pragma' => 'no-cache'];
$serveApp = fn () => response()->file(resource_path('frontend/index.html'), $noCache);

Route::get('/', $serveApp);
Route::get('/painel', fn () => response()->file(resource_path('frontend/painel.html'), $noCache));
Route::get('/{tenant}', $serveApp)->where('tenant', '^(?!up$|api$|storage$|painel$).+$');
