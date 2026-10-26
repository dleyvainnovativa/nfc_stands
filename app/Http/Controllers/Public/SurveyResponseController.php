<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Stand;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Support\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Public survey: GET /s/{code} renders, POST /s/{code} records a response.
 * Reached directly or via a stand (?via={standCode}) which attributes the
 * response to that stand.
 */
class SurveyResponseController extends Controller
{
    public function show(Request $request, string $code)
    {
        $survey = Survey::withoutTenantScope()->where('code', $code)->first();

        if (! $survey || ! $survey->isActive()) {
            return response()->view('public.survey_closed', [], $survey ? 200 : 404);
        }

        $survey->load('questions');
        $via = $request->query('via');
        $brand = $survey->tenant;

        return view('public.survey', compact('survey', 'via', 'brand'));
    }

    public function store(Request $request, string $code)
    {
        $survey = Survey::withoutTenantScope()->where('code', $code)->first();

        if (! $survey || ! $survey->isActive()) {
            return response()->view('public.survey_closed', [], 200);
        }

        $questions = $survey->questions()->get();

        $contact = [];
        if ($survey->collect_contact) {
            $contact = $request->validate([
                'contact_name'  => ['required', 'string', 'max:160'],
                'contact_phone' => ['nullable', 'string', 'max:40'],
                'contact_email' => ['nullable', 'email', 'max:190'],
            ]);
        }

        $validated = $this->validateAnswers($request, $questions);

        // Resolve originating stand (attribution), if the link carried one.
        $standId = null;
        if ($via = $request->input('via')) {
            $stand = Stand::withoutTenantScope()->where('code', $via)->first();
            if ($stand && $stand->tenant_id === $survey->tenant_id) {
                $standId = $stand->id;
            }
        }

        DB::transaction(function () use ($survey, $questions, $validated, $standId, $request) {
            $response = SurveyResponse::create([
                'tenant_id' => $survey->tenant_id,
                'survey_id' => $survey->id,
                'stand_id' => $standId,
                'submitted_at' => Carbon::now(),
                'device' => Device::detect($request->userAgent()),
                'ip_hash' => Device::hashIp($request->ip()),
                'contact_name' => $contact['contact_name'] ?? null,
                'contact_phone' => $contact['contact_phone'] ?? null,
                'contact_email' => $contact['contact_email'] ?? null,
            ]);

            foreach ($questions as $q) {
                $answer = $validated[$q->id] ?? null;
                if ($answer === null) {
                    continue; // optional + unanswered
                }

                SurveyAnswer::create([
                    'tenant_id' => $survey->tenant_id,
                    'survey_response_id' => $response->id,
                    'survey_question_id' => $q->id,
                    'value_text' => $answer['text'] ?? null,
                    'value_number' => $answer['number'] ?? null,
                    'value_options' => $answer['options'] ?? null,
                ]);
            }

            $survey->increment('responses_count');
        });

        return view('public.survey_thanks', ['survey' => $survey, 'brand' => $survey->tenant]);
    }

    /**
     * Build + run validation dynamically, returning a normalized answer map
     * keyed by question id: ['text'=>..]|['number'=>..]|['options'=>[..]].
     *
     * @return array<int, array>
     */
    private function validateAnswers(Request $request, $questions): array
    {
        $input = $request->input('answers', []);
        $errors = [];
        $out = [];

        foreach ($questions as $q) {
            $val = $input[$q->id] ?? null;
            $given = is_array($val) ? array_filter($val, fn ($v) => $v !== null && $v !== '') : (($val === '' ) ? null : $val);
            $empty = $given === null || (is_array($given) && count($given) === 0);

            if ($empty) {
                if ($q->required) {
                    $errors["answers.{$q->id}"] = 'Esta pregunta es obligatoria.';
                }
                continue;
            }

            switch ($q->type) {
                case SurveyQuestion::TYPE_SINGLE:
                    if (! in_array($given, $q->options ?? [], true)) {
                        $errors["answers.{$q->id}"] = 'Selección no válida.';
                    } else {
                        $out[$q->id] = ['options' => [$given]];
                    }
                    break;

                case SurveyQuestion::TYPE_MULTI:
                    $sel = array_values(array_intersect((array) $given, $q->options ?? []));
                    if (count($sel) === 0) {
                        if ($q->required) $errors["answers.{$q->id}"] = 'Selecciona al menos una opción.';
                    } else {
                        $out[$q->id] = ['options' => $sel];
                    }
                    break;

                case SurveyQuestion::TYPE_RATING:
                    $n = (int) $given; $max = $q->ratingMax();
                    if ($n < 1 || $n > $max) {
                        $errors["answers.{$q->id}"] = "Elige 1–{$max}.";
                    } else {
                        $out[$q->id] = ['number' => $n];
                    }
                    break;

                case SurveyQuestion::TYPE_NPS:
                    $n = (int) $given;
                    if ($n < 0 || $n > 10) {
                        $errors["answers.{$q->id}"] = 'Elige 0–10.';
                    } else {
                        $out[$q->id] = ['number' => $n];
                    }
                    break;

                case SurveyQuestion::TYPE_TEXT:
                    $out[$q->id] = ['text' => mb_substr((string) $given, 0, 2000)];
                    break;
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $out;
    }
}
