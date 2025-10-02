<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VoterController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $voters = User::where('role', 'voter')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('member_id', 'like', "%{$q}%")
                       ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString();
        return view('admin.voters.index', compact('voters', 'q'));
    }

    public function create()
    {
        return view('admin.voters.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'member_id' => 'required|string|max:64|unique:users,member_id',
            'phone' => 'nullable|string|max:32',
            'password' => 'required|string|min:6',
        ]);
        User::create([
            'name' => $data['name'],
            'email' => null,
            'member_id' => $data['member_id'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'voter',
        ]);
        return redirect()->route('admin.voters.index')->with('success', 'Voter created');
    }

    public function edit(User $voter)
    {
        abort_unless($voter->role === 'voter', 404);
        return view('admin.voters.edit', compact('voter'));
    }

    public function update(Request $request, User $voter)
    {
        abort_unless($voter->role === 'voter', 404);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'member_id' => 'required|string|max:64|unique:users,member_id,' . $voter->id,
            'phone' => 'nullable|string|max:32',
            'password' => 'nullable|string|min:6',
        ]);
        $voter->name = $data['name'];
        $voter->member_id = $data['member_id'];
        $voter->phone = $data['phone'] ?? null;
        if (!empty($data['password'])) {
            $voter->password = Hash::make($data['password']);
        }
        $voter->save();
        return redirect()->route('admin.voters.index')->with('success', 'Voter updated');
    }

    public function destroy(User $voter)
    {
        abort_unless($voter->role === 'voter', 404);
        $voter->delete();
        return back()->with('success', 'Voter deleted');
    }
}
