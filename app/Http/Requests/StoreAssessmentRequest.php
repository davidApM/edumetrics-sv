<?php

namespace App\Http\Requests;

use App\Models\Assessment;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'period_id' => ['required', 'exists:periods,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'title' => ['required', 'string', 'max:120'],
            'weight_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            'due_date' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $subject = Subject::find($this->subject_id);
            $section = Section::find($this->section_id);

            // 1. Verificar coherencia entre grado de la materia y grado de la sección
            if ($subject && $section && $subject->grade_level_id !== $section->grade_level_id) {
                $validator->errors()->add(
                    'section_id',
                    'La sección seleccionada no pertenece al mismo grado académico de la materia.'
                );
            }

            // 2. Validar titularidad de la materia si es docente (no admin ni coordinador)
            $user = $this->user();
            if ($user->hasRole('docente') && ! $user->hasAnyRole(['admin', 'coordinador'])) {
                if ($subject && $subject->user_id !== $user->id) {
                    $validator->errors()->add(
                        'subject_id',
                        'Solo puedes programar evaluaciones en las materias que tienes asignadas.'
                    );
                }
            }

            // 3. Validar que la ponderación acumulada del período no supere el 100%
            $currentWeight = Assessment::where('subject_id', $this->subject_id)
                ->where('period_id', $this->period_id)
                ->where('section_id', $this->section_id)
                ->sum('weight_percentage');

            $newTotal = round((float) $currentWeight + (float) $this->weight_percentage, 2);

            if ($newTotal > 100.00) {
                $available = max(0, round(100 - (float) $currentWeight, 2));
                $validator->errors()->add(
                    'weight_percentage',
                    "La ponderación total excede el 100%. Ya tienes asignado {$currentWeight}% (disponible: {$available}%)."
                );
            }
        });
    }
}