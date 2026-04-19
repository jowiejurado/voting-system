<?php

namespace App\Models;

use App\Models\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Election extends Model
{
    use HasEncryptedAttributes, HasFactory;

    /** Same timezone as voter ballot (BallotController). */
    public const SCHEDULE_TIMEZONE = 'Asia/Manila';

    /** Start date must fall within this many calendar days from “today” to show countdown (BallotController). */
    public const BALLOT_COUNTDOWN_DAYS_MAX = 10;

    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'status',
    ];

    protected array $encryptable = [
        'title',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'status',
    ];

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public static function nowForSchedule(): Carbon
    {
        return Carbon::now(self::SCHEDULE_TIMEZONE)->startOfSecond();
    }

    public static function parseScheduleStart(self $election): ?Carbon
    {
        try {
            return Carbon::parse(
                (string) $election->start_date.' '.(string) $election->start_time,
                self::SCHEDULE_TIMEZONE
            )->startOfSecond();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function parseScheduleEnd(self $election): ?Carbon
    {
        try {
            $endDate = $election->end_date
                ? (string) $election->end_date
                : (string) $election->start_date;

            return Carbon::parse($endDate.' '.(string) $election->end_time, self::SCHEDULE_TIMEZONE)->startOfSecond();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Voting window open: now >= start and now < end (end exclusive), same as BallotController.
     */
    public static function findActiveForBallot(?Carbon $now = null): ?self
    {
        $now = $now ?? self::nowForSchedule();

        foreach (static::query()->get() as $election) {
            $startAt = self::parseScheduleStart($election);
            $endAt = self::parseScheduleEnd($election);
            if ($startAt && $endAt && $now->gte($startAt) && $now->lt($endAt)) {
                return $election;
            }
        }

        return null;
    }

    /**
     * Next election with future start, not yet ended, and start date within BALLOT_COUNTDOWN_DAYS_MAX calendar days.
     *
     * @return array{election: self, startAt: Carbon, endAt: Carbon}|null
     */
    public static function resolveBallotCountdownContext(?Carbon $now = null): ?array
    {
        $now = $now ?? self::nowForSchedule();
        $candidates = [];

        foreach (static::query()->get() as $election) {
            $startAt = self::parseScheduleStart($election);
            $endAt = self::parseScheduleEnd($election);
            if (! $startAt || ! $endAt || $now->gte($endAt)) {
                continue;
            }
            if ($startAt->gt($now)) {
                $candidates[] = ['election' => $election, 'startAt' => $startAt, 'endAt' => $endAt];
            }
        }

        usort($candidates, fn ($a, $b) => $a['startAt']->getTimestamp() <=> $b['startAt']->getTimestamp());
        $next = $candidates[0] ?? null;

        if (! $next) {
            return null;
        }

        $todayStart = $now->copy()->startOfDay();
        $startDateOnly = $next['startAt']->copy()->startOfDay();
        $daysUntilStart = (int) $todayStart->diffInDays($startDateOnly, false);

        if ($daysUntilStart > self::BALLOT_COUNTDOWN_DAYS_MAX) {
            return null;
        }

        return $next;
    }

    /**
     * Next election the ballot countdown would show (future start, within BALLOT_COUNTDOWN_DAYS_MAX days).
     * Does not include the ongoing/active voting election.
     */
    public static function resolveUpcomingCountdownElection(?Carbon $now = null): ?self
    {
        $ctx = self::resolveBallotCountdownContext($now);

        return $ctx['election'] ?? null;
    }

    /**
     * Recompute and persist each election's status from schedule (Asia/Manila).
     * Required because date/status columns are encrypted and cannot be filtered reliably in SQL.
     */
    public static function syncComputedStatuses(): void
    {
        $now = now('Asia/Manila');

        foreach (static::query()->get() as $item) {
            $previousStatus = (string) $item->status;
            $newStatus = $item->status;
            $today = $now->toDateString();
            $startDate = Carbon::parse((string) $item->start_date, 'Asia/Manila')->toDateString();
            $endDate = $item->end_date
                ? Carbon::parse((string) $item->end_date, 'Asia/Manila')->toDateString()
                : $startDate;

            if ($endDate < $today) {
                $newStatus = 'completed';
            } elseif ($startDate > $today) {
                $newStatus = 'pending';
            } else {
                try {
                    $startAt = Carbon::parse($startDate.' '.(string) $item->start_time, 'Asia/Manila');
                    $endAt = Carbon::parse($endDate.' '.(string) $item->end_time, 'Asia/Manila');

                    if ($now->lt($startAt)) {
                        $newStatus = 'pending';
                    } elseif ($now->gt($endAt)) {
                        $newStatus = 'completed';
                    } else {
                        $newStatus = 'current';
                    }
                } catch (\Throwable $e) {
                    $newStatus = 'completed';
                }
            }

            if ($newStatus !== $item->status) {
                $item->status = $newStatus;
                $item->save();

                if ($newStatus === 'completed' && $previousStatus !== 'completed') {
                    User::query()->where('type', 'voter')->update(['has_voted' => false]);
                }
            }
        }
    }
}
