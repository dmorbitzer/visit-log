<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * @group Users
 */
class UserController extends Controller
{
    public function index(Request $request): InertiaResponse|JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json($users);
        }

        return Inertia::render('users/index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        $user = User::create($validated);

        if ($request->wantsJson()) {
            return response()->json($user, 201);
        }

        return redirect()->route('users.index');
    }

    public function update(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['nullable', Password::defaults()],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($request->wantsJson()) {
            return response()->json($user->fresh());
        }

        return redirect()->route('users.index');
    }

    public function destroy(Request $request, User $user): RedirectResponse|Response
    {
        $this->authorize('delete', $user);

        $user->delete();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->route('users.index');
    }
}
