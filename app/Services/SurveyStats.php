<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;

/**
 * Computes per-question aggregates for a survey's results screen.
 * Pure aggregation over survey_answers — no precompute table (fine for the
 * response volumes a restaurant survey produces; revisit if it grows).
 */
class SurveyStats
{
    /** @return array<int, array> keyed by question id */
    public function forSurvey(Survey $survey): array
    {
        $questions = $survey->questions()->get();
        $out = [];

        foreach ($questions as $q) {
            $answers = SurveyAnswer::where('survey_question_id', $q->id)->get();
            $out[$q->id] = [
                'question' => $q,
                'answered' => $answers->count(),
                'data' => $this->aggregate($q, $answers),
            ];
        }

        return $out;
    }

    private function aggregate(SurveyQuestion $q, $answers): array
    {
        return match ($q->type) {
            SurveyQuestion::TYPE_SINGLE,
            SurveyQuestion::TYPE_MULTI  => $this->choice($q, $answers),
            SurveyQuestion::TYPE_RATING => $this->rating($q, $answers),
            SurveyQuestion::TYPE_NPS    => $this->nps($answers),
            SurveyQuestion::TYPE_TEXT   => $this->text($answers),
            default => [],
        };
    }

    /** Count per defined option (plus total selections for % base). */
    private function choice(SurveyQuestion $q, $answers): array
    {
        $options = $q->options ?? [];
        $counts = array_fill_keys($options, 0);
        $totalSelections = 0;

        foreach ($answers as $a) {
            foreach (($a->value_options ?? []) as $sel) {
                if (! array_key_exists($sel, $counts)) {
                    $counts[$sel] = 0; // tolerate legacy/removed options
                }
                $counts[$sel]++;
                $totalSelections++;
            }
        }

        // % is of respondents who answered this question (single) — use answer
        // count as the base so single-choice bars sum to ~100%.
        $base = max(1, $answers->count());
        $rows = [];
        foreach ($counts as $label => $count) {
            $rows[] = [
                'label' => $label,
                'count' => $count,
                'pct' => round($count / $base * 100),
            ];
        }

        return ['rows' => $rows, 'total_selections' => $totalSelections];
    }

    /** Average + distribution across 1..max. */
    private function rating(SurveyQuestion $q, $answers): array
    {
        $max = $q->ratingMax();
        $dist = array_fill(1, $max, 0);
        $sum = 0; $n = 0;

        foreach ($answers as $a) {
            $v = (int) $a->value_number;
            if ($v >= 1 && $v <= $max) {
                $dist[$v]++; $sum += $v; $n++;
            }
        }

        $rows = [];
        for ($v = $max; $v >= 1; $v--) {
            $rows[] = [
                'label' => (string) $v,
                'count' => $dist[$v],
                'pct' => round($dist[$v] / max(1, $n) * 100),
            ];
        }

        return [
            'average' => $n ? round($sum / $n, 2) : null,
            'count' => $n,
            'max' => $max,
            'rows' => $rows,
        ];
    }

    /**
     * Net Promoter Score.
     * Promoters 9–10, Passives 7–8, Detractors 0–6.
     * NPS = (%promoters − %detractors), rounded, range −100..100.
     */
    private function nps($answers): array
    {
        $promoters = $passives = $detractors = 0; $n = 0;

        foreach ($answers as $a) {
            $v = $a->value_number;
            if ($v === null || $v < 0 || $v > 10) {
                continue;
            }
            $n++;
            if ($v >= 9)      $promoters++;
            elseif ($v >= 7)  $passives++;
            else              $detractors++;
        }

        $score = $n ? round(($promoters - $detractors) / $n * 100) : null;

        return [
            'score' => $score,
            'count' => $n,
            'promoters' => $promoters,
            'passives' => $passives,
            'detractors' => $detractors,
            'promoters_pct'  => $n ? round($promoters / $n * 100) : 0,
            'passives_pct'   => $n ? round($passives / $n * 100) : 0,
            'detractors_pct' => $n ? round($detractors / $n * 100) : 0,
        ];
    }

    private function text($answers): array
    {
        $values = $answers
            ->filter(fn ($a) => filled($a->value_text))
            ->sortByDesc('id')
            ->take(50)
            ->map(fn ($a) => $a->value_text)
            ->values()
            ->all();

        return ['count' => count($values), 'recent' => $values];
    }
}
