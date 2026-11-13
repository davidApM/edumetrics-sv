<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssessmentScoresRequest;
use App\Models\Assessment;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssessmentScoreController extends Controller
{
    public function edit(Request $request, Assessment $assessment): View
    {
        $user = $request->user();

        if ($user->hasRole('docente') && ! $user->hasAnyRole(['admin', 'coordinador'])) {
            abort_if($assessment->subject->user_id !== $user->id, 403);
        }

        $assessment->load(['subject.gradeLevel', 'section', 'period']);

        $students = Student::where('section_id', $assessment->section_id)
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $existingScores = $assessment->scores()
            ->get()
            ->keyBy('student_id');

        return view('assessments.scores', compact('assessment', 'students', 'existingScores'));
    }

    public function update(StoreAssessmentScoresRequest $request, Assessment $assessment): RedirectResponse
    {
        $scores = $request->validated('scores', []);
        $observations = $request->validated('observations', []);
        $recorderId = $request->user()->id;

        DB::transaction(function () use ($assessment, $scores, $observations, $recorderId) {
            foreach ($scores as $studentId => $scoreValue) {
                if ($scoreValue !== null && $scoreValue !== '') {
                    StudentScore::updateOrCreate(
                        [
                            'assessment_id' => $assessment->id,
                            'student_id' => $studentId,
                        ],
                        [
                            'score' => $scoreValue,
                            'observations' => $observations[$studentId] ?? null,
                            'recorded_by' => $recorderId,
                        ]
                    );
                } else {
                    // Si el docente borra la nota de una celda previamente guardada, se elimina el registro
                    StudentScore::where('assessment_id', $assessment->id)
                        ->where('student_id', $studentId)
                        ->delete();
                }
            }
        });

        return redirect()
            ->route('assessments.index')
            ->with('success', "Calificaciones guardadas correctamente para «{$assessment->title}».");
    }
}