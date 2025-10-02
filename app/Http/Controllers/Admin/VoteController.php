<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Position;
use App\Models\Vote;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function index()
    {
        $election = Election::first();
        $positions = Position::when($election, fn($q)=>$q->where('election_id', $election->id))
            ->orderBy('order_index')->get();
        $tally = [];
        foreach ($positions as $p) {
            $tally[$p->name] = Vote::where('position_id', $p->id)->count();
        }
        return view('admin.votes.index', compact('positions','tally','election'));
    }

    public function ballot()
    {
        $election = Election::first();
        $positions = Position::with('candidates')->when($election, fn($q)=>$q->where('election_id', $election->id))
            ->orderBy('order_index')->get();
        return view('admin.votes.ballot', compact('positions','election'));
    }
}
