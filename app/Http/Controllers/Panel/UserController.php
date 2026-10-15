<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('panel.users.index', [
            'users' => User::with('roles')->orderBy('name')->get(),
            'roles' => Role::with('permissions')->get(),
        ]);
    }

    public function create(): View
    {
        return view('panel.users.form', ['user' => new User(['is_active' => true]), 'roles' => Role::all()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = User::create($data);
        $user->roles()->sync($data['roles']);
        Audit::record('usuario.creado', $user, ['roles' => $data['roles']]);

        return redirect()->route('panel.users.index')->with('status', 'Usuario creado.');
    }

    public function edit(User $user): View
    {
        return view('panel.users.form', ['user' => $user->load('roles'), 'roles' => Role::all()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($user->is($request->user()) && (! $data['is_active'] || ! in_array(Role::where('slug', Role::ADMIN)->value('id'), $data['roles']))) {
            return back()->withErrors(['roles' => 'No puedes quitarte el rol de administrador ni desactivar tu propia cuenta.']);
        }

        $user->update($data);
        $user->roles()->sync($data['roles']);
        Audit::changes('usuario.actualizado', $user);
        Audit::record('usuario.roles', $user, ['roles' => $data['roles']]);

        return redirect()->route('panel.users.index')->with('status', 'Usuario actualizado.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', Password::min(10)->letters()->numbers()],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['roles'] = array_map('intval', $data['roles']);

        return $data;
    }
}
