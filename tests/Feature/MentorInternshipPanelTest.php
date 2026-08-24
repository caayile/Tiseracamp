<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentorInternshipPanelTest extends TestCase
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

    public function test_mentor_can_open_internship_create_form(): void
    {
        $this->actingAs($this->mentor())
            ->get(route('mentor.internships.create'))
            ->assertOk()
            ->assertSee('Kuota peserta');
    }

    public function test_mentor_can_create_internship_with_quota_and_weeks(): void
    {
        $mentor = $this->mentor();

        $response = $this->actingAs($mentor)->post(route('mentor.internships.store'), [
            'title' => 'Magang Konten Digital',
            'education_level' => 'S1',
            'majors' => 'Ilmu Komunikasi',
            'division' => 'Divisi COE',
            'location' => 'Surakarta',
            'duration_months' => 3,
            'quota' => 12,
            'description' => 'Deskripsi magang',
            'qualifications_text' => "Mahasiswa aktif\nBisa menulis",
        ]);

        $program = Program::query()->where('title', 'Magang Konten Digital')->first();

        $this->assertNotNull($program);
        $this->assertSame('internship', $program->type);
        $this->assertSame($mentor->id, $program->mentor_id);
        $this->assertTrue($program->is_published);
        $this->assertSame(12, $program->internshipQuota());
        $this->assertCount(4, $program->modules);
        $this->assertTrue($program->hasAvailableSeat());

        $response->assertRedirect(route('mentor.internships.curriculum', $program));
    }

    public function test_full_quota_blocks_internship_apply(): void
    {
        $mentor = $this->mentor();
        $student = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
            'email_verified_at' => now(),
            'screening_completed_at' => now(),
        ]);
        $occupied = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
            'email_verified_at' => now(),
            'screening_completed_at' => now(),
        ]);

        $program = Program::create([
            'title' => 'Magang Penuh',
            'slug' => 'magang-penuh-test',
            'type' => 'internship',
            'level' => 'Beginner',
            'education_level' => 'S1',
            'duration_months' => 3,
            'price' => 0,
            'mentor_id' => $mentor->id,
            'is_published' => true,
            'is_open' => true,
            'approval_status' => 'approved',
            'audience' => 'all',
        ]);
        $program->syncInternshipQuota(1);
        $program->enrollments()->create([
            'user_id' => $occupied->id,
            'status' => 'active',
            'batch_id' => $program->enrollableBatchId(),
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('internships.apply', $program))
            ->assertRedirect(route('programs.show', $program->slug));
    }

    public function test_mentor_can_add_weekly_link_assignment(): void
    {
        $mentor = $this->mentor();
        $this->actingAs($mentor)->post(route('mentor.internships.store'), [
            'title' => 'Magang Desain',
            'education_level' => 'S1',
            'duration_months' => 3,
            'quota' => 10,
        ]);

        $program = Program::query()->where('title', 'Magang Desain')->firstOrFail();
        $week = $program->modules()->orderBy('sort_order')->firstOrFail();

        $this->actingAs($mentor)
            ->get(route('mentor.internships.curriculum', $program))
            ->assertOk()
            ->assertSee('Upload tugas')
            ->assertSee('Tugas Minggu 1');

        $this->actingAs($mentor)->post(route('mentor.lessons.store', $week), [
            'title' => 'Tugas Minggu 1',
            'type' => 'assignment',
            'instructions' => 'Upload ke Google Drive lalu tempel tautannya.',
            'duration_minutes' => 30,
        ])->assertRedirect();

        $lesson = $week->lessons()->firstOrFail();
        $this->assertSame('assignment', $lesson->type);
        $this->assertSame('Tugas Minggu 1', $lesson->title);
        $this->assertTrue($lesson->assignment->collectsViaLink());

        $student = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
            'email_verified_at' => now(),
            'screening_completed_at' => now(),
        ]);
        $program->enrollments()->create([
            'user_id' => $student->id,
            'status' => 'active',
            'batch_id' => $program->enrollableBatchId(),
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('learn.lesson', [$program, $lesson]))
            ->assertOk()
            ->assertSee('Tautan pengumpulan')
            ->assertDontSee('name="proof"', false);

        $this->actingAs($student)
            ->post(route('learn.submit', [$program, $lesson]), [
                'file_url' => 'https://drive.google.com/file/d/abc123/view',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $lesson->assignment->id,
            'user_id' => $student->id,
            'file_url' => 'https://drive.google.com/file/d/abc123/view',
        ]);
    }

    public function test_mentor_can_delete_internship_lesson_and_week(): void
    {
        $mentor = $this->mentor();
        $this->actingAs($mentor)->post(route('mentor.internships.store'), [
            'title' => 'Magang Hapus Test',
            'education_level' => 'S1',
            'duration_months' => 3,
            'quota' => 5,
        ]);

        $program = Program::query()->where('title', 'Magang Hapus Test')->firstOrFail();
        $week = $program->modules()->orderBy('sort_order')->firstOrFail();

        $this->actingAs($mentor)->post(route('mentor.lessons.store', $week), [
            'title' => 'Tugas Minggu 1',
            'type' => 'assignment',
            'instructions' => 'Tempel tautan',
            'duration_minutes' => 20,
        ]);

        $lesson = $week->lessons()->firstOrFail();

        $this->actingAs($mentor)
            ->delete(route('mentor.lessons.destroy', $lesson))
            ->assertRedirect();

        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);

        $this->actingAs($mentor)
            ->delete(route('mentor.modules.destroy', $week))
            ->assertRedirect();

        $this->assertDatabaseMissing('modules', ['id' => $week->id]);
    }

    public function test_student_sees_mentor_week_task_on_learn_page(): void
    {
        $mentor = $this->mentor();
        $student = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
            'email_verified_at' => now(),
            'screening_completed_at' => now(),
        ]);

        $this->actingAs($mentor)->post(route('mentor.internships.store'), [
            'title' => 'Magang Mobile Sync',
            'education_level' => 'S1',
            'duration_months' => 3,
            'quota' => 8,
        ]);

        $program = Program::query()->where('title', 'Magang Mobile Sync')->firstOrFail();
        $week = $program->modules()->orderBy('sort_order')->firstOrFail();

        $this->actingAs($mentor)->post(route('mentor.lessons.store', $week), [
            'title' => 'Tugas Minggu 1',
            'type' => 'assignment',
            'instructions' => 'Kumpulkan lewat Drive',
            'duration_minutes' => 20,
        ]);

        $program->enrollments()->create([
            'user_id' => $student->id,
            'status' => 'active',
            'batch_id' => $program->enrollableBatchId(),
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('learn.show', $program))
            ->assertOk()
            ->assertSee('Minggu 1')
            ->assertSee('Tugas Minggu 1')
            ->assertSee('1 tugas');
    }

    public function test_mentor_can_claim_unassigned_internship_without_admin(): void
    {
        $mentor = $this->mentor();
        $program = Program::create([
            'title' => 'Magang Tanpa Mentor',
            'slug' => 'magang-tanpa-mentor',
            'type' => 'internship',
            'level' => 'Beginner',
            'education_level' => 'S1',
            'duration_months' => 3,
            'price' => 0,
            'mentor_id' => null,
            'is_published' => true,
            'is_open' => true,
            'approval_status' => 'approved',
            'audience' => 'all',
        ]);
        $program->ensureInternshipWeeks();

        $this->actingAs($mentor)
            ->post(route('mentor.internships.claim', $program))
            ->assertRedirect(route('mentor.internships.curriculum', $program));

        $this->assertSame($mentor->id, $program->fresh()->mentor_id);

        $this->actingAs($mentor)
            ->get(route('mentor.internships.curriculum', $program))
            ->assertOk()
            ->assertSee('Minggu 1');
    }
}
