<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Listar todos os usuários com filtros opcionais
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with('company');

            // Filtros
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->has('tipo')) {
                $query->where('tipo', $request->get('tipo'));
            }

            if ($request->has('id_comp')) {
                $query->where('id_comp', $request->get('id_comp'));
            }

            // Ordenação
            $orderBy = $request->get('order_by', 'name');
            $orderDir = $request->get('order_dir', 'asc');
            $query->orderBy($orderBy, $orderDir);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $users = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Usuários listados com sucesso',
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar usuários',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar um usuário específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = User::with('company')->find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuário encontrado com sucesso',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar um novo usuário
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'phone' => 'nullable|string|max:20',
                'id_comp' => 'nullable|exists:companies,id',
                'tipo' => ['required', Rule::in(['admin', 'comum'])],
            ], [
                'name.required' => 'O nome é obrigatório',
                'email.required' => 'O email é obrigatório',
                'email.email' => 'O email deve ser válido',
                'email.unique' => 'Este email já está em uso',
                'password.required' => 'A senha é obrigatória',
                'password.min' => 'A senha deve ter pelo menos 6 caracteres',
                'phone.max' => 'O telefone deve ter no máximo 20 caracteres',
                'id_comp.exists' => 'A companhia informada não existe',
                'tipo.required' => 'O tipo é obrigatório',
                'tipo.in' => 'O tipo deve ser admin ou comum',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'id_comp' => $request->id_comp,
                'tipo' => $request->tipo,
            ]);

            $user->load('company');

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar um usuário existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($id)],
                'phone' => 'nullable|string|max:20',
                'id_comp' => 'nullable|exists:companies,id',
                'tipo' => ['sometimes', 'required', Rule::in(['admin', 'comum'])],
            ], [
                'name.required' => 'O nome é obrigatório',
                'email.required' => 'O email é obrigatório',
                'email.email' => 'O email deve ser válido',
                'email.unique' => 'Este email já está em uso',
                'phone.max' => 'O telefone deve ter no máximo 20 caracteres',
                'id_comp.exists' => 'A companhia informada não existe',
                'tipo.required' => 'O tipo é obrigatório',
                'tipo.in' => 'O tipo deve ser admin ou comum',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->update($request->only(['name', 'email', 'phone', 'id_comp', 'tipo']));
            $user->load('company');

            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir um usuário
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            // Verificar se não é o último administrador
            if ($user->tipo === 'admin') {
                $adminCount = User::where('tipo', 'admin')->count();
                if ($adminCount <= 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Não é possível excluir o último administrador'
                    ], 400);
                }
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuário excluído com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trocar senha do usuário
     */
    public function changePassword(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|different:current_password',
                'confirm_password' => 'required|same:new_password',
            ], [
                'current_password.required' => 'A senha atual é obrigatória',
                'new_password.required' => 'A nova senha é obrigatória',
                'new_password.min' => 'A nova senha deve ter pelo menos 6 caracteres',
                'new_password.different' => 'A nova senha deve ser diferente da atual',
                'confirm_password.required' => 'A confirmação da senha é obrigatória',
                'confirm_password.same' => 'A confirmação da senha deve ser igual à nova senha',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            // Verificar se a senha atual está correta
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Senha atual incorreta'
                ], 400);
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Senha alterada com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar senha',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pesquisar usuários com filtros avançados
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = User::with('company');

            // Filtros de texto
            if ($request->has('q')) {
                $search = $request->get('q');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Filtros específicos
            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->get('name') . '%');
            }

            if ($request->has('email')) {
                $query->where('email', 'like', '%' . $request->get('email') . '%');
            }

            if ($request->has('phone')) {
                $query->where('phone', 'like', '%' . $request->get('phone') . '%');
            }

            if ($request->has('tipo')) {
                $query->where('tipo', $request->get('tipo'));
            }

            if ($request->has('id_comp')) {
                $query->where('id_comp', $request->get('id_comp'));
            }

            // Filtros de data
            if ($request->has('created_from')) {
                $query->whereDate('created_at', '>=', $request->get('created_from'));
            }

            if ($request->has('created_to')) {
                $query->whereDate('created_at', '<=', $request->get('created_to'));
            }

            // Ordenação
            $orderBy = $request->get('order_by', 'name');
            $orderDir = $request->get('order_dir', 'asc');
            $query->orderBy($orderBy, $orderDir);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $users = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Pesquisa realizada com sucesso',
                'data' => $users,
                'filters_applied' => $request->only(['q', 'name', 'email', 'phone', 'tipo', 'id_comp', 'created_from', 'created_to'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar pesquisa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar usuários por companhia
     */
    public function byCompany(int $companyId): JsonResponse
    {
        try {
            $company = Company::find($companyId);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Companhia não encontrada'
                ], 404);
            }

            $users = User::where('id_comp', $companyId)
                        ->with('company')
                        ->orderBy('name')
                        ->get();

            return response()->json([
                'success' => true,
                'message' => 'Usuários da companhia listados com sucesso',
                'data' => [
                    'company' => $company,
                    'users' => $users,
                    'total_users' => $users->count(),
                    'admin_count' => $users->where('tipo', 'admin')->count(),
                    'common_count' => $users->where('tipo', 'comum')->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar usuários da companhia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estatísticas dos usuários
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'admin_users' => User::where('tipo', 'admin')->count(),
                'common_users' => User::where('tipo', 'comum')->count(),
                'users_with_company' => User::whereNotNull('id_comp')->count(),
                'users_without_company' => User::whereNull('id_comp')->count(),
                'users_with_phone' => User::whereNotNull('phone')->count(),
                'users_without_phone' => User::whereNull('phone')->count(),
                'companies_with_users' => User::distinct('id_comp')->whereNotNull('id_comp')->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas obtidas com sucesso',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

