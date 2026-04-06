<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

        $organizations = Organization::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.organizations.index', compact('organizations', 'q', 'perPage'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Organization::create([
            'name' => $data['name'],
        ]);

        return redirect()->route('admin.organizations.index')
            ->with([
                'success' => 'Successfully Submitted',
                'buttonText' => 'Proceed',
            ]);
    }

    public function update(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $organization->update([
            'name' => $data['name'],
        ]);

        return redirect()->route('admin.organizations.index')
            ->with([
                'success' => 'Successfully Submitted',
                'buttonText' => 'Proceed',
            ]);
    }
}
