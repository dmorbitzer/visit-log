<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventUserController extends Controller
{
    public function index(Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $users = $event->users()
            ->select('users.id', 'users.name', 'users.username')
            ->withPivot('permission')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'permission' => $user->pivot->permission,
            ]);

        return response()->json($users);
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'permission' => ['required', Rule::enum(Permission::class)],
        ]);

        $event->users()->syncWithoutDetaching([
            $validated['user_id'] => ['permission' => $validated['permission']],
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Event $event, User $user): RedirectResponse
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'permission' => ['required', Rule::enum(Permission::class)],
        ]);

        $event->users()->updateExistingPivot($user->id, $validated);

        return redirect()->back();
    }

    public function destroy(Event $event, User $user): RedirectResponse
    {
        $this->authorize('update', $event);

        $event->users()->detach($user->id);

        return redirect()->back();
    }
}
