<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

// Controllers Auth (Breeze)
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;

// Controllers Principais
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController; // Corrigido
use App\Http\Controllers\PublicGalleryController;

// Controllers da Área de ADMINISTRAÇÃO
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\GroupController; // Corrigido
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;

// Controllers da Área de FOTÓGRAFO
use App\Http\Controllers\FotografoDashboardController; // Corrigido
use App\Http\Controllers\Fotografo\GalleryController as FotografoGalleryController;


// --- ROTAS PÚBLICAS ---
Route::get('/', function () {
    return Inertia::render('Home');
});

// Rota para a seção "Sobre a Escola"
Route::get('/sobre-a-escola', function () {
    return Inertia::render('Sobre/SobreEscola');
})->name('sobre-a-escola');

// Rota PÚBLICA: Coral Ranieri
Route::get('/coralranieri', function () {
    return Inertia::render('Coral/CoralRanieri');
})->name('coral-ranieri');

// Rota PÚBLICA: Grêmio Estudantil
Route::get('/gremio', function () {
    return Inertia::render('Gremio/Gremio');
})->name('gremio');

// Rota PÚBLICA: Brincando Dialogando
Route::get('/brincandodialogando', function () {
    return Inertia::render('BrincandoDialogando/BrincandoDialogando');
})->name('brincando-dialogando');

// NOVA ROTA PÚBLICA: Simoninha na Cozinha
Route::get('/simoninhanacozinha', function () {
    // O caminho do componente Vue é 'Simoninhanacozinha/Simoninhanacozinha', com a capitalização exata
    return Inertia::render('Simoninhanacozinha/Simoninhanacozinha');
})->name('simoninhanacozinha');


// --- ROTAS DA INTERFACE PÚBLICA DE GALERIAS (NOVO FLUXO) ---
Route::prefix('galerias')->name('public.galleries.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'index'])->name('index');
    Route::get('/grupo/{group}', [PublicGalleryController::class, 'listByGroup'])->name('list-by-group');
    Route::get('/{gallery}', [PublicGalleryController::class, 'show'])->name('show');
});


// --- DASHBOARD GERAL (AUTENTICADO) ---
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// --- ROTAS DE AUTENTICAÇÃO (Breeze) ---
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// --- ROTAS AUTENTICADAS (Usuário SEMPRE precisa estar logado) ---
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Rotas de Perfil (existentes)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- ROTAS DA ÁREA DE ADMINISTRAÇÃO ---
    Route::middleware('permission:gate,admin-only')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard'); // Dashboard do Admin
        Route::resource('groups', GroupController::class);
        Route::resource('roles', RoleController::class);
        Route::get('users/mass-assign-roles', [UserController::class, 'massAssignRolesIndex'])->name('users.mass-assign-roles.index');
        Route::post('users/mass-assign-roles', [UserController::class, 'massAssignRolesStore'])->name('users.mass-assign-roles.store');
        Route::get('users/mass-assign-groups', [UserController::class, 'massAssignGroupsIndex'])->name('users.mass-assign-groups.index');
        Route::post('users/mass-assign-groups', [UserController::class, 'massAssignGroupsStore'])->name('users.mass-assign-groups.store');
        Route::resource('users', UserController::class);
    });

    // --- ROTAS DA ÁREA DE FOTÓGRAFO ---
    // ESTE GRUPO DE ROTAS JÁ PROTEGE TUDO COM 'fotografo-only'
    Route::middleware('permission:gate,fotografo-only')->prefix('fotografo')->name('fotografo.')->group(function () {
        // Dashboard do Fotógrafo
        Route::get('/', FotografoDashboardController::class)->name('dashboard');

        // AGORA A ROTA DE INDEX TAMBÉM ESTÁ DENTRO DO GRUPO PROTEGIDO
        Route::get('/galleries', [FotografoGalleryController::class, 'index'])->name('galleries.index');

        // Rota para criar galeria (já deve existir)
        Route::get('/galleries/create', [FotografoGalleryController::class, 'create'])
            ->middleware('permission:gate,create-gallery')
            ->name('galleries.create');

        Route::post('/galleries', [FotografoGalleryController::class, 'store'])
            ->middleware('permission:gate,create-gallery')
            ->name('galleries.store');

        // Página para upload de imagens (frontend Inertia)
        Route::get('/galleries/{gallery}/upload-images', [FotografoGalleryController::class, 'uploadImages'])
            ->middleware('permission:gate,manage-gallery,gallery')
            ->name('galleries.upload-images');

        // Endpoint de upload de imagens (backend)
        Route::post('/galleries/{gallery}/images', [FotografoGalleryController::class, 'storeImage'])
            ->middleware('permission:gate,manage-gallery,gallery')
            ->name('galleries.images.store');

        // Rota para editar galeria (já deve existir)
        Route::get('/galleries/{gallery}/edit', [FotografoGalleryController::class, 'edit'])
            ->middleware('permission:gate,manage-gallery,gallery')
            ->name('galleries.edit');

        Route::patch('/galleries/{gallery}', [FotografoGalleryController::class, 'update'])
            ->middleware('permission:gate,manage-gallery,gallery')
            ->name('galleries.update');

        Route::delete('/galleries/{gallery}', [FotografoGalleryController::class, 'destroy'])
            ->middleware('permission:gate,manage-gallery,gallery')
            ->name('galleries.destroy');

        // Rota para buscar marcas d'água
        Route::get('/galleries/watermarks', [FotografoGalleryController::class, 'getAvailableWatermarks'])
            ->name('galleries.watermarks.index');

        // Rota para exibir a lista de imagens de uma galeria específica (PreviewImages.vue)
        Route::get('/galleries/{gallery}', [FotografoGalleryController::class, 'show'])
            ->middleware('permission:gate,view,gallery')
            ->name('galleries.show');

        // Rota para DELETAR uma imagem específica de uma galeria
        Route::delete('/galleries/{gallery}/images/{image}', [FotografoGalleryController::class, 'destroyImage'])
            ->middleware('permission:gate,manage-gallery,gallery') // Permissão para gerenciar a galeria
            ->name('galleries.images.destroy');
    });
});

require __DIR__.'/auth.php';
