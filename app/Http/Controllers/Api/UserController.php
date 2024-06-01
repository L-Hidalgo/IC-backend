<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Actions\Users\CreateUser;
use App\Actions\Users\UpdateUser;
use App\Datatables\UserDatatable;
use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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

    public function listar()
    {
        $user = User::select(['id', 'name'])
            ->where('gerencia', '=', 'Dotacion Evaluacion y Capacitacion')
            ->get();
        return $this->sendList($user);
    }

    public function listarUser(Request $request)
    {
        $query = User::select(['id', 'name', 'username', 'email', 'cargo', 'gerencia'])->get();
        $users = $query;
        return $this->sendList($users);
    }

    public function ByNameUser($nombre = null)
    {
        $query = User::select(['id', 'name', 'username', 'email', 'cargo']);

        if ($nombre !== null) {
            $query->where('name', 'LIKE', '%' . $nombre . '%');
        }

        $users = $query->get();

        return $this->sendList($users);
    }
}
