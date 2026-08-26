<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_every_week_gets_an_assignment_slot_automatically(): void
    {
        $mentor = $this->mentor();
        $this->actingAs($mentor)->post(route('mentor.internships.store'), [
            'title' => 'Magang Otomatis',
            'education_level' => 'S1',
            'duration_months' => 3,
            'quota' => 10,
        ]);

        $program = Program::query()->where('title', 'Magang Otomatis')->firstOrFail();

        foreach ($program->modules()->orderBy('sort_order')->get() as $module) {
            $task = $module->lessons()->where('type', 'assignment')->first();

            $this->assertNotNull($task, $module->title.' tidak punya slot pengumpulan tugas');
            $this->assertSame('Tugas '.$module->title, $task->title);
            $this->assertTrue($task->assignment->collectsWork());
        }
    }

    public function test_weekly_assignment_slot_cannot_be_deleted(): void
    {
        $mentor = $this->mentor();
        $this->actingAs($mentor)->post(route('mentor.internships.store'), [
            'title' => 'Magang Slot Tetap',
            'education_level' => 'S1',
            'duration_months' => 3,
            'quota' => 10,
        ]);

        $program = Program::query()->where('title', 'Magang Slot Tetap')->firstOrFail();
        $week = $program->modules()->orderBy('sort_order')->firstOrFail();
        $task = $week->lessons()->where('type', 'assignment')->firstOrFail();

        $this->actingAs($mentor)
            ->delete(route('mentor.lessons.destroy', $task))
            ->assertRedirect();

        $this->assertDatabaseHas('lessons', ['id' => $task->id]);
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
        $lesson = $week->lessons()->where('type', 'assignment')->firstOrFail();

        $this->actingAs($mentor)
            ->get(route('mentor.internships.curriculum', $program))
            ->assertOk()
            ->assertSee('Pengumpulan tugas Minggu 1')
            ->assertSee('Tugas Minggu 1');

        $this->actingAs($mentor)->put(route('mentor.assignments.update', $lesson->assignment), [
            'title' => 'Tugas Minggu 1',
            'instructions' => 'Upload ke Google Drive lalu tempel tautannya.',
        ])->assertRedirect();

        $lesson->refresh()->load('assignment');
        $this->assertSame('assignment', $lesson->type);
        $this->assertSame('Tugas Minggu 1', $lesson->title);
        $this->assertSame('Upload ke Google Drive lalu tempel tautannya.', $lesson->assignment->instructions);
        $this->assertTrue($lesson->assignment->collectsWork());

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
            ->assertSee('Tautan tugas')
            ->assertSee('Unggah file')
            ->assertSee('name="proof"', false);

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

    public function test_student_can_submit_weekly_assignment_as_file_upload(): void
    {
        Storage::fake(media_disk());

        $mentor = $this->mentor();
        $this->actingAs($mentor)->post(route('mentor.internships.store'), [
            'title' => 'Magang Unggah File',
            'education_level' => 'S1',
            'duration_months' => 3,
            'quota' => 5,
        ]);

        $program = Program::query()->where('title', 'Magang Unggah File')->firstOrFail();
        $week = $program->modules()->orderBy('sort_order')->firstOrFail();
        $lesson = $week->lessons()->where('type', 'assignment')->firstOrFail();

        $this->actingAs($mentor)->put(route('mentor.assignments.update', $lesson->assignment), [
            'title' => 'Tugas Minggu 1',
            'instructions' => 'Kumpulkan laporan mingguan.',
        ]);

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
            ->post(route('learn.submit', [$program, $lesson]), [
                'proof' => UploadedFile::fake()->create('laporan-minggu-1.pdf', 200, 'application/pdf'),
                'notes' => 'Laporan minggu pertama.',
            ])
            ->assertRedirect();

        $submission = Submission::where('assignment_id', $lesson->assignment->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        $this->assertStringStartsWith('submissions/', $submission->file_url);
        Storage::disk(media_disk())->assertExists($submission->file_url);

        $this->actingAs($mentor)
            ->get(route('mentor.submissions'))
            ->assertOk()
            ->assertSee('Tugas Minggu 1')
            ->assertSee('Minggu 1')
            ->assertSee('Unduh file tugas');
    }

    public function test_weekly_assignment_requires_link_or_file(): void
    {
        $mentor = $this->mentor();
        $this->actingAs($mentor)->post(route('mentor.internships.store'), [
            'title' => 'Magang Validasi',
            'education_level' => 'S1',
            'duration_months' => 3,
            'quota' => 5,
        ]);

        $program = Program::query()->where('title', 'Magang Validasi')->firstOrFail();
        $week = $program->modules()->orderBy('sort_order')->firstOrFail();
        $lesson = $week->lessons()->where('type', 'assignment')->firstOrFail();

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
            ->post(route('learn.submit', [$program, $lesson]), ['notes' => 'lupa lampiran'])
            ->assertSessionHasErrors('file_url');

        $this->assertDatabaseCount('submissions', 0);
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
            'title' => 'Materi pembuka',
            'type' => 'text',
            'content' => 'Selamat datang',
            'duration_minutes' => 10,
        ]);

        $lesson = $week->lessons()->where('type', 'text')->firstOrFail();

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
        $task = $week->lessons()->where('type', 'assignment')->firstOrFail();

        $this->actingAs($mentor)->put(route('mentor.assignments.update', $task->assignment), [
            'title' => 'Tugas Minggu 1',
            'instructions' => 'Kumpulkan lewat Drive',
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

    public function test_curriculum_page_shows_who_receives_the_material(): void
    {
        $mentor = $this->mentor();
        $this->actingAs($mentor)->post(route('mentor.internships.store'), [
            'title' => 'Magang Audiens',
            'education_level' => 'S1',
            'duration_months' => 3,
            'quota' => 5,
        ]);

        $program = Program::query()->where('title', 'Magang Audiens')->firstOrFail();

        $this->actingAs($mentor)
            ->get(route('mentor.internships.curriculum', $program))
            ->assertOk()
            ->assertSee('Belum ada peserta diterima di magang ini');

        $student = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
            'name' => 'Okky Puspa',
            'email_verified_at' => now(),
            'screening_completed_at' => now(),
        ]);
        $program->enrollments()->create([
            'user_id' => $student->id,
            'status' => 'active',
            'batch_id' => $program->enrollableBatchId(),
            'enrolled_at' => now(),
        ]);

        $this->actingAs($mentor)
            ->get(route('mentor.internships.curriculum', $program))
            ->assertOk()
            ->assertSee('Materi ini dilihat oleh')
            ->assertSee('Okky Puspa');
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
