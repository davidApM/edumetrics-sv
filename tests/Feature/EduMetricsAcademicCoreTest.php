<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\Period;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EduMetricsAcademicCoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_periods_weights_must_sum_exactly_100_percent(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $periods = Period::orderBy('period_order')->get();
        $this->assertGreaterThan(0, $periods->count());

        // Intento inválido: suma distinta de 100%
        $invalidPayload = $periods->map(fn ($period) => [
            'id' => $period->id,
            'name' => $period->name,
            'weight_percentage' => 10.00,
        ])->toArray();

        $this->actingAs($admin)
            ->put(route('academic.periods.update'), ['periods' => $invalidPayload])
            ->assertSessionHas('error');

        // Intento válido: distribución equitativa al 100%
        $equalWeight = round(100 / $periods->count(), 2);
        $validPayload = $periods->map(fn ($period) => [
            'id' => $period->id,
            'name' => $period->name,
            'weight_percentage' => $equalWeight,
        ])->toArray();

        $this->actingAs($admin)
            ->put(route('academic.periods.update'), ['periods' => $validPayload])
            ->assertRedirect(route('academic.index'))
            ->assertSessionHas('success');
    }

    public function test_subject_with_assessments_cannot_be_deleted_while_empty_subject_can(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $subjectWithAssessments = Subject::has('assessments')->first();
        if ($subjectWithAssessments) {
            $this->actingAs($admin)
                ->delete(route('academic.subjects.destroy', $subjectWithAssessments))
                ->assertSessionHas('error');

            $this->assertDatabaseHas('subjects', ['id' => $subjectWithAssessments->id]);
        }

        $gradeLevel = GradeLevel::first();
        $newSubject = Subject::create([
            'code' => 'TMP-101',
            'name' => 'Asignatura Temporal QA',
            'grade_level_id' => $gradeLevel->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('academic.subjects.destroy', $newSubject))
            ->assertRedirect(route('academic.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('subjects', ['id' => $newSubject->id]);
    }

    public function test_admin_can_enroll_student_enforce_unique_nie_and_toggle_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $section = Section::first();

        $this->actingAs($admin)
            ->post(route('students.store'), [
                'nie' => '20260099',
                'first_name' => 'Valeria Sofía',
                'last_name' => 'Martínez Cruz',
                'section_id' => $section->id,
                'is_active' => 1,
            ])
            ->assertRedirect(route('students.index', ['section_id' => $section->id]));

        $this->assertDatabaseHas('students', [
            'nie' => '20260099',
            'first_name' => 'Valeria Sofía',
            'is_active' => true,
        ]);

        // NIE duplicado debe ser rechazado
        $this->actingAs($admin)
            ->post(route('students.store'), [
                'nie' => '20260099',
                'first_name' => 'Otro',
                'last_name' => 'Alumno',
                'section_id' => $section->id,
            ])
            ->assertSessionHasErrors('nie');

        $student = Student::where('nie', '20260099')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('students.toggle-status', $student))
            ->assertSessionHas('success');

        $this->assertFalse($student->fresh()->is_active);
    }

   public function test_gradebook_and_official_pdf_export_work_properly(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $section = Section::first();
        $subject = Subject::where('grade_level_id', $section->grade_level_id)->first();
        $period = Period::orderBy('period_order')->first();

        $this->actingAs($admin)
            ->get(route('gradebook.index', [
                'section_id' => $section->id,
                'subject_id' => $subject->id,
            ]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('gradebook.export.section', [
                'section_id' => $section->id,
                'subject_id' => $subject->id,
                'period_id' => $period->id,
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}