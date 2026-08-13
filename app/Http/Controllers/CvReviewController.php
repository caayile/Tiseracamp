<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\CvReview;
use App\Models\CvSubscription;
use App\Services\CvReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class CvReviewController extends Controller
{
    public function plans(): View
    {
        $plans = cv_plans();
        $subscription = auth()->user()->activeCvSubscription();
        $pending = auth()->user()->cvSubscriptions()
            ->where('status', 'waiting_verification')
            ->latest()
            ->first();

        return view('cv-review.plans', compact('plans', 'subscription', 'pending'));
    }

    public function checkout(string $plan): View|RedirectResponse
    {
        $planConfig = cv_plans($plan);
        abort_unless(is_array($planConfig), 404);

        $subscription = auth()->user()->activeCvSubscription();
        if ($subscription && $subscription->plan_code === $plan) {
            return redirect()
                ->route('cv-review.plans')
                ->with('success', 'Paket '.$subscription->plan_name.' sudah aktif. Sisa coba masih bisa dipakai.');
        }

        return view('cv-review.checkout', [
            'plan' => $planConfig,
            'planCode' => $plan,
            'isUpgrade' => (bool) $subscription,
            'currentPlan' => $subscription,
        ]);
    }

    public function purchase(Request $request, string $plan): RedirectResponse
    {
        $planConfig = cv_plans($plan);
        abort_unless(is_array($planConfig), 404);

        $subscription = auth()->user()->activeCvSubscription();
        if ($subscription && $subscription->plan_code === $plan) {
            return redirect()->route('cv-review.plans');
        }

        $pending = auth()->user()->cvSubscriptions()
            ->where('status', 'waiting_verification')
            ->exists();
        if ($pending) {
            return redirect()
                ->route('cv-review.plans')
                ->with('error', 'Masih ada pembayaran menunggu verifikasi. Tunggu admin dulu.');
        }

        $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'proof.required' => 'Upload bukti transfer dulu.',
        ]);

        $path = $request->file('proof')->store('cv-subscriptions', media_disk());

        $newSubscription = CvSubscription::create([
            'user_id' => auth()->id(),
            'plan_code' => $planConfig['code'],
            'plan_name' => $planConfig['name'],
            'amount' => (int) $planConfig['price'],
            'reviews_limit' => $planConfig['reviews'],
            'reviews_used' => 0,
            'invoice_code' => 'CV-'.strtoupper(Str::random(8)),
            'proof_path' => $path,
            'status' => 'waiting_verification',
            'admin_note' => $subscription ? 'Upgrade dari '.$subscription->plan_name : null,
        ]);

        AppNotification::create([
            'user_id' => auth()->id(),
            'title' => $subscription ? 'Upgrade paket CV dikirim' : 'Pembayaran paket CV dikirim',
            'body' => 'Menunggu verifikasi admin untuk invoice '.$newSubscription->invoice_code,
            'type' => 'payment',
            'link' => route('cv-review.plans'),
        ]);

        notify_admins(
            'Pembayaran paket CV baru',
            auth()->user()->name.' mengunggah bukti paket '.$newSubscription->plan_name.' ('.$newSubscription->invoice_code.').',
            'payment',
            route('admin.cv-subscriptions.index')
        );

        return redirect()
            ->route('cv-review.plans')
            ->with('success', 'Bukti pembayaran diunggah. Setelah admin verifikasi, paket baru aktif.');
    }

    public function index(CvReviewService $service): View|RedirectResponse
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->hasActiveCvSubscription()) {
            return redirect()->route('cv-review.plans');
        }

        return view('cv-review.index', [
            'cvReviewReady' => $service->isConfigured(),
            'subscription' => auth()->user()->activeCvSubscription(),
        ]);
    }

    public function store(Request $request, CvReviewService $service): RedirectResponse
    {
        $subscription = auth()->user()->activeCvSubscription();
        if (! auth()->user()->isAdmin() && ! $subscription) {
            return redirect()->route('cv-review.plans')
                ->with('error', 'Pilih & aktifkan paket langganan dulu sebelum review CV.');
        }

        if (! $service->isConfigured()) {
            return back()->with('error', 'Fitur Review CV AI belum siap. Admin perlu set GEMINI_API_KEY / GROQ_API_KEY di .env.');
        }

        $data = $request->validate([
            'target_position' => ['required', 'string', 'max:160'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'education_level' => ['nullable', 'in:D3,D4,S1,SMA/SMK,Lainnya'],
            'preferred_field' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:160'],
            'experience_level' => ['nullable', 'in:mahasiswa,fresh_graduate,1-2_tahun,3+_tahun'],
            'cv' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'target_position.required' => 'Isi posisi / pekerjaan tujuan dulu.',
            'cv.required' => 'Unggah file CV PDF dulu.',
            'cv.mimes' => 'CV harus berformat PDF.',
            'cv.max' => 'Ukuran CV maksimal 5 MB.',
        ]);

        $context = [
            'target_position' => $data['target_position'],
            'company_name' => $data['company_name'] ?? null,
            'education_level' => $data['education_level'] ?? null,
            'preferred_field' => $data['preferred_field'] ?? null,
            'location' => $data['location'] ?? null,
            'experience_level' => $data['experience_level'] ?? null,
        ];

        try {
            $file = $data['cv'];
            $result = $service->review($file, $context);

            $path = $file->store('cv-reviews', media_disk());

            $review = CvReview::create([
                'user_id' => auth()->id(),
                'target_position' => $context['target_position'],
                'company_name' => $context['company_name'],
                'education_level' => $context['education_level'],
                'preferred_field' => $context['preferred_field'],
                'location' => $context['location'],
                'experience_level' => $context['experience_level'],
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'score' => $result['score'],
                'result' => $result,
                'provider' => $result['provider'] ?? null,
            ]);

            $subscription?->consumeReview();

            award_achievement(auth()->user(), 'first_cv_review');

            return redirect()
                ->route('cv-review.show', $review)
                ->with('success', 'Form & CV berhasil dianalisis. Mulai dari Review CV, lalu lihat kecocokan karier.');
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat mereview CV. Coba lagi.');
        }
    }

    public function show(Request $request, CvReview $cvReview): View|RedirectResponse
    {
        abort_unless(
            $cvReview->user_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );

        if (! auth()->user()->isAdmin() && ! auth()->user()->hasActiveCvSubscription()) {
            // Allow viewing existing results even if quota ended? Better allow owner to view past reviews.
        }

        $result = $cvReview->result ?? [];
        $points = $result['points'] ?? [];
        $activePointId = $request->string('point')->toString();
        if ($activePointId === '' && $points !== []) {
            $activePointId = (string) ($points[0]['id'] ?? '');
        }

        $journeyStep = $request->integer('step', 1);
        if ($journeyStep < 1 || $journeyStep > 4) {
            $journeyStep = 1;
        }

        $careerTab = $request->string('tab')->toString();
        if (! in_array($careerTab, ['karir', 'skill', 'pengalaman'], true)) {
            $careerTab = 'karir';
        }

        $interview = $cvReview->interview;
        if (is_array($interview) && $request->has('q')) {
            $qIndex = $request->integer('q', 0);
            $max = max(0, count($interview['questions'] ?? []) - 1);
            $interview['current_index'] = max(0, min($qIndex, $max));
        }

        $career = $result['career'] ?? [];
        $jobBoards = cv_job_board_recommendations([
            'target_position' => $cvReview->target_position,
            'suggested_role' => $career['suggested_role'] ?? null,
            'location' => $cvReview->location,
            'alternatives' => $career['alternatives'] ?? [],
        ]);

        return view('cv-review.show', [
            'review' => $cvReview,
            'result' => $result,
            'points' => $points,
            'activePointId' => $activePointId,
            'journeyStep' => $journeyStep,
            'career' => $career,
            'careerTab' => $careerTab,
            'coverLetter' => $cvReview->cover_letter,
            'interview' => $interview,
            'cvReviewReady' => app(CvReviewService::class)->isConfigured(),
            'jobBoards' => $jobBoards,
        ]);
    }

    public function generateCoverLetter(Request $request, CvReview $cvReview, CvReviewService $service): RedirectResponse
    {
        $this->authorizeReviewOwner($cvReview);

        if (! $service->isConfigured()) {
            return back()->with('error', 'Fitur AI belum siap. Set GEMINI_API_KEY / GROQ_API_KEY di .env.');
        }

        $data = $request->validate([
            'tone' => ['nullable', 'in:profesional,hangat,formal'],
            'highlights' => ['nullable', 'string', 'max:500'],
            'company_name' => ['nullable', 'string', 'max:160'],
        ]);

        if (! empty($data['company_name'])) {
            $cvReview->update(['company_name' => $data['company_name']]);
        }

        try {
            $generated = $service->generateCoverLetter($cvReview->file_path, [
                'target_position' => $cvReview->target_position,
                'company_name' => $cvReview->company_name,
                'education_level' => $cvReview->education_level,
                'preferred_field' => $cvReview->preferred_field,
                'location' => $cvReview->location,
                'experience_level' => $cvReview->experience_level,
                'tone' => $data['tone'] ?? 'profesional',
                'highlights' => $data['highlights'] ?? '',
                'summary' => (string) data_get($cvReview->result, 'summary', ''),
            ]);

            $cvReview->update([
                'cover_letter' => [
                    'subject' => $generated['subject'],
                    'body' => $generated['body'],
                    'tips' => $generated['tips'],
                    'tone' => $data['tone'] ?? 'profesional',
                    'generated_at' => now()->toIso8601String(),
                    'provider' => $generated['provider'],
                ],
            ]);

            return redirect()
                ->route('cv-review.show', ['cvReview' => $cvReview, 'step' => 3])
                ->with('success', 'Cover letter berhasil dibuat.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal membuat cover letter. Coba lagi.');
        }
    }

    public function generateInterview(CvReview $cvReview, CvReviewService $service): RedirectResponse
    {
        $this->authorizeReviewOwner($cvReview);

        if (! $service->isConfigured()) {
            return back()->with('error', 'Fitur AI belum siap. Set GEMINI_API_KEY / GROQ_API_KEY di .env.');
        }

        try {
            $generated = $service->generateInterview($cvReview->file_path, [
                'target_position' => $cvReview->target_position,
                'company_name' => $cvReview->company_name,
                'education_level' => $cvReview->education_level,
                'preferred_field' => $cvReview->preferred_field,
                'location' => $cvReview->location,
                'experience_level' => $cvReview->experience_level,
                'summary' => (string) data_get($cvReview->result, 'summary', ''),
            ]);

            $questions = array_map(function (array $q) {
                return [
                    'id' => $q['id'],
                    'question' => $q['question'],
                    'focus' => $q['focus'],
                    'tip' => $q['tip'],
                    'answer' => null,
                    'score' => null,
                    'feedback' => null,
                    'improved_answer' => null,
                ];
            }, $generated['questions']);

            $cvReview->update([
                'interview' => [
                    'questions' => $questions,
                    'current_index' => 0,
                    'started_at' => now()->toIso8601String(),
                    'provider' => $generated['provider'],
                ],
            ]);

            return redirect()
                ->route('cv-review.show', ['cvReview' => $cvReview, 'step' => 4])
                ->with('success', '5 pertanyaan interview siap. Jawab satu per satu.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal membuat soal interview. Coba lagi.');
        }
    }

    public function submitInterviewAnswer(Request $request, CvReview $cvReview, CvReviewService $service): RedirectResponse
    {
        $this->authorizeReviewOwner($cvReview);

        if (! $service->isConfigured()) {
            return back()->with('error', 'Fitur AI belum siap. Set GEMINI_API_KEY / GROQ_API_KEY di .env.');
        }

        $interview = $cvReview->interview;
        if (! is_array($interview) || empty($interview['questions'])) {
            return redirect()
                ->route('cv-review.show', ['cvReview' => $cvReview, 'step' => 4])
                ->with('error', 'Generate pertanyaan interview dulu.');
        }

        $data = $request->validate([
            'question_id' => ['required', 'string', 'max:40'],
            'answer' => ['required', 'string', 'min:20', 'max:3000'],
        ], [
            'answer.required' => 'Isi jawabanmu dulu.',
            'answer.min' => 'Jawaban minimal 20 karakter agar bisa dinilai.',
        ]);

        $questions = $interview['questions'];
        $index = null;
        foreach ($questions as $i => $q) {
            if (($q['id'] ?? '') === $data['question_id']) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return back()->with('error', 'Pertanyaan tidak ditemukan.');
        }

        try {
            $feedback = $service->evaluateInterviewAnswer([
                'question' => $questions[$index]['question'] ?? '',
                'focus' => $questions[$index]['focus'] ?? '',
                'tip' => $questions[$index]['tip'] ?? '',
                'answer' => $data['answer'],
                'target_position' => $cvReview->target_position,
                'company_name' => $cvReview->company_name,
            ]);

            $questions[$index]['answer'] = $data['answer'];
            $questions[$index]['score'] = $feedback['score'];
            $questions[$index]['feedback'] = $feedback['feedback'];
            $questions[$index]['improved_answer'] = $feedback['improved_answer'];

            $answered = collect($questions)->filter(fn ($q) => filled($q['answer'] ?? null))->count();
            $scored = collect($questions)->filter(fn ($q) => isset($q['score']))->pluck('score');
            $avg = $scored->isNotEmpty() ? (int) round($scored->avg()) : 0;

            $interview['questions'] = $questions;
            $interview['current_index'] = min($index + 1, count($questions) - 1);
            $interview['answered_count'] = $answered;
            $interview['average_score'] = $avg;
            if ($answered >= count($questions)) {
                $interview['completed_at'] = now()->toIso8601String();
            }

            $cvReview->update(['interview' => $interview]);

            return redirect()
                ->route('cv-review.show', ['cvReview' => $cvReview, 'step' => 4])
                ->with('success', 'Jawaban dinilai. Skor: '.$feedback['score'].'/100.');
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Gagal menilai jawaban. Coba lagi.');
        }
    }

    private function authorizeReviewOwner(CvReview $cvReview): void
    {
        abort_unless(
            $cvReview->user_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );
    }
}
