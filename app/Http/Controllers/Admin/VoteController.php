<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class VoteController extends Controller
{
    public function index(Request $request)
    {
        Election::syncComputedStatuses();

        $activeElection = Election::findActiveForBallot(Election::nowForSchedule());

        $q = trim($request->get('q', ''));
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

        $with = [
            'position',
            'candidate.organization',
            'election',
        ];

        $votesBase = Vote::query()
            ->with($with)
            ->latest();

        if ($activeElection) {
            $votesBase->where('election_id', $activeElection->id);
        } else {
            $votesBase->whereRaw('0 = 1');
        }

        if ($q === '') {
            $votes = $votesBase
                ->paginate($perPage)
                ->withQueryString();
        } else {
            $filtered = $votesBase
                ->get()
                ->filter(function (Vote $vote) use ($q) {
                    if (mb_stripos((string) ($vote->position->name ?? ''), $q) !== false) {
                        return true;
                    }
                    $c = $vote->candidate;
                    if ($c) {
                        if (mb_stripos((string) $c->first_name, $q) !== false) {
                            return true;
                        }
                        if (mb_stripos((string) $c->last_name, $q) !== false) {
                            return true;
                        }
                        if (mb_stripos(trim((string) $c->first_name . ' ' . (string) $c->last_name), $q) !== false) {
                            return true;
                        }
                    }
                    if (mb_stripos((string) ($c?->organization?->name ?? ''), $q) !== false) {
                        return true;
                    }

                    return false;
                })
                ->values();

            $page = max(1, (int) $request->get('page', 1));
            $votes = new LengthAwarePaginator(
                $filtered->slice(($page - 1) * $perPage, $perPage)->values(),
                $filtered->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        return view('admin.votes.index', compact('votes', 'q', 'perPage', 'activeElection'));
    }

    public function voterStatus(Request $request)
    {
        Election::syncComputedStatuses();

        $q = trim($request->get('q', ''));
        $perPage = (int) $request->get('per_page', 9);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 9;

        $filter = $request->get('filter', $request->get('sort', ''));

        $baseVotersQuery = function () use ($filter) {
            return User::query()
                ->where('type', 'voter')
                ->when($filter === 'voted', fn ($query) => $query->where('has_voted', true))
                ->when($filter === 'undone', fn ($query) => $query->where('has_voted', false))
                ->orderByDesc('created_at');
        };

        if ($q === '') {
            $voters = $baseVotersQuery()
                ->simplePaginate($perPage)
                ->withQueryString();
        } else {
            $filtered = $baseVotersQuery()
                ->get()
                ->filter(function (User $voter) use ($q) {
                    if (mb_stripos((string) $voter->first_name, $q) !== false) {
                        return true;
                    }
                    if (mb_stripos((string) $voter->last_name, $q) !== false) {
                        return true;
                    }
                    if (mb_stripos(trim((string) $voter->first_name . ' ' . (string) $voter->last_name), $q) !== false) {
                        return true;
                    }
                    if (mb_stripos((string) $voter->organization_name, $q) !== false) {
                        return true;
                    }
                    if (mb_stripos((string) $voter->email, $q) !== false) {
                        return true;
                    }

                    return false;
                })
                ->values();

            $page = max(1, (int) $request->get('page', 1));
            $voters = new LengthAwarePaginator(
                $filtered->slice(($page - 1) * $perPage, $perPage)->values(),
                $filtered->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        return view('admin.voter-status.index', compact('voters', 'q', 'perPage', 'filter'));
    }
}
