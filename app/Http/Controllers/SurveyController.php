<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Services\SurveyStats;
use App\Support\CurrentTenant;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SurveyController extends Controller
{
    public function index()
    {
        $surveys = Survey::withCount('questions')
            ->orderByDesc('id')
            ->paginate(20);

        return view('surveys.index', compact('surveys'));
    }

    public function create()
    {
        $this->guardTenantContext();
        $survey = new Survey(['status' => Survey::STATUS_DRAFT]);
        return view('surveys.create', compact('survey'));
    }

    public function store(Request $request)
    {
        $this->guardTenantContext();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in([Survey::STATUS_DRAFT, Survey::STATUS_ACTIVE, Survey::STATUS_CLOSED])],
            'thank_you_message' => ['nullable', 'string', 'max:300'],
            'return_url' => ['nullable', 'url', 'max:2048'],
            'return_label' => ['nullable', 'string', 'max:60'],
        ]);
        $data['paginated'] = $request->boolean('paginated');
        $data['collect_contact'] = $request->boolean('collect_contact');

        $survey = Survey::create($data); // code + tenant_id auto-fill

        return redirect()->route('surveys.edit', $survey)
            ->with('flash.success', 'Encuesta creada: agrega tus preguntas.');
    }

    public function edit(Survey $survey)
    {
        $survey->load('questions');
        return view('surveys.edit', compact('survey'));
    }

    public function update(Request $request, Survey $survey)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in([Survey::STATUS_DRAFT, Survey::STATUS_ACTIVE, Survey::STATUS_CLOSED])],
            'thank_you_message' => ['nullable', 'string', 'max:300'],
            'return_url' => ['nullable', 'url', 'max:2048'],
            'return_label' => ['nullable', 'string', 'max:60'],
        ]);
        $data['paginated'] = $request->boolean('paginated');
        $data['collect_contact'] = $request->boolean('collect_contact');

        $questions = $this->validateQuestions($request);

        DB::transaction(function () use ($survey, $data, $questions) {
            $survey->update($data);
            $this->syncQuestions($survey, $questions);
        });

        return redirect()->route('surveys.edit', $survey)
            ->with('flash.success', 'Encuesta guardada.');
    }

    public function show(Survey $survey, SurveyStats $stats)
    {
        $survey->loadCount('responses');
        $results = $stats->forSurvey($survey);

        $contacts = $survey->collect_contact
            ? $survey->responses()
                ->whereNotNull('contact_name')
                ->orderByDesc('submitted_at')
                ->limit(200)
                ->get(['contact_name', 'contact_phone', 'contact_email', 'submitted_at'])
            : collect();

        return view('surveys.show', compact('survey', 'results', 'contacts'));
    }

    public function destroy(Survey $survey)
    {
        $survey->delete();
        return redirect()->route('surveys.index')
            ->with('flash.success', 'Encuesta eliminada.');
    }

    /* ----------------------------------------------------------------- */

    /** Normalize + validate the posted question rows. @return array<int,array> */
    private function validateQuestions(Request $request): array
    {
        $raw = $request->input('q', []);
        if (! is_array($raw) || count($raw) === 0) {
            throw new HttpResponseException(
                back()->withInput()->with('flash.error', 'Agrega al menos una pregunta.')
            );
        }

        $types = array_keys(SurveyQuestion::typeOptions());
        $clean = [];
        $errors = [];

        foreach (array_values($raw) as $i => $row) {
            $type = $row['type'] ?? null;
            $prompt = trim((string) ($row['prompt'] ?? ''));

            if (! in_array($type, $types, true)) { $errors[] = 'A question has an invalid type.'; continue; }
            if ($prompt === '') { $errors[] = 'Cada pregunta necesita un enunciado.'; continue; }

            $options = null;
            $config = null;

            if (in_array($type, [SurveyQuestion::TYPE_SINGLE, SurveyQuestion::TYPE_MULTI], true)) {
                $options = collect(preg_split('/\r\n|\r|\n/', (string) ($row['options'] ?? '')))
                    ->map(fn ($o) => trim($o))->filter()->values()->all();
                if (count($options) < 2) {
                    $errors[] = "\"{$prompt}\" necesita al menos dos opciones.";
                    continue;
                }
            }

            if ($type === SurveyQuestion::TYPE_RATING) {
                $maxVal = (int) ($row['rating_max'] ?? 5);
                $maxVal = max(2, min(10, $maxVal));
                $config = ['max' => $maxVal];
            }

            $clean[] = [
                'id' => isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null,
                'type' => $type,
                'prompt' => mb_substr($prompt, 0, 255),
                'options' => $options,
                'config' => $config,
                'required' => ! empty($row['required']),
                'position' => $i,
            ];
        }

        if ($errors) {
            throw new HttpResponseException(
                back()->withInput()->with('flash.error', $errors[0])
            );
        }

        return $clean;
    }

    /** Upsert questions by id, delete the ones no longer present. */
    private function syncQuestions(Survey $survey, array $questions): void
    {
        $existingIds = $survey->questions()->pluck('id')->all();
        $keptIds = [];

        foreach ($questions as $q) {
            if ($q['id'] && in_array($q['id'], $existingIds, true)) {
                $model = SurveyQuestion::find($q['id']);
            } else {
                $model = new SurveyQuestion();
                $model->survey_id = $survey->id;
                // tenant_id auto-fills via HasTenant on create
            }
            $model->type = $q['type'];
            $model->prompt = $q['prompt'];
            $model->options = $q['options'];
            $model->config = $q['config'];
            $model->required = $q['required'];
            $model->position = $q['position'];
            $model->save();

            $keptIds[] = $model->id;
        }

        // Remove deleted questions (their answers cascade).
        $toDelete = array_diff($existingIds, $keptIds);
        if ($toDelete) {
            SurveyQuestion::whereIn('id', $toDelete)->delete();
        }
    }

    private function guardTenantContext(): void
    {
        if (CurrentTenant::id() === null) {
            throw new HttpResponseException(
                redirect()->route('surveys.index')->with(
                    'flash.warning',
                    'Primero selecciona un cliente (usa "Ver como" en un cliente) para crear una encuesta.'
                )
            );
        }
    }
}
