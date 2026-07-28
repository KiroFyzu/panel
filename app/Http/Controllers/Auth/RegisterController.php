<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\User;
use Pterodactyl\Rules\Username;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Pterodactyl\Services\Users\UserCreationService;
use Pterodactyl\Exceptions\Model\DataValidationException;

class RegisterController extends AbstractLoginController
{
    /**
     * RegisterController constructor.
     */
    public function __construct(private UserCreationService $creationService)
    {
        parent::__construct();
    }

    /**
     * Render registration page.
     */
    public function index(): View
    {
        return view('templates.auth.core');
    }

    /**
     * Handle registration request.
     *
     * @throws ValidationException
     * @throws DataValidationException
     * @throws \Exception
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'between:1,191', 'unique:users,username', new Username()],
            'email' => ['required', 'email', 'between:1,191', 'unique:users,email'],
            'name_first' => ['required', 'string', 'between:1,191'],
            'name_last' => ['required', 'string', 'between:1,191'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->creationService->handle($validated);

        $this->auth->login($user);

        return new JsonResponse([
            'success' => true,
            'redirect' => '/',
        ]);
    }
}
