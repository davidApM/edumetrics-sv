<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EduMetricsRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/academic')->assertRedirect('/login');
        $this->get('/students')->assertRedirect('/login');
        $this->get('/gradebook')->assertRedirect('/login');
    }

    public function test_docente_cannot_access_academic_management_module(): void
    {
        $docente = User::factory()->create();
        $docente->assignRole(Role::firstOrCreate(['name' => 'docente']));

        $gradeLevel = GradeLevel::first();

        $this->actingAs($docente)
            ->get(route('academic.index'))
            ->assertForbidden();

        $this->actingAs($docente)
            ->post(route('academic.subjects.store'), [
                'code' => 'TEST-99',
                'name' => 'Materia No Autorizada',
                'grade_level_id' => $gradeLevel->id,
            ])
            ->assertForbidden();

        $this->actingAs($docente)
            ->post(route('academic.sections.store'), [
                'grade_level_id' => $gradeLevel->id,
                'name' => 'Z',
            ])
            ->assertForbidden();
    }

    public function test_docente_cannot_create_edit_or_toggle_students(): void
    {
        $docente = User::factory()->create();
        $docente->assignRole(Role::firstOrCreate(['name' => 'docente']));

        $section = Section::first();
        $student = Student::first();

        $this->actingAs($docente)
            ->get(route('students.create'))
            ->assertForbidden();

        $this->actingAs($docente)
            ->post(route('students.store'), [
                'nie' => '99988877',
                'first_name' => 'Intento',
                'last_name' => 'Bloqueado',
                'section_id' => $section->id,
            ])
            ->assertForbidden();

        if ($student) {
            $this->actingAs($docente)
                ->patch(route('students.toggle-status', $student))
                ->assertForbidden();
        }
    }

    public function test_admin_and_coordinador_can_access_academic_and_student_management(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $coordinador = User::factory()->create();
        $coordinador->assignRole(Role::firstOrCreate(['name' => 'coordinador']));

        $this->actingAs($admin)
            ->get(route('academic.index'))
            ->assertOk();

        $this->actingAs($coordinador)
            ->get(route('academic.index'))
            ->assertOk();

        $this->actingAs($coordinador)
            ->get(route('students.create'))
            ->assertOk();
    }

    public function test_admin_can_create_teacher_credentials_and_docente_cannot_access_teachers_module(): void
    {
        $docente = User::factory()->create();
        $docente->assignRole(Role::firstOrCreate(['name' => 'docente']));

        $this->actingAs($docente)
            ->get(route('teachers.index'))
            ->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $this->actingAs($admin)
            ->post(route('teachers.store'), [
                'name' => 'Prof. Laura Rivas',
                'email' => 'lrivas@edumetrics.sv',
                'password' => 'ClaveSegura2026',
                'password_confirmation' => 'ClaveSegura2026',
            ])
            ->assertRedirect(route('teachers.index'));

        $createdTeacher = User::where('email', 'lrivas@edumetrics.sv')->firstOrFail();
        $this->assertTrue($createdTeacher->hasRole('docente'));
    }
}