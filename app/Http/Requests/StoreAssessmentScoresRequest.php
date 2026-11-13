<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAssessmentScoresRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $assessment = $this->route('assessment');

        if (! $user || ! $assessment) {
            return false;
        }

        if ($user->hasRole('docente') && ! $user->hasAnyRole(['admin', 'coordinador'])) {
            return $assessment->subject->user_id === $user->id;
        }

        return $user->hasAnyRole(['admin', 'coordinador']);
    }

    public function rules(): array
    {
        return [
            'scores' => ['required', 'array'],
            'scores.*' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'observations' => ['nullable', 'array'],
            'observations.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'scores.*.numeric' => 'La calificación debe ser un número válido.',
            'scores.*.min' => 'La nota mínima permitida es 0.00.',
            'scores.*.max' => 'La nota máxima permitida es 10.00.',
            'observations.*.max' => 'La observación no puede superar los 255 caracteres.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $assessment = $this->route('assessment');
            $submittedIds = array_keys($this->input('scores', []));

            if (empty($submittedIds)) {
                return;
            }

            $validStudentsCount = Student::where('section_id', $assessment->section_id)
                ->where('is_active', true)
                ->whereIn('id', $submittedIds)
                ->count();

            if ($validStudentsCount !== count($submittedIds)) {
                $validator->errors()->add(
                    'scores',
                    'Uno o más estudiantes enviados no pertenecen a esta sección o están inactivos.'
                );
            }
        });
    }
}