<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Position;
use App\Models\Vote;
use Illuminate\Http\Request;

class BallotController extends Controller
{
    public function dashboard()
    {
        $active = Election::where('is_active', true)->first();
        return view('voter.dashboard', compact('active'));
    }

    public function show(Request $request)
    {
        /** @var Election $election */
        $election = $request->attributes->get('activeElection');
        $positions = Position::where('election_id', $election->id)->orderBy('order_index')->get();
        $currentIndex = (int) $request->query('step', 0);
        $position = $positions[$currentIndex] ?? null;
        return view('voter.ballot.step', compact('positions', 'position', 'currentIndex'));
    }

    public function step(Request $request)
    {
        $request->validate([
            'position_id' => 'required|exists:positions,id',
            'candidate_id' => 'nullable|exists:candidates,id',
            'current_index' => 'required|integer',
        ]);

        /** @var Election $election */
        $election = $request->attributes->get('activeElection');
        $positionId = (int) $request->input('position_id');
        $candidateId = $request->input('candidate_id');

        if ($candidateId) {
            Vote::updateOrCreate([
                'election_id' => $election->id,
                'position_id' => $positionId,
                'user_id' => auth()->id(),
            ], [
                'candidate_id' => $candidateId,
            ]);
        }

        $next = (int) $request->input('current_index') + 1;
        return redirect()->route('voter.ballot', ['step' => $next]);
    }

    public function submit(Request $request)
    {
        /** @var Election $election */
        $election = $request->attributes->get('activeElection');
        $positions = Position::where('election_id', $election->id)->orderBy('order_index')->get();
        $votes = Vote::where('election_id', $election->id)->where('user_id', auth()->id())->get();
        return view('voter.ballot.summary', compact('positions', 'votes'));
    }
}
