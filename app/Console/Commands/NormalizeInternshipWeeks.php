<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Program;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeInternshipWeeks extends Command
{
    protected $signature = 'internships:normalize-weeks {--dry-run : Tampilkan rencana perubahan tanpa menyimpan}';

    protected $description = 'Samakan struktur materi semua magang menjadi Minggu 1-4';

    /**
     * Materi bawaan seeder bootcamp. Hanya materi ini yang boleh dibuang;
     * sisanya dianggap buatan mentor dan dipindahkan ke Minggu 1.
     */
    private const DEMO_LESSONS = [
        'Pengantar program' => 'text',
        'Praktik inti (video)' => 'video',
        'Modul PDF pendukung' => 'pdf',
        'Artikel mendalam' => 'article',
        'Checkpoint quiz' => 'quiz',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $programs = Program::where('type', 'internship')->with('modules.lessons')->orderBy('id')->get();

        if ($programs->isEmpty()) {
            $this->info('Tidak ada program magang.');

            return self::SUCCESS;
        }

        foreach ($programs as $program) {
            $this->line($program->id.' | '.$program->title);

            $legacy = $program->modules->filter(fn (Module $m) => $this->weekNumber($m->title) === null);
            $weeks = $program->modules->filter(fn (Module $m) => $this->weekNumber($m->title) !== null);
            $duplicates = $weeks->groupBy(fn (Module $m) => $this->weekNumber($m->title))
                ->filter(fn ($group) => $group->count() > 1);

            if ($legacy->isEmpty() && $duplicates->isEmpty() && $weeks->count() === 4) {
                if (! $dryRun) {
                    $program->ensureWeeklyAssignments();
                }

                $this->line('   sudah rapi (Minggu 1-4)');

                continue;
            }

            $rescued = $legacy->flatMap->lessons->reject(fn (Lesson $l) => $this->isDemoLesson($l));

            foreach ($legacy as $module) {
                $this->line('   hapus modul lama: '.$module->title);
            }

            foreach ($rescued as $lesson) {
                $this->line('   pindahkan ke Minggu 1: '.$lesson->title);
            }

            foreach ($duplicates as $week => $group) {
                $this->line('   gabung '.$group->count().' modul duplikat "Minggu '.$week.'"');
            }

            if ($dryRun) {
                continue;
            }

            DB::transaction(function () use ($program, $legacy, $duplicates, $rescued) {
                foreach ($duplicates as $group) {
                    $keeper = $group->sortBy('id')->first();
                    foreach ($group->where('id', '!=', $keeper->id) as $extra) {
                        $extra->lessons()->update(['module_id' => $keeper->id]);
                        $extra->delete();
                    }
                }

                $program->unsetRelation('modules');
                $this->ensureWeeks($program);

                $firstWeek = $program->modules()->get()
                    ->filter(fn (Module $m) => $this->weekNumber($m->title) !== null)
                    ->sortBy(fn (Module $m) => $this->weekNumber($m->title))
                    ->first();

                $sortOrder = (int) $firstWeek->lessons()->max('sort_order');
                foreach ($rescued as $lesson) {
                    $lesson->update([
                        'module_id' => $firstWeek->id,
                        'sort_order' => ++$sortOrder,
                    ]);
                }

                foreach ($legacy as $module) {
                    $module->lessons()->whereNotIn('id', $rescued->pluck('id'))->delete();
                    $module->delete();
                }

                $program->ensureWeeklyAssignments();
            });

            $program->load('modules');
            $this->line('   hasil: '.$program->modules->sortBy('sort_order')->pluck('title')->implode(', '));
        }

        if ($dryRun) {
            $this->warn('Dry run — tidak ada perubahan yang disimpan.');
        }

        return self::SUCCESS;
    }

    private function ensureWeeks(Program $program): void
    {
        $existing = $program->modules()->get()->keyBy(fn (Module $m) => $this->weekNumber($m->title));

        foreach (range(1, 4) as $week) {
            if ($existing->has($week)) {
                $existing[$week]->update(['sort_order' => $week]);

                continue;
            }

            $program->modules()->create([
                'title' => 'Minggu '.$week,
                'sort_order' => $week,
            ]);
        }

        $program->unsetRelation('modules');
    }

    private function isDemoLesson(Lesson $lesson): bool
    {
        return (self::DEMO_LESSONS[$lesson->title] ?? null) === $lesson->type;
    }

    private function weekNumber(string $title): ?int
    {
        return Program::weekNumber($title);
    }
}
