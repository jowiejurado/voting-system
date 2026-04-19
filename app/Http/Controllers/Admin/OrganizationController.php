<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

        // `name` is encrypted at rest; SQL LIKE cannot match plaintext.
        if ($q === '') {
            $organizations = Organization::query()
                ->latest()
                ->paginate($perPage)
                ->withQueryString();
        } else {
            $filtered = Organization::query()
                ->latest()
                ->get()
                ->filter(fn (Organization $org) => mb_stripos((string) $org->name, $q) !== false)
                ->values();

            $page = max(1, (int) $request->get('page', 1));
            $organizations = new LengthAwarePaginator(
                $filtered->slice(($page - 1) * $perPage, $perPage)->values(),
                $filtered->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

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

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()->route('admin.organizations.index')
            ->with([
                'success' => 'Organization deleted.',
                'buttonText' => 'Proceed',
            ]);
    }
}
