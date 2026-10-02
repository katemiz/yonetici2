<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ManagerUserController extends Controller
{
    public function index(): Response
    {
        $managers = User::query()
            ->where('role', 'manager')
            ->withCount('binalar')
            ->orderBy('name')
            ->paginate(20)
            ->through(fn (User $manager) => [
                'id' => $manager->id,
                'name' => $manager->name,
                'lastname' => $manager->lastname,
                'email' => $manager->email,
                'building_quota' => $manager->building_quota,
                'building_count' => $manager->binalar_count,
                'is_active' => $manager->is_active,
            ]);

        return Inertia::render('ManagerUsers', ['managers' => $managers]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
            'building_quota' => ['required', 'integer', 'min:1'],
        ]);

        $manager = new User([
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $manager->forceFill([
            'role' => 'manager',
            'building_quota' => $data['building_quota'],
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        return redirect()->route('admin.managers.index')->with('success', 'Yönetici hesabı oluşturuldu.');
    }

    public function update(Request $request, User $manager): RedirectResponse
    {
        abort_unless($manager->role === 'manager', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $manager->id],
            'building_quota' => ['required', 'integer', 'min:1'],
        ]);

        $manager->fill([
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
        ]);
        $manager->building_quota = $data['building_quota'];
        $manager->save();

        return redirect()->route('admin.managers.index')->with('success', 'Yönetici hesabı güncellendi.');
    }

    public function toggleActive(User $manager): RedirectResponse
    {
        abort_unless($manager->role === 'manager', 404);

        $manager->is_active = !$manager->is_active;
        $manager->save();

        return redirect()->route('admin.managers.index')->with('success', 'Yönetici hesap durumu güncellendi.');
    }
}
