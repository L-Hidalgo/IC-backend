<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Actions\Users\CreateUser;
use App\Datatables\UserDatatable;
use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request, UserDatatable $datatable): JsonResponse
    {
        $data = $datatable->make($request);
        return response()->json($data);
    }

    public function store(Request    $request, CreateUser $createUser)
    {

        $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['nullable', 'string']
        ]);

        $user = $createUser->execute(new UserDTO([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password'])
        ]));

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data'    => $user
        ]);
    }

    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        if ($user) {
            if ($request->has('roles')) {
                $rolesExist = Role::whereIn('id', $request->roles)->count() === count($request->roles);
                if ($rolesExist) {
                    $user->roles()->sync($request->roles);
                    return response()->json(['message' => 'Roles actualizados correctamente'], 200);
                } else {
                    return response()->json(['error' => 'Algunos roles proporcionados no existen'], 400);
                }
            } else {
                return response()->json(['error' => 'No se proporcionaron roles en la solicitud'], 400);
            }
        } else {
            return response()->json(['error' => 'No se encontró el usuario'], 404);
        }
    }


    public function destroy(User $user): JsonResponse
    {

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function listarUsersDDE()
    {
        $gerencias = [
            'Dotacion Evaluacion y Capacitacion',
            'Administracion y Recursos Humanos'
        ];

        $users = User::select(['id', 'name'])
            ->whereIn('gerencia', $gerencias)
            ->get();

        return $this->sendList($users);
    }

    public function listarUsers(Request $request)
    {
        $limit = $request->input('limit');
        $page = $request->input('page');
        
        $nombreUsuario = $request->input('query.nombreUsuario');

        $query = User::select(['id', 'name', 'ci', 'username', 'email', 'cargo'])
            ->with('roles:name')
            ->orderBy('id', 'asc');

        if ($nombreUsuario !== null) {
            $query->where('name', 'LIKE', '%' . $nombreUsuario . '%');
        }

        $users = $query->paginate($limit, ['*'], 'page', $page);

        $users->getCollection()->transform(function ($user) {
            $user->rol = $user->roles->implode('name', ', ');
            unset($user->roles);
            return $user;
        });

        return $this->sendPaginated($users);
    }


    public function obtenerRolUser($userId)
    {
        $rol = DB::table('model_has_roles')
            ->where('model_id', $userId)
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name')
            ->first();

        if ($rol) {
            return response()->json(['rol' => $rol->name], 200);
        } else {
            return response()->json(['error' => 'El usuario no tiene un rol asignado'], 404);
        }
    }
}
