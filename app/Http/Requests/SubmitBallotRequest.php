<?php

namespace App\Http\Requests;

use App\Models\Candidate;
use App\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SubmitBallotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation(): void
    {
        $electionId = (int) $this->input('election_id');
        if ($electionId <= 0) {
            return;
        }

        $incoming = (array) $this->input('positions', []);

        $expectedIds = Position::query()
            ->whereHas('candidates', function ($q) use ($electionId) {
                $q->where('election_id', $electionId);
            })
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $normalized = [];
        foreach ($expectedIds as $pid) {
            $raw = $incoming[$pid] ?? $incoming[(string) $pid] ?? [];
            $normalized[(string) $pid] = array_values(array_filter(
                (array) $raw,
                static fn ($v) => $v !== '' && $v !== null
            ));
        }

        $this->merge(['positions' => $normalized]);
    }

    public function rules(): array
    {
        return [
            'election_id' => ['required', 'integer', 'exists:elections,id'],
            // positions is an array: [position_id => [candidate_id,...]]
            'positions' => ['required', 'array'],
            'positions.*' => ['array'], // allow empty array = skipped
            'positions.*.*' => ['integer', 'exists:candidates,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $electionId = (int) $this->input('election_id');
            $positions = (array) $this->input('positions', []);

            foreach ($positions as $positionId => $candidateIds) {
                $position = Position::find($positionId);
                if (! $position) {
                    $v->errors()->add("positions.$positionId", 'Invalid position.');

                    continue;
                }

                $candidateIds = array_values(array_filter((array) $candidateIds));

                // Enforce max
                if (count($candidateIds) > $position->maximum_votes) {
                    $v->errors()->add(
                        "positions.$positionId",
                        "You can only select up to {$position->maximum_votes} candidate(s) for {$position->name}."
                    );
                }

                // Ensure each candidate belongs to THIS election + THIS position
                foreach ($candidateIds as $cid) {
                    $ok = Candidate::where('id', $cid)
                        ->where('election_id', $electionId)
                        ->where('position_id', $positionId)
                        ->exists();

                    if (! $ok) {
                        $v->errors()->add("positions.$positionId", 'Invalid candidate for this position/election.');
                        break;
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'positions.required' => 'No selections received. You can skip a position, but the form must be submitted from the receipt screen.',
        ];
    }
}
