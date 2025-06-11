<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Group;
use App\Models\Profile;
use App\Models\Avatar; // Certifique-se de importar o modelo Avatar
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // Importar Storage para manipulação de arquivos

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        // Carregando roles e groups para cada usuário
        // Carregando também o perfil e a relação de avatar, para que possa ser exibido na lista, se necessário.
        $users = User::with('roles', 'groups', 'profile.avatarRelation')->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $roles = Role::all(['id', 'name']);
        $groups = Group::all(['id', 'name']);

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
            'groups' => $groups,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'groups' => 'array',
            'groups.*' => 'exists:groups,id',
            // --- Validação para campos do Profile ---
            'avatar' => 'nullable|image|max:2048', // Alterado para validar como arquivo de imagem
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'whatsapp' => 'nullable|string|max:30',
            'other_contact' => 'nullable|string|max:255',
            'ranieri_text' => 'nullable|string',
            'biography' => 'nullable|string',
            // --- Fim da validação Profile ---
        ], [
            'name.required' => 'O nome do usuário é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser um endereço de e-mail válido.',
            'email.unique' => 'Este e-mail já está sendo usado por outro usuário.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos :min caracteres.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
            'roles.*.exists' => 'Um ou mais papéis selecionados são inválidos.',
            'groups.*.exists' => 'Um ou mais grupos selecionados são inválidos.',
            'avatar.image' => 'O arquivo do avatar deve ser uma imagem.',
            'avatar.max' => 'O avatar não pode ter mais de 2MB.',
            'birth_date.date' => 'A data de nascimento deve ser uma data válida.',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Cria o perfil do usuário
            $profile = $user->profile()->create([
                'birth_date' => $request->birth_date,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'whatsapp' => $request->whatsapp,
                'other_contact' => $request->other_contact,
                'ranieri_text' => $request->ranieri_text,
                'biography' => $request->biography,
            ]);

            // --- Lógica para upload do Avatar (para criação) ---
            if ($request->hasFile('avatar')) {
                $uploadedAvatar = $request->file('avatar');
                $storageDir = 'avatars';
                // Usar o ID do perfil para nomear o arquivo garante unicidade por perfil
                $filename = $profile->id . '.' . $uploadedAvatar->getClientOriginalExtension();
                $pathSaved = $uploadedAvatar->storeAs($storageDir, $filename, 'public');
                $publicUrlPath = Storage::url($pathSaved);

                $profile->avatarRelation()->create([
                    'path' => $pathSaved,
                    'url' => $publicUrlPath,
                    'original_filename' => $uploadedAvatar->getClientOriginalName(),
                    'mime_type' => $uploadedAvatar->getMimeType(),
                    'size' => $uploadedAvatar->getSize(),
                ]);
            }
            // --- Fim da lógica Avatar ---

            // Sincroniza roles e groups
            $user->roles()->sync($request->input('roles', []));
            $user->groups()->sync($request->input('groups', []));

            DB::commit();
            // CORREÇÃO: Adicionado ->withStatus(303) para que o Inertia.js lide corretamente com o redirecionamento após POST.
            return redirect()->route('admin.users.index')->with('success', 'Usuário criado com sucesso!')->withStatus(303);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar usuário: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Erro ao criar usuário. Verifique o log para detalhes.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): Response
    {
        $roles = Role::all(['id', 'name']);
        $groups = Group::all(['id', 'name']);

        // Carregar o perfil do usuário e as relações de avatar, roles e groups
        $user->load('profile.avatarRelation', 'roles', 'groups');
        $userRoleIds = $user->roles->pluck('id')->toArray();
        $userGroupIds = $user->groups->pluck('id')->toArray();

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'groups' => $groups,
            'userRoleIds' => $userRoleIds,
            'userGroupIds' => $userGroupIds,
            'profile' => $user->profile, // O perfil já está carregado com avatarRelation
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'groups' => 'array',
            'groups.*' => 'exists:groups,id',
            // --- Validação para campos do Profile ---
            'avatar' => 'nullable|image|max:2048', // Alterado para validar como arquivo de imagem
            'remove_avatar' => 'nullable|boolean', // Adicionado para lidar com a remoção
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'whatsapp' => 'nullable|string|max:30',
            'other_contact' => 'nullable|string|max:255',
            'ranieri_text' => 'nullable|string',
            'biography' => 'nullable|string',
            // --- Fim da validação Profile ---
        ], [
            'name.required' => 'O nome do usuário é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser um endereço de e-mail válido.',
            'email.unique' => 'Este e-mail já está sendo usado por outro usuário.',
            'password.min' => 'A senha deve ter pelo menos :min caracteres.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
            'roles.*.exists' => 'Um ou mais papéis selecionados são inválidos.',
            'groups.*.exists' => 'Um ou mais grupos selecionados são inválidos.',
            'avatar.image' => 'O arquivo do avatar deve ser uma imagem.',
            'avatar.max' => 'O avatar não pode ter mais de 2MB.',
            'birth_date.date' => 'A data de nascimento deve ser uma data válida.',
        ]);

        DB::beginTransaction();
        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
            ]);

            // Obtém ou cria o perfil do usuário
            $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

            // --- Lógica para o Avatar (para edição) ---
            if ($request->hasFile('avatar')) {
                $uploadedAvatar = $request->file('avatar');
                $storageDir = 'avatars';

                // Se já existe um avatar associado, exclui o arquivo e o registro do banco de dados
                if ($profile->avatarRelation) {
                    if (Storage::disk('public')->exists($profile->avatarRelation->path)) {
                        Storage::disk('public')->delete($profile->avatarRelation->path);
                    }
                    $profile->avatarRelation->delete(); // Exclui o registro do Avatar no banco de dados
                }

                // Gera o nome do arquivo usando o ID do perfil
                $filename = $profile->id . '.' . $uploadedAvatar->getClientOriginalExtension();
                $pathSaved = $uploadedAvatar->storeAs($storageDir, $filename, 'public');
                $publicUrlPath = Storage::url($pathSaved);

                // Cria um novo registro na tabela 'avatars' e associa ao perfil
                $profile->avatarRelation()->create([
                    'path' => $pathSaved,
                    'url' => $publicUrlPath,
                    'original_filename' => $uploadedAvatar->getClientOriginalName(),
                    'mime_type' => $uploadedAvatar->getMimeType(),
                    'size' => $uploadedAvatar->getSize(),
                ]);
            } elseif ($request->boolean('remove_avatar') && $profile->avatarRelation) {
                // Se o usuário marcou para remover o avatar e um avatar existe
                if (Storage::disk('public')->exists($profile->avatarRelation->path)) {
                    Storage::disk('public')->delete($profile->avatarRelation->path);
                }
                $profile->avatarRelation->delete(); // Exclui o registro do Avatar do banco de dados
            }
            // --- Fim da lógica Avatar ---

            // Atualiza os outros campos do perfil.
            // A chave 'avatar' NÃO deve estar aqui, pois ela é um arquivo e já foi tratada.
            // A chave 'remove_avatar' também não é um campo do DB, é uma flag.
            $profile->update($request->only([
                'birth_date',
                'address',
                'city',
                'state',
                'whatsapp',
                'other_contact',
                'ranieri_text',
                'biography',
            ]));

            // Sincroniza roles e groups
            $user->roles()->sync($request->input('roles', []));
            $user->groups()->sync($request->input('groups', []));

            DB::commit();
            // CORREÇÃO: Adicionado ->withStatus(303) para que o Inertia.js lide corretamente com o redirecionamento após PUT/PATCH.
            return redirect()->route('admin.users.index')->with('success', 'Usuário atualizado com sucesso!')->withStatus(303);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Erro ao atualizar usuário. Verifique o log para detalhes.');
        }
    }

    /**
     * Remove o recurso especificado do armazenamento.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Cuidado ao excluir o próprio usuário logado!
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        DB::beginTransaction();
        try {
            // Se o usuário tiver um perfil com avatar, exclui o arquivo do disco antes de excluir o perfil/usuário
            if ($user->profile && $user->profile->avatarRelation) {
                if (Storage::disk('public')->exists($user->profile->avatarRelation->path)) {
                    Storage::disk('public')->delete($user->profile->avatarRelation->path);
                }
            }
            // Devido ao onDelete('cascade') na migração do perfil, o perfil será excluído automaticamente.
            // O Avatar também será excluído via cascade se a foreign key estiver bem configurada.
            $user->delete();
            DB::commit();
            // CORREÇÃO: Adicionado ->withStatus(303) para que o Inertia.js lide corretamente com o redirecionamento após DELETE.
            return redirect()->route('admin.users.index')->with('success', 'Usuário excluído com sucesso!')->withStatus(303);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir usuário: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Erro ao excluir usuário. Verifique o log para detalhes.');
        }
    }


    /**
     * O método show não é necessário para o CRUD de admin.
     */
    public function show(User $user)
    {
        abort(404);
    }

    /**
     * Exibe o formulário para associação em massa de roles a usuários.
     */
    public function massAssignRolesIndex(Request $request): Response
    {
        // 1. Filtragem e carregamento de Usuários com Perfil
        $usersQuery = User::with('profile');

        if ($request->has('created_at_start') && $request->input('created_at_start')) {
            $usersQuery->whereDate('created_at', '>=', $request->input('created_at_start'));
        }

        if ($request->has('created_at_end') && $request->input('created_at_end')) {
            $usersQuery->whereDate('created_at', '<=', $request->input('created_at_end'));
        }

        // Adicionar ordenação padrão
        $usersQuery->orderBy('name');

        $users = $usersQuery->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                // Acessa ranieri_text via relacionamento profile, com fallback 'N/A'
                'ranieri_text' => $user->profile->ranieri_text ?? 'N/A',
                'created_at' => $user->created_at->format('d/m/Y H:i'), // Formata a data para exibição
            ];
        });

        // 2. Obtenção de todos os Roles
        $roles = Role::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Roles/MassAssign', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['created_at_start', 'created_at_end']),
        ]);
    }

    /**
     * Processa a associação em massa de roles aos usuários selecionados.
     */
    public function massAssignRolesStore(Request $request): RedirectResponse
    {
        $request->validate([
            'selected_user_ids' => 'required|array|min:1',
            'selected_user_ids.*' => 'exists:users,id',
            'selected_role_ids' => 'required|array|min:1',
            'selected_role_ids.*' => 'exists:roles,id',
        ], [
            'selected_user_ids.required' => 'Selecione pelo menos um usuário.',
            'selected_user_ids.min' => 'Selecione pelo menos um usuário.',
            'selected_role_ids.required' => 'Selecione pelo menos um papel (role).',
            'selected_role_ids.min' => 'Selecione pelo menos um papel (role).',
        ]);

        $selectedUserIds = $request->input('selected_user_ids');
        $selectedRoleIds = $request->input('selected_role_ids');

        DB::beginTransaction();
        try {
            foreach ($selectedUserIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    // Anexa os novos roles ao usuário, evitando duplicidade
                    // O syncWithoutDetaching garante que roles existentes não sejam removidos
                    $user->roles()->syncWithoutDetaching($selectedRoleIds);
                }
            }
            DB::commit();
            // CORREÇÃO: Adicionado ->withStatus(303) para que o Inertia.js lide corretamente com o redirecionamento após POST.
            return redirect()->back()->with('success', 'Papéis associados aos usuários com sucesso!')->withStatus(303);
        } catch (\Exception $e) {
            DB::rollBack();
            // Logar o erro para depuração
            Log::error('Erro ao associar papéis em massa: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Erro ao associar papéis. Verifique o log para detalhes.');
        }
    }

    // --- NOVOS MÉTODOS PARA ASSOCIAÇÃO EM MASSA DE GRUPOS ---

    /**
     * Exibe o formulário para associação em massa de grupos a usuários.
     */
    public function massAssignGroupsIndex(Request $request): Response
    {
        // 1. Filtragem e carregamento de Usuários com Perfil (mesma lógica de roles)
        $usersQuery = User::with('profile');

        if ($request->has('created_at_start') && $request->input('created_at_start')) {
            $usersQuery->whereDate('created_at', '>=', $request->input('created_at_start'));
        }

        if ($request->has('created_at_end') && $request->input('created_at_end')) {
            $usersQuery->whereDate('created_at', '<=', $request->input('created_at_end'));
        }

        $usersQuery->orderBy('name');

        $users = $usersQuery->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'ranieri_text' => $user->profile->ranieri_text ?? 'N/A',
                'created_at' => $user->created_at->format('d/m/Y H:i'),
            ];
        });

        // 2. Obtenção de todos os Grupos
        $groups = Group::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Groups/MassAssign', [
            'users' => $users,
            'groups' => $groups,
            'filters' => $request->only(['created_at_start', 'created_at_end']),
        ]);
    }

    /**
     * Processa a associação em massa de grupos aos usuários selecionados.
     */
    public function massAssignGroupsStore(Request $request): RedirectResponse
    {
        $request->validate([
            'selected_user_ids' => 'required|array|min:1',
            'selected_user_ids.*' => 'exists:users,id',
            'selected_group_ids' => 'required|array|min:1',
            'selected_group_ids.*' => 'exists:groups,id',
        ], [
            'selected_user_ids.required' => 'Selecione pelo menos um usuário.',
            'selected_user_ids.min' => 'Selecione pelo menos um usuário.',
            'selected_group_ids.required' => 'Selecione pelo menos um grupo.',
            'selected_group_ids.min' => 'Selecione pelo menos um grupo.',
        ]);

        $selectedUserIds = $request->input('selected_user_ids');
        $selectedGroupIds = $request->input('selected_group_ids');

        DB::beginTransaction();
        try {
            foreach ($selectedUserIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->groups()->syncWithoutDetaching($selectedGroupIds);
                }
            }
            DB::commit();
            // CORREÇÃO: Adicionado ->withStatus(303) para que o Inertia.js lide corretamente com o redirecionamento após POST.
            return redirect()->back()->with('success', 'Grupos associados aos usuários com sucesso!')->withStatus(303);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao associar grupos em massa: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Erro ao associar grupos. Verifique o log para detalhes.');
        }
    }
}
