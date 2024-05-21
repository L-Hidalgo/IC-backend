<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        //$request->session()->regenerate();
        
        $user = Auth::user();

        return response()->json([
            'message' => 'Autenticación exitosa',

            'user' => $user
        ]);
    }*/
    public function store(LoginRequest $request)
    {
        try {
            $request->authenticate();
            $user = Auth::user();

            // Obtener el factory de tokens de acceso
            $token = $user->createToken("API Token")->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'El usuario inició sesión correctamente',
                'user' => $user,
                'token' => $token
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
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
