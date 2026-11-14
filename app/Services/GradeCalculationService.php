<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Period;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;

class GradeCalculationService
{
    public const PASSING_GRADE = 6.00;

    /**
     * Genera la matriz de calificaciones y métricas de una materia y sección para un período específico.
     */
    public function buildPeriodMatrix(Subject $subject, Section $section, Period $period): array
    {
        $students = Student::where('section_id', $section->id)
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $assessments = Assessment::with('scores')
            ->where('subject_id', $subject->id)
            ->where('section_id', $section->id)
            ->where('period_id', $period->id)
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $plannedWeight = (float) $assessments->sum('weight_percentage');
        $periodFactor = ((float) $period->weight_percentage) / 100;

        // Indexar notas como [student_id][assessment_id] => score
        $scoresMap = [];
        foreach ($assessments as $assessment) {
            foreach ($assessment->scores as $scoreRecord) {
                $scoresMap[$scoreRecord->student_id][$assessment->id] = (float) $scoreRecord->score;
            }
        }

        $rows = $students->map(function (Student $student) use ($assessments, $scoresMap, $periodFactor) {
            $cellScores = [];
            $accumulatedGrade = 0.0;
            $evaluatedWeight = 0.0;

            foreach ($assessments as $assessment) {
                $rawScore = $scoresMap[$student->id][$assessment->id] ?? null;
                $weight = (float) $assessment->weight_percentage;
                $weightedPoints = null;

                if ($rawScore !== null) {
                    $weightedPoints = $rawScore * ($weight / 100);
                    $accumulatedGrade += $weightedPoints;
                    $evaluatedWeight += $weight;
                }

                $cellScores[$assessment->id] = [
                    'raw' => $rawScore,
                    'weighted' => $weightedPoints !== null ? round($weightedPoints, 2) : null,
                ];
            }

            $finalPeriodGrade = round($accumulatedGrade, 2);
            $projectedGrade = $evaluatedWeight > 0
                ? round($accumulatedGrade / ($evaluatedWeight / 100), 2)
                : null;
            $annualContribution = round($finalPeriodGrade * $periodFactor, 2);
            $passed = $finalPeriodGrade >= self::PASSING_GRADE;

            return [
                'student' => $student,
                'scores' => $cellScores,
                'evaluated_weight' => round($evaluatedWeight, 2),
                'final_grade' => $finalPeriodGrade,
                'projected_grade' => $projectedGrade,
                'annual_contribution' => $annualContribution,
                'passed' => $passed,
            ];
        });

        return [
            'mode' => 'period',
            'assessments' => $assessments,
            'rows' => $rows,
            'summary' => $this->buildSummary($rows, $plannedWeight),
        ];
    }

    /**
     * Genera el cuadro consolidado anual cruzando todos los períodos de la materia y sección.
     */
    public function buildAnnualMatrix(Subject $subject, Section $section, Collection $periods): array
    {
        $students = Student::where('section_id', $section->id)
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $assessmentsByPeriod = Assessment::with('scores')
            ->where('subject_id', $subject->id)
            ->where('section_id', $section->id)
            ->whereIn('period_id', $periods->pluck('id'))
            ->get()
            ->groupBy('period_id');

        $rows = $students->map(function (Student $student) use ($periods, $assessmentsByPeriod) {
            $periodGrades = [];
            $annualGrade = 0.0;

            foreach ($periods as $period) {
                $periodAssessments = $assessmentsByPeriod->get($period->id, collect());
                $periodAccumulated = 0.0;

                foreach ($periodAssessments as $assessment) {
                    $scoreRecord = $assessment->scores->firstWhere('student_id', $student->id);
                    if ($scoreRecord !== null) {
                        $periodAccumulated += ((float) $scoreRecord->score) * (((float) $assessment->weight_percentage) / 100);
                    }
                }

                $roundedPeriodGrade = round($periodAccumulated, 2);
                $contribution = $roundedPeriodGrade * (((float) $period->weight_percentage) / 100);
                $annualGrade += $contribution;

                $periodGrades[$period->id] = [
                    'grade' => $roundedPeriodGrade,
                    'contribution' => round($contribution, 2),
                ];
            }

            $finalAnnualGrade = round($annualGrade, 2);

            return [
                'student' => $student,
                'periods' => $periodGrades,
                'final_grade' => $finalAnnualGrade,
                'passed' => $finalAnnualGrade >= self::PASSING_GRADE,
            ];
        });

        $totalAnnualWeight = (float) $periods->sum('weight_percentage');

        return [
            'mode' => 'annual',
            'periods' => $periods,
            'rows' => $rows,
            'summary' => $this->buildSummary($rows, $totalAnnualWeight),
        ];
    }

    private function buildSummary(Collection $rows, float $plannedWeight): array
    {
        $totalStudents = $rows->count();
        $approvedCount = $rows->where('passed', true)->count();
        $failedCount = $totalStudents - $approvedCount;
        $sectionAverage = $totalStudents > 0
            ? round((float) $rows->avg('final_grade'), 2)
            : 0.00;
        $approvalRate = $totalStudents > 0
            ? round(($approvedCount / $totalStudents) * 100, 1)
            : 0.0;

        return [
            'total_students' => $totalStudents,
            'approved_count' => $approvedCount,
            'failed_count' => $failedCount,
            'section_average' => $sectionAverage,
            'approval_rate' => $approvalRate,
            'planned_weight' => round($plannedWeight, 2),
        ];
    }
}