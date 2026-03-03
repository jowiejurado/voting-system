<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitBallotRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BallotController extends Controller
{
	private const TIMEZONE = 'Asia/Manila';
	private const COUNTDOWN_DAYS_MAX = 10;

	/**
	 * Parse election start datetime (start_date + start_time), normalized to second precision.
	 */
	private static function parseStartAt(Election $election): ?Carbon
	{
		try {
			return Carbon::parse(
				(string) $election->start_date . ' ' . (string) $election->start_time,
				self::TIMEZONE
			)->startOfSecond();
		} catch (\Throwable $e) {
			return null;
		}
	}

	/**
	 * Parse election end datetime (end_date ?? start_date + end_time), normalized to second precision.
	 */
	private static function parseEndAt(Election $election): ?Carbon
	{
		try {
			$endDate = $election->end_date
				? (string) $election->end_date
				: (string) $election->start_date;
			return Carbon::parse($endDate . ' ' . (string) $election->end_time, self::TIMEZONE)->startOfSecond();
		} catch (\Throwable $e) {
			return null;
		}
	}

	/**
	 * Current time in election timezone, normalized to second precision for strict comparison.
	 */
	private static function nowStrict(): Carbon
	{
		return Carbon::now(self::TIMEZONE)->startOfSecond();
	}

	/**
	 * Find the currently active election.
	 * Strict: ballot shown only when now >= startAt AND now < endAt (end is exclusive).
	 */
	private function findActiveElection(Carbon $now): ?Election
	{
		$all = Election::query()->get();
		foreach ($all as $election) {
			$startAt = self::parseStartAt($election);
			$endAt = self::parseEndAt($election);
			if ($startAt && $endAt && $now->gte($startAt) && $now->lt($endAt)) {
				return $election;
			}
		}
		return null;
	}

	/**
	 * Find the next upcoming election (start datetime strictly in the future).
	 * Countdown is shown only when the start date is within COUNTDOWN_DAYS_MAX calendar days from today
	 * (e.g. today March 2, start March 12 → show countdown; start March 13 → show "no active election").
	 */
	private function findNextUpcomingElection(Carbon $now): ?array
	{
		$all = Election::query()->get();
		$candidates = [];
		foreach ($all as $election) {
			$startAt = self::parseStartAt($election);
			$endAt = self::parseEndAt($election);
			if (!$startAt || !$endAt || $now->gte($endAt)) {
				continue;
			}
			if ($startAt->gt($now)) {
				$candidates[] = ['election' => $election, 'startAt' => $startAt, 'endAt' => $endAt];
			}
		}
		usort($candidates, fn ($a, $b) => $a['startAt']->getTimestamp() <=> $b['startAt']->getTimestamp());
		$next = $candidates[0] ?? null;
		if (!$next) {
			return null;
		}
		// Use calendar days: start date within 10 days from today → countdown; otherwise no active election
		$todayStart = $now->copy()->startOfDay();
		$startDateOnly = $next['startAt']->copy()->startOfDay();
		$daysUntilStart = (int) $todayStart->diffInDays($startDateOnly, false);
		if ($daysUntilStart > self::COUNTDOWN_DAYS_MAX) {
			return null;
		}
		return $next;
	}

	public function showBallot(Request $request)
	{
		$now = self::nowStrict();

		$election = $this->findActiveElection($now);

		if ($election) {
			$user = Auth::user();
			$alreadyVoted = Vote::where('election_id', $election->id)
				->where('user_id', $user->id)
				->exists();

			if ($alreadyVoted) {
				return view('voter.ballot_already_voted', compact('election'));
			}

			$hasCandidates = Candidate::where('election_id', $election->id)->exists();
			if (!$hasCandidates) {
				return view('voter.no_candidates');
			}

			$positions = Position::query()
				->whereHas('candidates', function ($q) use ($election) {
					$q->where('election_id', $election->id);
				})
				->with(['candidates' => function ($q) use ($election) {
					$q->where('election_id', $election->id)
						->orderBy('last_name')
						->orderBy('first_name');
				}])
				->orderBy('id')
				->get();

			$positionsPayload = $positions->map(function ($p) {
				return [
					'id'   => $p->id,
					'name' => $p->name,
					'max'  => $p->maximum_votes,
					'candidates' => $p->candidates->map(function ($c) {
						return [
							'id'   => $c->id,
							'name' => trim($c->last_name . ', ' . $c->first_name),
							'org'  => $c->organization_name,
						];
					})->values()->all(),
				];
			})->values()->all();

			$endAt = self::parseEndAt($election);
			$votingEndsAtTimestampMs = $endAt ? $endAt->getTimestampMs() : null;

			return view('voter.ballot', compact('election', 'positions', 'positionsPayload', 'votingEndsAtTimestampMs'));
		}

		$next = $this->findNextUpcomingElection($now);

		if (!$next) {
			return view('voter.ballot_closed');
		}

		$startAt = $next['startAt'];
		$endAt = $next['endAt'];
		$nextElection = $next['election'];

		$startTimestampMs = $startAt->getTimestampMs();
		$endTimestampMs = $endAt->getTimestampMs();
		$endAtFormatted = $endAt->format('F j, Y g:i A');

		return view('voter.ballot_countdown', [
			'election'         => $nextElection,
			'startTimestampMs' => $startTimestampMs,
			'endTimestampMs'  => $endTimestampMs,
			'endAtFormatted'   => $endAtFormatted,
		]);
	}

	public function submit(SubmitBallotRequest $request)
	{
		$user = Auth::user();

		$election = Election::findOrFail($request->input('election_id'));

		// Re-check election window: strict (start inclusive, end exclusive)
		$now = self::nowStrict();
		$startAt = self::parseStartAt($election);
		$endAt = self::parseEndAt($election);

		if (!$startAt || !$endAt || $now->lt($startAt) || $now->gte($endAt)) {
			return back()->withErrors(['election' => 'This election is not currently accepting votes.'])->withInput();
		}

		// Optional: block double-voting (one-shot policy)
		$alreadyVoted = Vote::where('election_id', $election->id)
			->where('user_id', $user->id)
			->exists();

		if ($alreadyVoted) {
			return redirect()->route('voter.ballot')->withErrors(['vote' => 'You have already cast your vote for this election.']);
		}

		$positionsPayload = $request->input('positions', []); // [position_id => [candidate_id, ...]]

		DB::transaction(function () use ($positionsPayload, $election, $user) {
			foreach ($positionsPayload as $positionId => $candidateIds) {
				$candidateIds = array_filter((array) $candidateIds); // allow skip => empty
				foreach ($candidateIds as $cid) {
					Vote::create([
						'election_id'  => $election->id,
						'position_id'  => $positionId,
						'candidate_id' => $cid,
						'user_id'      => $user->id,
					]);
				}
			}
		});

		$userModel = User::find($user->id);
		$userModel->forceFill(['has_voted' => true])->save();

		return redirect()
			->route('voter.ballot')
			->with(['success' => 'Your vote has been submitted. Thank you!']);
	}
}
