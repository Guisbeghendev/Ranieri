<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Group;
use App\Models\Image; // Certifique-se de que o modelo Image está importado
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // Importa a Facade Gate

class PublicGalleryController extends Controller
{
    /**
     * Exibe a tela de seleção de grupos (para usuários logados)
     * ou a lista de galerias públicas (para guests).
     */
    public function index()
    {
        if (Auth::check()) {
            // Se o usuário está autenticado, mostra a tela de seleção de grupos
            $user = Auth::user()->load('groups');
            return Inertia::render('PublicGalleries/GroupSelection', [
                'userGroups' => $user->groups,
            ]);
        } else {
            // Se o usuário não está autenticado (guest), mostra apenas galerias públicas
            $publicGroup = Group::where('name', 'público')->first();
            $galleries = collect(); // Coleção vazia por padrão

            if ($publicGroup) {
                // Se existe um grupo público, filtra as galerias associadas a ele
                $galleries = Gallery::whereHas('groups', function ($q) use ($publicGroup) {
                    $q->where('groups.id', $publicGroup->id);
                })->with('groups', 'images')->orderBy('event_date', 'desc')->get();
            }

            return Inertia::render('PublicGalleries/ListGalleries', [
                'galleries' => $galleries,
                'selectedGroupName' => 'Todas as Galerias Públicas',
            ]);
        }
    }

    /**
     * Exibe a lista de galerias filtradas por um grupo específico.
     *
     * @param Group $group O grupo selecionado pelo usuário.
     * @return \Inertia\Response
     */
    public function listByGroup(Group $group)
    {
        // Garante que o usuário autenticado tem permissão para acessar este grupo
        // A Gate 'access-group' no AuthServiceProvider já verifica se o usuário
        // é admin ou pertence ao grupo.
        Gate::authorize('access-group', $group);

        // Busca as galerias associadas a este grupo
        $galleries = Gallery::whereHas('groups', function ($q) use ($group) {
            $q->where('groups.id', $group->id);
        })->with('groups', 'images')->orderBy('event_date', 'desc')->get();

        return Inertia::render('PublicGalleries/ListGalleries', [
            'galleries' => $galleries,
            'selectedGroupName' => $group->name, // Passa o nome do grupo para o título da tela
        ]);
    }

    /**
     * Exibe os detalhes de uma galeria específica para o usuário final,
     * aplicando controle de acesso via Gate 'view-public-gallery'.
     *
     * @param Gallery $gallery A instância da galeria resolvida via Route Model Binding.
     * @return \Inertia\Response
     */
    public function show(Gallery $gallery)
    {
        // Utiliza a Gate 'view-public-gallery' para autorização (a nova Gate dinâmica)
        // Esta Gate no AuthServiceProvider.php agora contém toda a lógica de acesso para a área pública.
        Gate::authorize('view-public-gallery', $gallery);

        // Carrega explicitamente a relação 'groups' (necessário para a Gate e para o frontend)
        $gallery->load('groups');

        // Pagina as imagens da galeria
        $images = $gallery->images()->paginate(30);

        // Prepara os dados da galeria para enviar ao frontend.
        // Convertemos para array e removemos a relação 'images' para evitar duplicação,
        // mas garantimos que 'groups' esteja incluído.
        $galleryData = $gallery->toArray();
        unset($galleryData['images']); // Remove a relação 'images' se ela foi carregada

        return Inertia::render('PublicGalleries/Show', [
            'gallery' => $galleryData, // Passa a galeria com 'groups' e sem a coleção completa de 'images'
            'images' => $images, // Passa as imagens paginadas separadamente
        ]);
    }
}
