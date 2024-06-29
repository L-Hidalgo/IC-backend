<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AuthenticatedSessionController extends Controller
{

    /**
     * Muestra la vista de inicio de sesión.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    /*public function store(LoginRequest $request)
    {
        try {
            $request->authenticate();
            $user = Auth::user();
            $token = $user->createToken("API Token")->plainTextToken;

            $role = $user->roles->first(); 

            $roleName = $role ? $role->name : null;

            if (!$role) {
                $roleLector = Role::where('name', 'Lector')->first();
                if (!$roleLector) {
                    return response()->json(['error' => 'No se encontró el rol "Lector"'], 500);
                }
                $user->syncRoles($roleLector);
                $roleName = 'Lector'; 
            }

            return response()->json([
                'status' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $roleName,
                    'ci' => $user->ci,
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }*/
    public function store(LoginRequest $request)
{
    try {
        // Obtener las credenciales del request
        $credentials = $request->only('username', 'password');

        // Verificar si las credenciales son válidas y autenticar al usuario
        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        // Obtener el usuario autenticado
        $user = Auth::user();

        // Verificar si el usuario tiene un rol asignado
        if (!$user->hasRole()) {
            // Si no tiene un rol asignado, asignar el rol "Lector" por defecto si existe
            $roleLector = Role::where('name', 'Lector')->first();
            if (!$roleLector) {
                return response()->json(['error' => 'No se encontró el rol "Lector"'], 500);
            }
            $user->syncRoles($roleLector);
        }

        // Crear un nuevo token para el usuario
        $token = $user->createToken("API Token")->plainTextToken;

        // Obtener el rol del usuario
        $role = $user->roles->first();
        $roleName = $role ? $role->name : null;

        // Construir la respuesta JSON
        $response = [
            'status' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $roleName,
                'ci' => $user->ci,
            ],
        ];

        return response()->json($response, 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Capturar excepción de validación
        return response()->json(['error' => 'Error de validación: ' . $e->getMessage()], 400);
    } catch (\Illuminate\Auth\AuthenticationException $e) {
        // Capturar excepción de autenticación
        return response()->json(['error' => 'Error de autenticación: ' . $e->getMessage()], 401);
    } catch (\Throwable $th) {
        // Capturar otras excepciones
        return response()->json(['error' => 'Error interno del servidor: ' . $th->getMessage()], 500);
    }
}




    /**
     * Destruye una sesión autenticada.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
