<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ParentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminParentController extends Controller
{
    public function index()
    {
        return response()->json(
            User::where('role', 'parent')
                ->with(['parent.children.user', 'parent.children.class'])
                ->latest()
                ->get()
                ->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'avatar' => $u->avatar,
                    'parent' => $u->parent ? [
                        'id' => $u->parent->id,
                        'children' => $u->parent->children->map(fn($s) => [
                            'id' => $s->id,
                            'name' => $s->user->name ?? '—',
                            'class_name' => $s->class->name ?? '—',
                            'section' => $s->class->section ?? '',
                        ]),
                    ] : null,
                ])
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'parent',
        ]);

        ParentModel::create([
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'تم إضافة ولي الأمر',
            'parent' => User::with('parent.children.user')->find($user->id),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::where('role', 'parent')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'avatar' => 'sometimes|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $userData = [];
        if (isset($validated['name'])) $userData['name'] = $validated['name'];
        if (isset($validated['email'])) $userData['email'] = $validated['email'];
        if (array_key_exists('phone', $validated)) $userData['phone'] = $validated['phone'];
        if (!empty($validated['password'])) $userData['password'] = Hash::make($validated['password']);
        if (!empty($userData)) $user->update($userData);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->update(['avatar' => $request->file('avatar')->store('avatars', 'public')]);
        }

        if ($request->input('remove_avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->update(['avatar' => null]);
        }

        return response()->json(['message' => 'تم تحديث ولي الأمر']);
    }

    public function destroy($id)
    {
        $user = User::where('role', 'parent')->findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'تم حذف ولي الأمر']);
    }
}
