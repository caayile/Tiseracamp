<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NormalizeInternshipWeeksTest extends TestCase
{
    use RefreshDatabase;

    private function internship(string $title = 'Magang Uji'): Program
    {
        return Program::create([
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'type' => 'internship',
            'description' => 'Deskripsi',
            'price' => 0,
            'level' => 'Beginner',
            'duration_months' => 3,
            'is_published' => true,
            'approval_status' => 'approved',
        ]);
    }

    private function legacyModule(Program $program, string $title, int $sortOrder): Module
    {
        return Module::create([
            'program_id' => $program->id,
            'title' => $title,
            'sort_order' => $sortOrder,
        ]);
    }

    private function lesson(Module $module, string $title, string $type, int $sortOrder = 1): Lesson
    {
        return Lesson::create([
            'module_id' => $module->id,
            'title' => $title,
            'type' => $type,
            'content' => 'Isi',
            'duration_minutes' => 15,
            'sort_order' => $sortOrder,
        ]);
    }

    public function test_legacy_bootcamp_modules_become_four_weeks(): void
    {
        $program = $this->internship();
        $module = $this->legacyModule($program, 'Fondasi & Mindset', 1);
        $this->lesson($module, 'Pengantar program', 'text');
        $this->lesson($module, 'Checkpoint quiz', 'quiz', 2);

        $this->artisan('internships:normalize-weeks')->assertSuccessful();

        $program->load('modules');
        $this->assertSame(
            ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
            $program->modules->sortBy('sort_order')->pluck('title')->all()
        );

        $lessons = Lesson::whereIn('module_id', $program->modules->pluck('id'))->get();
        $this->assertSame(0, $lessons->where('type', '!=', 'assignment')->count());
        $this->assertSame(4, $lessons->where('type', 'assignment')->count());
    }

    public function test_mentor_lessons_are_moved_into_week_one_instead_of_deleted(): void
    {
        $program = $this->internship();
        $module = $this->legacyModule($program, 'Core Skills', 1);
        $this->lesson($module, 'Pengantar program', 'text');
        $this->lesson($module, 'Materi buatan mentor', 'video', 2);

        $this->artisan('internships:normalize-weeks')->assertSuccessful();

        $program->load('modules.lessons');
        $weekOne = $program->modules->firstWhere('title', 'Minggu 1');

        $this->assertNotNull($weekOne);
        $this->assertSame(
            ['Materi buatan mentor'],
            $weekOne->lessons->where('type', '!=', 'assignment')->pluck('title')->values()->all()
        );
    }

    public function test_duplicate_week_modules_are_merged(): void
    {
        $program = $this->internship();
        $first = $this->legacyModule($program, 'Minggu 1', 1);
        $second = $this->legacyModule($program, 'Minggu 1', 2);
        $this->lesson($first, 'Tugas A', 'text');
        $this->lesson($second, 'Tugas B', 'text');

        $this->artisan('internships:normalize-weeks')->assertSuccessful();

        $program->load('modules.lessons');
        $this->assertCount(4, $program->modules);
        $this->assertSame(
            ['Tugas A', 'Tugas B'],
            $program->modules->firstWhere('title', 'Minggu 1')
                ->lessons->where('type', '!=', 'assignment')->pluck('title')->sort()->values()->all()
        );
    }

    public function test_renamed_week_modules_are_kept(): void
    {
        $program = $this->internship();
        $this->legacyModule($program, 'Minggu 1 - Riset Pasar', 1);

        $this->artisan('internships:normalize-weeks')->assertSuccessful();

        $program->load('modules');
        $this->assertContains('Minggu 1 - Riset Pasar', $program->modules->pluck('title')->all());
        $this->assertCount(4, $program->modules);
    }

    public function test_existing_weeks_get_an_assignment_slot_backfilled(): void
    {
        $program = $this->internship();
        $this->legacyModule($program, 'Minggu 1', 1);
        $this->legacyModule($program, 'Minggu 2', 2);

        $this->artisan('internships:normalize-weeks')->assertSuccessful();

        $program->load('modules.lessons.assignment');
        foreach ($program->modules as $module) {
            $task = $module->lessons->firstWhere('type', 'assignment');

            $this->assertNotNull($task, $module->title.' tidak punya slot pengumpulan tugas');
            $this->assertNotNull($task->assignment);
            $this->assertSame('assignment', $task->assignment->kind);
        }
    }

    public function test_assignment_slot_stays_last_within_a_week(): void
    {
        $program = $this->internship();
        $module = $this->legacyModule($program, 'Minggu 1', 1);
        $this->lesson($module, 'Materi buatan mentor', 'video', 1);

        $this->artisan('internships:normalize-weeks')->assertSuccessful();

        $titles = $module->lessons()->get()->pluck('title')->all();
        $this->assertSame(['Materi buatan mentor', 'Tugas Minggu 1'], $titles);
    }

    public function test_command_is_idempotent(): void
    {
        $program = $this->internship();
        $program->ensureInternshipWeeks();

        $this->artisan('internships:normalize-weeks')->assertSuccessful();
        $this->artisan('internships:normalize-weeks')->assertSuccessful();

        $program->load('modules');
        $this->assertCount(4, $program->modules);
    }
}
