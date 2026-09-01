<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentorReviewAndGradeSplitTest extends TestCase
{
    use RefreshDatabase;

    private function mentor(): User
    {
        return User::factory()->create([
            'role' => 'mentor',
            'status' => 'active',
            'email_verified_at' => now(),
            'screening_completed_at' => now(),
        ]);
    }

    /**
     * @return array{0: User, 1: Program, 2: Submission}
     */
    private function bootcampWithSubmission(User $mentor): array
    {
        $program = Program::create([
            'title' => 'Bootcamp UI',
            'slug' => 'bootcamp-ui-'.uniqid(),
            'type' => 'bootcamp',
            'level' => 'Beginner',
            'price' => 0,
            'mentor_id' => $mentor->id,
            'is_published' => true,
            'is_open' => true,
            'approval_status' => 'approved',
            'audience' => 'all',
        ]);
        $module = $program->modules()->create(['title' => 'Modul 1', 'sort_order' => 1]);
        $lesson = $module->lessons()->create([
            'title' => 'Tugas prototipe',
            'type' => 'assignment',
            'duration_minutes' => 30,
            'sort_order' => 1,
        ]);
        $assignment = $lesson->assignment()->create([
            'title' => 'Tugas prototipe',
            'kind' => 'assignment',
        ]);
        $student = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
            'email_verified_at' => now(),
            'screening_completed_at' => now(),
        ]);
        Enrollment::create([
            'user_id' => $student->id,
            'program_id' => $program->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_url' => 'https://drive.google.com/file/d/bootcamp/view',
            'status' => 'submitted',
        ]);

        return [$student, $program, $submission];
    }

    public function test_bootcamp_and_internship_reviews_are_separated(): void
    {
        $mentor = $this->mentor();
        [$student, $bootcampProgram] = $this->bootcampWithSubmission($mentor);

        $this->actingAs($mentor)->post(route('mentor.internships.store'), [
            'title' => 'Magang Review Split',
            'education_level' => 'S1',
            'duration_months' => 3,
            'quota' => 5,
        ]);
        $internship = Program::query()->where('title', 'Magang Review Split')->firstOrFail();
        $week = $internship->modules()->orderBy('sort_order')->firstOrFail();
        $task = $week->lessons()->where('type', 'assignment')->firstOrFail();
        $intern = User::factory()->create([
            'name' => 'Ayu Magang',
            'role' => 'student',
            'status' => 'active',
            'email_verified_at' => now(),
            'screening_completed_at' => now(),
        ]);
        $internship->enrollments()->create([
            'user_id' => $intern->id,
            'status' => 'active',
            'batch_id' => $internship->enrollableBatchId(),
            'enrolled_at' => now(),
        ]);
        $internshipSubmission = Submission::create([
            'assignment_id' => $task->assignment->id,
            'user_id' => $intern->id,
            'file_url' => 'https://drive.google.com/file/d/magang/view',
            'status' => 'submitted',
        ]);

        $this->actingAs($mentor)
            ->get(route('mentor.submissions.bootcamp'))
            ->assertOk()
            ->assertSee('Review Tugas Bootcamp')
            ->assertSee('Tugas prototipe')
            ->assertSee('Tugas bootcamp')
            ->assertSee($student->name)
            ->assertDontSee('Simpan penilaian');

        $this->actingAs($mentor)
            ->get(route('mentor.submissions.internship'))
            ->assertOk()
            ->assertSee('Review Tugas Magang')
            ->assertSee('Tugas Minggu 1')
            ->assertSee('Tugas magang')
            ->assertSee($intern->name)
            ->assertSee('Buka pengumpulan')
            ->assertDontSee('Tugas prototipe')
            ->assertDontSee('Simpan penilaian');

        $this->actingAs($mentor)
            ->get(route('mentor.submissions.show', $internshipSubmission))
            ->assertOk()
            ->assertSee('Pengumpulan Tugas Magang')
            ->assertSee($intern->name)
            ->assertSee('Tugas Minggu 1')
            ->assertSee('Buka tautan tugas')
            ->assertSee('Tandai sudah dicek')
            ->assertSee('Simpan penilaian');

        $this->actingAs($mentor)
            ->get(route('mentor.submissions.file', $internshipSubmission))
            ->assertRedirect('https://drive.google.com/file/d/magang/view');

        $internshipSubmission->update([
            'file_url' => 'https://drive.google.com/uc?export=download&id=1AbCdefGhijkLmNopQrsTuvWxyz012345',
        ]);
        $this->actingAs($mentor)
            ->get(route('mentor.submissions.file', $internshipSubmission))
            ->assertRedirect('https://drive.google.com/file/d/1AbCdefGhijkLmNopQrsTuvWxyz012345/view');
        $internshipSubmission->update([
            'file_url' => 'https://drive.google.com/file/d/magang/view',
        ]);

        $this->actingAs($mentor)
            ->post(route('mentor.submissions.mark', $internshipSubmission))
            ->assertRedirect(route('mentor.submissions.show', $internshipSubmission));

        $internshipSubmission->refresh();
        $this->assertSame('reviewed', $internshipSubmission->status);

        $this->actingAs($mentor)
            ->post(route('mentor.submissions.review', $internshipSubmission), [
                'score' => 91,
                'feedback' => 'Laporan minggu ini rapi.',
            ])
            ->assertRedirect(route('mentor.submissions.internship'));

        $internshipSubmission->refresh();
        $this->assertSame(91, (int) $internshipSubmission->score);
        $this->assertSame('reviewed', $internshipSubmission->status);

        $bootcampEnrollment = Enrollment::where('program_id', $bootcampProgram->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        $this->actingAs($mentor)
            ->get(route('mentor.grades.bootcamp'))
            ->assertOk()
            ->assertSee('Nilai Peserta Bootcamp')
            ->assertSee('Bootcamp UI')
            ->assertSee($student->name)
            ->assertSee('Klik untuk input nilai')
            ->assertDontSee('Simpan nilai bootcamp')
            ->assertDontSee('Semua program magang');

        $this->actingAs($mentor)
            ->get(route('mentor.grades.bootcamp.edit', $bootcampEnrollment))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Input Nilai Bootcamp')
            ->assertSee('Simpan nilai bootcamp');

        $this->actingAs($mentor)
            ->put(route('mentor.grades.bootcamp.update', $bootcampEnrollment), [
                'final_score' => 88,
                'grade_note' => 'Kuat di praktik.',
            ])
            ->assertRedirect(route('mentor.grades.bootcamp'))
            ->assertSessionHas('success');

        $bootcampEnrollment->refresh();
        $this->assertSame(88, (int) $bootcampEnrollment->final_score);
        $this->assertSame('bootcamp', $bootcampEnrollment->grade_aspects['kind'] ?? null);

        $this->actingAs($mentor)
            ->get(route('mentor.grades.bootcamp'))
            ->assertOk()
            ->assertSee('Nilai Peserta Bootcamp')
            ->assertSee('Bootcamp UI')
            ->assertSee($student->name)
            ->assertSee('88')
            ->assertDontSee('Semua program magang');

        $this->actingAs($mentor)
            ->get(route('mentor.grades.index'))
            ->assertOk()
            ->assertSee('Nilai Peserta Magang')
            ->assertSee('Magang Review Split')
            ->assertSee($intern->name)
            ->assertSee('Belum dinilai')
            ->assertDontSee('Bootcamp UI')
            ->assertDontSee('Nama kompetensi project');

        $internshipEnrollment = Enrollment::where('program_id', $internship->id)
            ->where('user_id', $intern->id)
            ->firstOrFail();

        $this->actingAs($mentor)
            ->get(route('mentor.grades.edit', $internshipEnrollment))
            ->assertOk()
            ->assertSee($intern->name)
            ->assertSee('Nama kompetensi project')
            ->assertSee('Simpan nilai');

        $this->actingAs($mentor)
            ->put(route('mentor.grades.update', $internshipEnrollment), [
                'project_name' => ['Implementasi UI'],
                'project_score' => [80],
                'sikap_name' => ['Kehadiran'],
                'sikap_score' => [90],
            ])
            ->assertRedirect(route('mentor.grades.index'));

        $internshipEnrollment->refresh();
        $this->assertSame(86, (int) $internshipEnrollment->final_score);

        $this->actingAs($mentor)
            ->get(route('mentor.grades.index'))
            ->assertOk()
            ->assertSee($intern->name)
            ->assertSee('Rata-rata nilai magang')
            ->assertSee('86')
            ->assertDontSee('Belum dinilai');
    }
}
