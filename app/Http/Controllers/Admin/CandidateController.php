<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function index()
    {
        $election = Election::first();
        $candidates = Candidate::with('position')
            ->when($election, fn($q)=>$q->where('election_id', $election->id))
            ->orderBy('id','desc')
            ->get();
        return view('admin.candidates.index', compact('candidates','election'));
    }

    public function create()
    {
        $elections = Election::orderBy('id','desc')->get();
        $positions = Position::orderBy('order_index')->get();
        return view('admin.candidates.create', compact('elections','positions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'election_id' => 'required|exists:elections,id',
            'position_id' => 'required|exists:positions,id',
            'name' => 'required|string|max:255',
            'party' => 'nullable|string|max:255',
        ]);
        Candidate::create($data);
        return redirect()->route('admin.candidates.index')->with('success', 'Candidate created');
    }

    public function edit(Candidate $candidate)
    {
        $elections = Election::orderBy('id','desc')->get();
        $positions = Position::orderBy('order_index')->get();
        return view('admin.candidates.edit', compact('candidate','elections','positions'));
    }

    public function update(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'election_id' => 'required|exists:elections,id',
            'position_id' => 'required|exists:positions,id',
            'name' => 'required|string|max:255',
            'party' => 'nullable|string|max:255',
        ]);
        $candidate->update($data);
        return redirect()->route('admin.candidates.index')->with('success', 'Candidate updated');
    }

    public function destroy(Candidate $candidate)
    {
        $candidate->delete();
        return back()->with('success', 'Candidate deleted');
    }
}
