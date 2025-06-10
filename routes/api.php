<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aqui você pode registrar rotas API para sua aplicação. Essas
| rotas são carregadas pelo RouteServiceProvider e todas elas serão
| atribuídas ao grupo de middleware "api". Crie algo incrível!
|
*/

// Rota padrão do Laravel para pegar o usuário autenticado via Sanctum.
// Esta rota geralmente já vem com o Laravel Sanctum e deve ser mantida.
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Todas as rotas relacionadas a upload de imagens para galerias foram removidas
// deste arquivo e estão agora no routes/web.php, sob o middleware 'fotografo'.
