<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Profile;
use App\Models\Group;
use App\Models\Gallery;
use App\Models\Image;

use App\Policies\UserPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\GroupPolicy;
use App\Policies\GalleryPolicy;
use App\Policies\ImagePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Profile::class => ProfilePolicy::class,
        Group::class => GroupPolicy::class,
        Gallery::class => GalleryPolicy::class,
        Image::class => ImagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gates úteis para o projeto:

        // SOMENTE ADMIN PODE EXECUTAR (CONVENÇÃO)
        Gate::define('admin-only', function (User $user) {
            return $user->hasRole('admin');
        });

        // SOMENTE FOTÓGRAFO PODE EXECUTAR (CONVENÇÃO)
        Gate::define('fotografo-only', function (User $user) {
            return $user->hasRole('fotografo');
        });

        // Usuário pode editar o próprio perfil ou admin pode editar qualquer perfil
        // ARGUMENTO NULLABLE: ?Profile $profile = null
        Gate::define('edit-profile', function (User $user, ?Profile $profile = null) {
            if ($profile) {
                // Se um perfil específico foi passado, verifica se é o dono ou admin
                return $user->id === $profile->user_id || $user->hasRole('admin');
            }
            // Se nenhum perfil foi passado (ex: para acessar a página de edição do próprio perfil sem um ID na rota)
            // Assumimos que o usuário pode editar seu próprio perfil.
            return true;
        });

        // Usuário pode acessar grupo se for membro ou admin
        // ARGUMENTO NULLABLE: ?Group $group = null
        Gate::define('access-group', function (User $user, ?Group $group = null) {
            if ($group) {
                // Se um grupo específico foi passado, verifica se é membro ou admin
                return $user->hasRole('admin') || ($user->groups->contains($group->id));
            }
            // Se nenhum grupo foi passado (ex: para acessar a lista de grupos)
            // A lógica aqui deve ser para uma permissão mais geral, por exemplo, apenas admin pode ver a lista
            return $user->hasRole('admin'); // Ex: Apenas admins podem ver a lista de grupos. Ajuste se necessário.
        });

        // Usuário pode criar galeria se for admin ou fotógrafo
        // Não recebe modelo, já está OK.
        Gate::define('create-gallery', fn(User $user) =>
        $user->hasRole(['admin', 'fotografo'])
        );

        // Usuário pode gerenciar galeria se for admin, fotógrafo ou dono
        // ARGUMENTO NULLABLE: ?Gallery $gallery = null
        Gate::define('manage-gallery', function (User $user, ?Gallery $gallery = null) {
            if ($gallery) {
                return $user->hasRole(['admin', 'fotografo']) || $user->id === $gallery->user_id;
            }
            // Se nenhum modelo for passado, você pode definir um comportamento padrão, ou negar se a permissão exige um modelo.
            return $user->hasRole(['admin', 'fotografo']); // Ex: Apenas admin/fotografo podem gerenciar *qualquer* galeria
        });

        // GATE ORIGINAL DO FOTÓGRAFO: Permanece inalterada, para uso em áreas restritas.
        // Usuário pode visualizar galeria se for admin, fotógrafo ou dono (para preview/gerenciamento)
        Gate::define('view', function (User $user, ?Gallery $gallery = null) {
            if ($gallery) {
                return $user->hasRole(['admin', 'fotografo']) || $user->id === $gallery->user_id;
            }
            // Se nenhum modelo for passado, ex: para ver a lista de galerias.
            return $user->hasRole(['admin', 'fotografo']); // Ex: Apenas admin/fotografo podem ver a lista de galerias
        });

        // --- GATE CORRIGIDA E EXPANDIDA PARA ACESSO PÚBLICO ÀS GALERIAS ---
        // Esta Gate lida com a visualização de galerias na rota /galerias/{gallery}
        // A Gate suporta User como nullable para lidar com guests.
        Gate::define('view-public-gallery', function (?User $user, Gallery $gallery) {
            // Garante que as relações 'groups' estejam carregadas para a galeria
            if (!$gallery->relationLoaded('groups')) {
                $gallery->load('groups');
            }

            // Busca o grupo 'público' para identificar galerias públicas
            $publicGroup = Group::where('name', 'público')->first();
            $publicGroupId = $publicGroup ? $publicGroup->id : null;

            // Condição 1: Galeria é pública (associada ao grupo 'público')
            if ($publicGroupId && $gallery->groups->contains($publicGroupId)) {
                return true; // Se é pública, qualquer um pode visualizar (mesmo guest)
            }

            // Se não é pública, o usuário DEVE estar autenticado para continuar
            if (!$user) {
                return false; // Guests não podem ver galerias não-públicas
            }

            // Garante que as relações 'groups' estejam carregadas para o usuário (se autenticado)
            if (!$user->relationLoaded('groups')) {
                $user->load('groups');
            }

            // Condição 2: Usuário é admin ou fotógrafo (sempre pode ver)
            if ($user->hasRole(['admin', 'fotografo'])) {
                return true;
            }

            // Condição 3: Usuário é o dono da galeria (sempre pode ver)
            if ($gallery->user_id === $user->id) {
                return true;
            }

            // Condição 4: Usuário pertence a qualquer um dos grupos da galeria
            $userGroupIds = $user->groups->pluck('id')->toArray();
            $galleryGroupIds = $gallery->groups->pluck('id')->toArray();

            if (array_intersect($userGroupIds, $galleryGroupIds)) {
                return true;
            }

            // Se nenhuma das condições acima for atendida, nega o acesso
            return false;
        });

        // Usuário pode gerenciar imagem se for admin, fotógrafo ou dono da galeria
        // ARGUMENTO NULLABLE: ?Image $image = null
        Gate::define('manage-image', function (User $user, ?Image $image = null) {
            if ($image) {
                // Carrega a galeria da imagem para verificar o dono
                $gallery = \App\Models\Gallery::find($image->gallery_id);
                if (!$gallery) return false; // Galeria não encontrada

                return $user->hasRole(['admin', 'fotografo']) || $user->id === $gallery->user_id;
            }
            // Se nenhum modelo for passado
            return $user->hasRole(['admin', 'fotografo']); // Ex: Apenas admin/fotografo podem gerenciar imagens em geral
        });
    }
}
