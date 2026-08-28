<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Program;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MentorMaterialEditTest extends TestCase
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
     * @return array{0: User, 1: Program, 2: \App\Models\Module}
     */
    private function seededModule(): array
    {
        $mentor = $this->mentor();
        $program = Program::create([
            'title' => 'Bootcamp Edit Materi',
            'slug' => 'bootcamp-edit-materi-'.uniqid(),
            'type' => 'bootcamp',
            'level' => 'Beginner',
            'price' => 0,
            'mentor_id' => $mentor->id,
            'is_published' => true,
            'is_open' => true,
            'approval_status' => 'approved',
            'audience' => 'all',
        ]);
        $module = $program->modules()->create([
            'title' => 'Modul 1',
            'sort_order' => 1,
        ]);

        return [$mentor, $program, $module];
    }

    public function test_mentor_can_update_video_url(): void
    {
        [$mentor, , $module] = $this->seededModule();
        $lesson = $module->lessons()->create([
            'title' => 'Video lama',
            'type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration_minutes' => 10,
            'sort_order' => 1,
        ]);

        $this->actingAs($mentor)
            ->put(route('mentor.materials.update', $lesson), [
                'title' => 'Video terbaru',
                'type' => 'video',
                'video_url' => 'https://youtu.be/jNQXAC9IVRw',
                'duration_minutes' => 12,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $lesson->refresh();
        $this->assertSame('Video terbaru', $lesson->title);
        $this->assertSame('https://www.youtube.com/embed/jNQXAC9IVRw', $lesson->video_url);
    }

    public function test_mentor_can_replace_uploaded_pdf_without_reentering_url(): void
    {
        Storage::fake(media_disk());
        [$mentor, , $module] = $this->seededModule();

        $oldPath = 'lesson-pdfs/lama.pdf';
        Storage::disk(media_disk())->put($oldPath, 'old-pdf');

        $lesson = $module->lessons()->create([
            'title' => 'Dokumen lama',
            'type' => 'pdf',
            'file_url' => $oldPath,
            'file_type' => 'pdf',
            'duration_minutes' => 15,
            'sort_order' => 1,
        ]);

        $this->actingAs($mentor)
            ->get(route('mentor.materials.edit', $lesson))
            ->assertOk()
            ->assertSee('Dokumen saat ini')
            ->assertDontSee('value="'.$oldPath.'"', false);

        $this->actingAs($mentor)
            ->put(route('mentor.materials.update', $lesson), [
                'title' => 'Dokumen baru',
                'type' => 'pdf',
                'description' => 'Baca bab 2',
                'pdf_file' => UploadedFile::fake()->create('modul-baru.pdf', 120, 'application/pdf'),
                'duration_minutes' => 15,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $lesson->refresh();
        $this->assertSame('Dokumen baru', $lesson->title);
        $this->assertSame('pdf', $lesson->file_type);
        $this->assertStringStartsWith('lesson-pdfs/', $lesson->file_url);
        $this->assertNotSame($oldPath, $lesson->file_url);
        Storage::disk(media_disk())->assertExists($lesson->file_url);
        Storage::disk(media_disk())->assertMissing($oldPath);
    }

    public function test_mentor_can_upload_new_recording_audio(): void
    {
        Storage::fake(media_disk());
        [$mentor, , $module] = $this->seededModule();

        $lesson = $module->lessons()->create([
            'title' => 'Rekaman lama',
            'type' => 'recording',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration_minutes' => 8,
            'sort_order' => 1,
        ]);

        $this->actingAs($mentor)
            ->put(route('mentor.materials.update', $lesson), [
                'title' => 'Rekaman baru',
                'type' => 'recording',
                'audio_file' => UploadedFile::fake()->create('briefing.mp3', 80, 'audio/mpeg'),
                'duration_minutes' => 8,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $lesson->refresh();
        $this->assertSame('Rekaman baru', $lesson->title);
        $this->assertSame('audio', $lesson->file_type);
        $this->assertStringStartsWith('lesson-audios/', $lesson->file_url);
        $this->assertNull($lesson->getRawOriginal('video_url'));
        Storage::disk(media_disk())->assertExists($lesson->file_url);
    }

    public function test_mentor_can_update_quiz_questions(): void
    {
        [$mentor, , $module] = $this->seededModule();
        $lesson = $module->lessons()->create([
            'title' => 'Quiz modul',
            'type' => 'quiz',
            'duration_minutes' => 20,
            'sort_order' => 1,
        ]);
        $assignment = $lesson->assignment()->create([
            'title' => 'Quiz modul',
            'instructions' => 'Pilih jawaban benar',
            'kind' => 'quiz',
        ]);
        QuizQuestion::create([
            'assignment_id' => $assignment->id,
            'question' => 'Soal lama?',
            'options' => ['A', 'B', 'C', 'D'],
            'correct_index' => 0,
            'points' => 10,
        ]);

        $this->actingAs($mentor)
            ->put(route('mentor.materials.update', $lesson), [
                'title' => 'Quiz diperbarui',
                'type' => 'quiz',
                'instructions' => 'Kerjakan dengan teliti',
                'duration_minutes' => 20,
                'questions' => [
                    [
                        'question' => 'Ibu kota Indonesia?',
                        'options' => ['Jakarta', 'Bandung', 'Surabaya', 'Medan'],
                        'correct_index' => 0,
                    ],
                    [
                        'question' => '2 + 2 = ?',
                        'options' => ['3', '4', '5', '6'],
                        'correct_index' => 1,
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $lesson->refresh()->load('assignment.questions');
        $this->assertSame('Quiz diperbarui', $lesson->title);
        $this->assertSame('Kerjakan dengan teliti', $lesson->assignment->instructions);
        $this->assertCount(2, $lesson->assignment->questions);
        $this->assertSame('Ibu kota Indonesia?', $lesson->assignment->questions->first()->question);
        $this->assertSame(1, (int) $lesson->assignment->questions->last()->correct_index);
    }

    public function test_mentor_can_update_assignment_with_deadline(): void
    {
        [$mentor, , $module] = $this->seededModule();
        $lesson = $module->lessons()->create([
            'title' => 'Tugas modul',
            'type' => 'assignment',
            'duration_minutes' => 30,
            'sort_order' => 1,
        ]);
        $lesson->assignment()->create([
            'title' => 'Tugas modul',
            'instructions' => 'Kumpulkan laporan',
            'kind' => 'assignment',
        ]);

        $deadline = now()->addWeek()->startOfMinute();

        $this->actingAs($mentor)
            ->get(route('mentor.materials.edit', $lesson))
            ->assertOk()
            ->assertSee('Deadline')
            ->assertSee('Pengumpulan tugas');

        $this->actingAs($mentor)
            ->put(route('mentor.materials.update', $lesson), [
                'title' => 'Tugas riset kompetitor',
                'type' => 'assignment',
                'instructions' => 'Upload PDF atau tautan Drive.',
                'deadline' => $deadline->format('Y-m-d\TH:i'),
                'duration_minutes' => 30,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $lesson->refresh()->load('assignment');
        $this->assertSame('Tugas riset kompetitor', $lesson->title);
        $this->assertSame('Upload PDF atau tautan Drive.', $lesson->assignment->instructions);
        $this->assertNotNull($lesson->assignment->deadline);
        $this->assertSame($deadline->format('Y-m-d H:i'), $lesson->assignment->deadline->format('Y-m-d H:i'));
    }
}
