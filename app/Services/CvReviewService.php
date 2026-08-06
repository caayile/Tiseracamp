<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CvReviewService
{
    public function isConfigured(): bool
    {
        return filled(config('services.gemini.key')) || filled(config('services.openai.key'));
    }

    /**
     * @param  array{target_position?:string,company_name?:string,education_level?:string,preferred_field?:string,location?:string,experience_level?:string}  $context
     * @return array{score:int,summary:string,strengths:array,weaknesses:array,suggestions:array,points:array,career:array,provider:string}
     */
    public function review(UploadedFile $file, array $context = []): array
    {
        if (filled(config('services.gemini.key'))) {
            return $this->reviewWithGemini($file, $context);
        }

        if (filled(config('services.openai.key'))) {
            return $this->reviewWithOpenAi($file, $context);
        }

        throw new RuntimeException('API AI belum dikonfigurasi. Set GEMINI_API_KEY atau OPENAI_API_KEY di .env.');
    }

    private function reviewWithGemini(UploadedFile $file, array $context = []): array
    {
        $key = config('services.gemini.key');
        $models = array_values(array_unique(array_filter([
            config('services.gemini.model'),
            'gemini-2.5-flash',
            'gemini-2.5-flash-lite',
            'gemini-flash-latest',
        ])));

        $payload = [
            'contents' => [[
                'parts' => [
                    ['text' => $this->prompt($context)],
                    [
                        'inline_data' => [
                            'mime_type' => 'application/pdf',
                            'data' => base64_encode($file->get()),
                        ],
                    ],
                ],
            ]],
            'generationConfig' => [
                'temperature' => 0.3,
                'responseMimeType' => 'application/json',
            ],
        ];

        $lastStatus = null;
        $lastBody = null;

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";

            $response = Http::timeout(90)
                ->acceptJson()
                ->post($url, $payload);

            if ($response->successful()) {
                $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');
                $parsed = $this->parseJsonPayload($text);
                $parsed['provider'] = 'gemini';

                return $parsed;
            }

            $lastStatus = $response->status();
            $lastBody = $response->body();
            Log::warning('Gemini CV review failed', [
                'model' => $model,
                'status' => $lastStatus,
                'body' => $lastBody,
            ]);

            // Coba model lain kalau model tidak ada / deprecated.
            if (in_array($lastStatus, [404, 400], true)) {
                continue;
            }

            // Kuota habis: jangan spam model lain yang sama project-nya.
            if ($lastStatus === 429) {
                break;
            }
        }

        if ($lastStatus === 429) {
            throw new RuntimeException('Kuota Gemini habis / model tidak tersedia di free tier. Ganti GEMINI_MODEL=gemini-2.5-flash di .env, atau buat API key project baru di Google AI Studio, lalu jalankan php artisan config:clear.');
        }

        if (in_array($lastStatus, [401, 403], true)) {
            throw new RuntimeException('API key Gemini tidak valid. Buat ulang key di https://aistudio.google.com/apikey');
        }

        throw new RuntimeException('Gagal menghubungi Gemini. Coba lagi nanti.');
    }

    private function reviewWithOpenAi(UploadedFile $file, array $context = []): array
    {
        $key = config('services.openai.key');
        $model = config('services.openai.model', 'gpt-4o-mini');

        $textContent = $this->extractPdfTextFallback($file);

        $response = Http::timeout(90)
            ->withToken($key)
            ->acceptJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Kamu adalah reviewer CV profesional untuk mahasiswa Indonesia. Jawab hanya JSON valid sesuai skema.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->prompt($context)."\n\nIsi CV:\n".$textContent,
                    ],
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('OpenAI CV review failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Gagal menghubungi OpenAI. Coba lagi nanti.');
        }

        $text = data_get($response->json(), 'choices.0.message.content', '');
        $parsed = $this->parseJsonPayload($text);
        $parsed['provider'] = 'openai';

        return $parsed;
    }

    private function extractPdfTextFallback(UploadedFile $file): string
    {
        $raw = $file->get();
        // Best-effort extract readable strings from PDF binary for OpenAI text path.
        $chunks = [];
        if (preg_match_all('/\((\\\\.|[^\\\\)]){2,}\)/s', $raw, $matches)) {
            foreach ($matches[0] as $match) {
                $inner = trim($match, '()');
                $inner = stripcslashes($inner);
                $inner = preg_replace('/[^\P{C}\n\t]+/u', ' ', $inner) ?? $inner;
                $inner = trim($inner);
                if (mb_strlen($inner) >= 3) {
                    $chunks[] = $inner;
                }
            }
        }

        $text = trim(implode("\n", array_slice($chunks, 0, 400)));
        if (mb_strlen($text) < 80) {
            throw new RuntimeException('Tidak bisa membaca teks CV. Pakai GEMINI_API_KEY agar PDF dibaca langsung, atau unggah PDF berbasis teks.');
        }

        return mb_substr($text, 0, 12000);
    }

    private function prompt(array $context = []): string
    {
        $target = trim((string) ($context['target_position'] ?? '')) ?: 'belum ditentukan';
        $company = trim((string) ($context['company_name'] ?? '')) ?: 'belum ditentukan';
        $education = trim((string) ($context['education_level'] ?? '')) ?: 'belum ditentukan';
        $field = trim((string) ($context['preferred_field'] ?? '')) ?: 'belum ditentukan';
        $location = trim((string) ($context['location'] ?? '')) ?: 'belum ditentukan';
        $experience = trim((string) ($context['experience_level'] ?? '')) ?: 'belum ditentukan';

        return <<<PROMPT
Kamu adalah coach karier + reviewer CV profesional (gaya MySkill) untuk mahasiswa/fresh graduate Indonesia.
Bahasa: Indonesia. Penilaian jujur, spesifik, actionable.

Profil target user:
- Posisi / pekerjaan tujuan: {$target}
- Perusahaan tujuan (opsional): {$company}
- Jenjang pendidikan: {$education}
- Bidang/minat: {$field}
- Lokasi: {$location}
- Level pengalaman: {$experience}

Tugas:
1) Review CV per bagian (dengan skor).
2) Analisa kecocokan CV dengan posisi tujuan (karir & skill).

Kembalikan JSON dengan skema tepat:
{
  "score": 0-100,
  "summary": "ringkasan keseluruhan 2-3 kalimat",
  "strengths": ["kekuatan 1", "kekuatan 2", "kekuatan 3"],
  "weaknesses": ["kelemahan 1", "kelemahan 2", "kelemahan 3"],
  "points": [
    {
      "id": "identitas",
      "label": "A. Informasi Dasar & Kontak",
      "score": 0-100,
      "analysis": "analisa 2-4 kalimat",
      "hr_criteria": [{"title": "...", "description": "..."}],
      "suggestions": [{"title": "...", "detail": "...", "current": "...", "improved": "..."}]
    },
    {
      "id": "ringkasan",
      "label": "B. Ringkasan / Profil Profesional",
      "score": 0-100,
      "analysis": "...",
      "hr_criteria": [{"title": "...", "description": "..."}],
      "suggestions": [{"title": "...", "detail": "...", "current": "...", "improved": "..."}]
    },
    {
      "id": "pengalaman",
      "label": "C. Pengalaman & Pencapaian",
      "score": 0-100,
      "analysis": "...",
      "hr_criteria": [{"title": "...", "description": "..."}],
      "suggestions": [{"title": "...", "detail": "...", "current": "...", "improved": "..."}]
    },
    {
      "id": "pendidikan",
      "label": "D. Pendidikan",
      "score": 0-100,
      "analysis": "...",
      "hr_criteria": [{"title": "...", "description": "..."}],
      "suggestions": [{"title": "...", "detail": "...", "current": "...", "improved": "..."}]
    },
    {
      "id": "skill",
      "label": "E. Skill & Sertifikasi",
      "score": 0-100,
      "analysis": "...",
      "hr_criteria": [{"title": "...", "description": "..."}],
      "suggestions": [{"title": "...", "detail": "...", "current": "...", "improved": "..."}]
    },
    {
      "id": "format",
      "label": "F. Format, ATS & Keterbacaan",
      "score": 0-100,
      "analysis": "...",
      "hr_criteria": [{"title": "...", "description": "..."}],
      "suggestions": [{"title": "...", "detail": "...", "current": "...", "improved": "..."}]
    }
  ],
  "career": {
    "suggested_role": "nama peran yang paling cocok (boleh sama dengan target)",
    "match_score": 0-100,
    "alternatives": ["alternatif peran 1", "alternatif peran 2"],
    "note": "catatan motivasi singkat",
    "job_fit": {
      "score": 0-100,
      "analysis": "analisa kesesuaian deskripsi pekerjaan vs CV",
      "criteria": [
        {"title": "1. Nama kriteria", "status": "sudah menguasai|belum menguasai|tidak memiliki", "description": "penjelasan"}
      ]
    },
    "skill_fit": {
      "score": 0-100,
      "analysis": "analisa skill vs karier tujuan",
      "ideal": "kondisi ideal kandidat",
      "requirements": ["persyaratan utama 1", "persyaratan 2"],
      "tools": ["tool 1", "tool 2"],
      "gaps": [{"title": "judul gap", "detail": "penjelasan gap"}]
    },
    "experience_fit": {
      "score": 0-100,
      "analysis": "analisa pengalaman terkait karier tujuan",
      "ideal_conditions": ["kondisi ideal 1", "kondisi ideal 2"],
      "suggestions": [{"title": "judul saran", "detail": "detail saran"}]
    }
  }
}

Wajib isi ke-6 points dan object career. Minimal 2 saran per point. Minimal 3 criteria di job_fit.criteria. status criteria hanya: sudah menguasai, belum menguasai, atau tidak memiliki.
PROMPT;
    }

    private function parseJsonPayload(string $text): array
    {
        $text = trim($text);
        if (preg_match('/\{.*\}/s', $text, $m)) {
            $text = $m[0];
        }

        $data = json_decode($text, true);
        if (! is_array($data)) {
            throw new RuntimeException('Respons AI tidak valid. Coba unggah ulang.');
        }

        $score = max(0, min(100, (int) ($data['score'] ?? 0)));

        $defaultLabels = [
            'identitas' => 'A. Informasi Dasar & Kontak',
            'ringkasan' => 'B. Ringkasan / Profil Profesional',
            'pengalaman' => 'C. Pengalaman & Pencapaian',
            'pendidikan' => 'D. Pendidikan',
            'skill' => 'E. Skill & Sertifikasi',
            'format' => 'F. Format, ATS & Keterbacaan',
        ];

        $points = [];
        foreach ($data['points'] ?? [] as $index => $point) {
            if (! is_array($point)) {
                continue;
            }

            $id = (string) ($point['id'] ?? ('point_'.$index));
            $suggestions = [];
            foreach ($point['suggestions'] ?? [] as $suggestion) {
                if (is_string($suggestion)) {
                    $suggestions[] = [
                        'title' => $suggestion,
                        'detail' => '',
                        'current' => '',
                        'improved' => '',
                    ];
                    continue;
                }
                if (! is_array($suggestion)) {
                    continue;
                }
                $suggestions[] = [
                    'title' => (string) ($suggestion['title'] ?? 'Saran perbaikan'),
                    'detail' => (string) ($suggestion['detail'] ?? ''),
                    'current' => (string) ($suggestion['current'] ?? ''),
                    'improved' => (string) ($suggestion['improved'] ?? ''),
                ];
            }

            $hrCriteria = [];
            foreach ($point['hr_criteria'] ?? [] as $criterion) {
                if (! is_array($criterion)) {
                    continue;
                }
                $hrCriteria[] = [
                    'title' => (string) ($criterion['title'] ?? ''),
                    'description' => (string) ($criterion['description'] ?? ''),
                ];
            }

            $points[] = [
                'id' => $id,
                'label' => (string) ($point['label'] ?? ($defaultLabels[$id] ?? ucfirst($id))),
                'score' => max(0, min(100, (int) ($point['score'] ?? 0))),
                'analysis' => (string) ($point['analysis'] ?? ''),
                'hr_criteria' => $hrCriteria,
                'suggestions' => $suggestions,
            ];
        }

        // Backward compatible: convert old sections schema into points.
        if ($points === [] && is_array($data['sections'] ?? null)) {
            $legacyMap = [
                'format' => 'F. Format, ATS & Keterbacaan',
                'content' => 'B. Ringkasan / Profil Profesional',
                'ats' => 'F. Format, ATS & Keterbacaan',
                'impact' => 'C. Pengalaman & Pencapaian',
            ];
            foreach ($data['sections'] as $key => $section) {
                $points[] = [
                    'id' => (string) $key,
                    'label' => $legacyMap[$key] ?? ucfirst((string) $key),
                    'score' => max(0, min(100, (int) ($section['score'] ?? 0))),
                    'analysis' => (string) ($section['note'] ?? ''),
                    'hr_criteria' => [],
                    'suggestions' => [],
                ];
            }
        }

        if ($points === [] && $score > 0) {
            $points[] = [
                'id' => 'ringkasan',
                'label' => 'A. Ringkasan Review',
                'score' => $score,
                'analysis' => (string) ($data['summary'] ?? ''),
                'hr_criteria' => [],
                'suggestions' => array_map(
                    fn ($item) => ['title' => (string) $item, 'detail' => '', 'current' => '', 'improved' => ''],
                    $data['suggestions'] ?? []
                ),
            ];
        }

        return [
            'score' => $score,
            'summary' => (string) ($data['summary'] ?? ''),
            'strengths' => array_values(array_filter(array_map('strval', $data['strengths'] ?? []))),
            'weaknesses' => array_values(array_filter(array_map('strval', $data['weaknesses'] ?? []))),
            'suggestions' => array_values(array_filter(array_map('strval', $data['suggestions'] ?? []))),
            'points' => $points,
            'career' => $this->parseCareerPayload(is_array($data['career'] ?? null) ? $data['career'] : []),
        ];
    }

    /**
     * @param  array{target_position?:string,company_name?:string,education_level?:string,preferred_field?:string,location?:string,experience_level?:string,tone?:string,highlights?:string}  $context
     * @return array{subject:string,body:string,tips:array<int,string>,provider:string}
     */
    public function generateCoverLetter(?string $cvFilePath, array $context = []): array
    {
        $prompt = $this->coverLetterPrompt($context);
        $data = $this->requestJson($prompt, $cvFilePath);

        return [
            'subject' => trim((string) ($data['subject'] ?? '')),
            'body' => trim((string) ($data['body'] ?? '')),
            'tips' => array_values(array_filter(array_map('strval', $data['tips'] ?? []))),
            'provider' => (string) ($data['_provider'] ?? 'gemini'),
        ];
    }

    /**
     * @param  array{target_position?:string,company_name?:string,education_level?:string,preferred_field?:string,location?:string,experience_level?:string}  $context
     * @return array{questions:array<int,array{id:string,question:string,focus:string,tip:string}>,provider:string}
     */
    public function generateInterview(?string $cvFilePath, array $context = []): array
    {
        $prompt = $this->interviewPrompt($context);
        $data = $this->requestJson($prompt, $cvFilePath);

        $questions = [];
        foreach ($data['questions'] ?? [] as $index => $item) {
            if (! is_array($item)) {
                continue;
            }
            $questions[] = [
                'id' => (string) ($item['id'] ?? ('q'.($index + 1))),
                'question' => trim((string) ($item['question'] ?? '')),
                'focus' => trim((string) ($item['focus'] ?? 'umum')),
                'tip' => trim((string) ($item['tip'] ?? '')),
            ];
        }

        $questions = array_values(array_filter($questions, fn ($q) => $q['question'] !== ''));
        if ($questions === []) {
            throw new RuntimeException('AI tidak menghasilkan pertanyaan interview. Coba lagi.');
        }

        return [
            'questions' => array_slice($questions, 0, 5),
            'provider' => (string) ($data['_provider'] ?? 'gemini'),
        ];
    }

    /**
     * @param  array{question:string,focus?:string,tip?:string,answer:string,target_position?:string,company_name?:string}  $payload
     * @return array{score:int,feedback:string,improved_answer:string,provider:string}
     */
    public function evaluateInterviewAnswer(array $payload): array
    {
        $prompt = $this->interviewFeedbackPrompt($payload);
        $data = $this->requestJson($prompt, null);

        return [
            'score' => max(0, min(100, (int) ($data['score'] ?? 0))),
            'feedback' => trim((string) ($data['feedback'] ?? '')),
            'improved_answer' => trim((string) ($data['improved_answer'] ?? '')),
            'provider' => (string) ($data['_provider'] ?? 'gemini'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function requestJson(string $prompt, ?string $cvFilePath = null): array
    {
        if (filled(config('services.gemini.key'))) {
            $pdfBytes = null;
            if ($cvFilePath && Storage::disk(media_disk())->exists($cvFilePath)) {
                $pdfBytes = Storage::disk(media_disk())->get($cvFilePath);
            }

            $data = $this->geminiJson($prompt, $pdfBytes);
            $data['_provider'] = 'gemini';

            return $data;
        }

        if (filled(config('services.openai.key'))) {
            $data = $this->openAiJson($prompt);
            $data['_provider'] = 'openai';

            return $data;
        }

        throw new RuntimeException('API AI belum dikonfigurasi. Set GEMINI_API_KEY atau OPENAI_API_KEY di .env.');
    }

    /**
     * @return array<string, mixed>
     */
    private function geminiJson(string $prompt, ?string $pdfBytes = null): array
    {
        $key = config('services.gemini.key');
        $models = array_values(array_unique(array_filter([
            config('services.gemini.model'),
            'gemini-2.5-flash',
            'gemini-2.5-flash-lite',
            'gemini-flash-latest',
        ])));

        $parts = [['text' => $prompt]];
        if ($pdfBytes !== null && $pdfBytes !== '') {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => 'application/pdf',
                    'data' => base64_encode($pdfBytes),
                ],
            ];
        }

        $payload = [
            'contents' => [['parts' => $parts]],
            'generationConfig' => [
                'temperature' => 0.4,
                'responseMimeType' => 'application/json',
            ],
        ];

        $lastStatus = null;

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";
            $response = Http::timeout(90)->acceptJson()->post($url, $payload);

            if ($response->successful()) {
                $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');

                return $this->decodeJsonObject($text);
            }

            $lastStatus = $response->status();
            Log::warning('Gemini career AI failed', [
                'model' => $model,
                'status' => $lastStatus,
                'body' => $response->body(),
            ]);

            if (in_array($lastStatus, [404, 400], true)) {
                continue;
            }

            if ($lastStatus === 429) {
                break;
            }
        }

        if ($lastStatus === 429) {
            throw new RuntimeException('Kuota Gemini habis. Coba lagi nanti atau ganti API key.');
        }

        if (in_array($lastStatus, [401, 403], true)) {
            throw new RuntimeException('API key Gemini tidak valid.');
        }

        throw new RuntimeException('Gagal menghubungi Gemini. Coba lagi nanti.');
    }

    /**
     * @return array<string, mixed>
     */
    private function openAiJson(string $prompt): array
    {
        $key = config('services.openai.key');
        $model = config('services.openai.model', 'gpt-4o-mini');

        $response = Http::timeout(90)
            ->withToken($key)
            ->acceptJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'temperature' => 0.4,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Kamu adalah coach karier untuk mahasiswa Indonesia. Jawab hanya JSON valid.',
                    ],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('OpenAI career AI failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Gagal menghubungi OpenAI. Coba lagi nanti.');
        }

        $text = data_get($response->json(), 'choices.0.message.content', '');

        return $this->decodeJsonObject($text);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonObject(string $text): array
    {
        $text = trim($text);
        if (preg_match('/\{.*\}/s', $text, $m)) {
            $text = $m[0];
        }

        $data = json_decode($text, true);
        if (! is_array($data)) {
            throw new RuntimeException('Respons AI tidak valid. Coba lagi.');
        }

        return $data;
    }

    private function coverLetterPrompt(array $context): string
    {
        $target = trim((string) ($context['target_position'] ?? '')) ?: 'belum ditentukan';
        $company = trim((string) ($context['company_name'] ?? '')) ?: 'perusahaan tujuan';
        $education = trim((string) ($context['education_level'] ?? '')) ?: 'belum ditentukan';
        $field = trim((string) ($context['preferred_field'] ?? '')) ?: 'belum ditentukan';
        $location = trim((string) ($context['location'] ?? '')) ?: 'Indonesia';
        $experience = trim((string) ($context['experience_level'] ?? '')) ?: 'fresh graduate';
        $tone = trim((string) ($context['tone'] ?? 'profesional'));
        $highlights = trim((string) ($context['highlights'] ?? ''));
        $summary = trim((string) ($context['summary'] ?? ''));

        return <<<PROMPT
Kamu adalah penulis cover letter / surat lamaran kerja profesional untuk kandidat Indonesia (mahasiswa / fresh graduate).
Bahasa: Indonesia. Nada: {$tone}. Panjang: 250–350 kata. Spesifik, tidak klise.

Profil:
- Posisi tujuan: {$target}
- Perusahaan: {$company}
- Pendidikan: {$education}
- Bidang: {$field}
- Lokasi: {$location}
- Level: {$experience}
- Highlight tambahan dari user: {$highlights}
- Ringkasan review CV (jika ada): {$summary}

Jika ada PDF CV terlampir, sesuaikan isi surat dengan pengalaman & skill di CV.

Kembalikan JSON tepat:
{
  "subject": "subjek email singkat",
  "body": "isi surat lengkap (boleh pakai baris baru \\n), tanpa markdown",
  "tips": ["tips kirim 1", "tips kirim 2", "tips kirim 3"]
}
PROMPT;
    }

    private function interviewPrompt(array $context): string
    {
        $target = trim((string) ($context['target_position'] ?? '')) ?: 'posisi umum';
        $company = trim((string) ($context['company_name'] ?? '')) ?: 'perusahaan umum';
        $education = trim((string) ($context['education_level'] ?? '')) ?: 'belum ditentukan';
        $field = trim((string) ($context['preferred_field'] ?? '')) ?: 'belum ditentukan';
        $experience = trim((string) ($context['experience_level'] ?? '')) ?: 'fresh graduate';
        $summary = trim((string) ($context['summary'] ?? ''));

        return <<<PROMPT
Kamu adalah interviewer HR + hiring manager untuk posisi "{$target}" di "{$company}".
Buat 5 pertanyaan interview realistis (campuran: perkenalan, behavioral STAR, teknis/role-fit, motivasi, situasional).
Bahasa pertanyaan: Indonesia. Cocok untuk level {$experience}, pendidikan {$education}, bidang {$field}.
Ringkasan CV/review: {$summary}

Kembalikan JSON tepat:
{
  "questions": [
    {
      "id": "q1",
      "question": "teks pertanyaan",
      "focus": "perkenalan|behavioral|teknis|motivasi|situasional",
      "tip": "tips singkat menjawab (1 kalimat)"
    }
  ]
}
Wajib tepat 5 pertanyaan dengan id q1..q5.
PROMPT;
    }

    private function interviewFeedbackPrompt(array $payload): string
    {
        $question = trim((string) ($payload['question'] ?? ''));
        $focus = trim((string) ($payload['focus'] ?? 'umum'));
        $tip = trim((string) ($payload['tip'] ?? ''));
        $answer = trim((string) ($payload['answer'] ?? ''));
        $target = trim((string) ($payload['target_position'] ?? '')) ?: 'posisi tujuan';
        $company = trim((string) ($payload['company_name'] ?? '')) ?: 'perusahaan';

        return <<<PROMPT
Kamu adalah coach interview untuk kandidat Indonesia.
Nilai jawaban user untuk posisi "{$target}" di "{$company}".

Pertanyaan ({$focus}): {$question}
Tips ideal: {$tip}
Jawaban kandidat:
{$answer}

Kembalikan JSON tepat:
{
  "score": 0-100,
  "feedback": "2-4 kalimat feedback jujur & actionable dalam Bahasa Indonesia",
  "improved_answer": "contoh jawaban yang lebih baik (2-5 kalimat, Bahasa Indonesia)"
}
PROMPT;
    }

    private function parseCareerPayload(array $career): array
    {
        $criteria = [];
        foreach ($career['job_fit']['criteria'] ?? [] as $item) {
            if (! is_array($item)) {
                continue;
            }
            $status = (string) ($item['status'] ?? 'belum menguasai');
            if (! in_array($status, ['sudah menguasai', 'belum menguasai', 'tidak memiliki'], true)) {
                $status = 'belum menguasai';
            }
            $criteria[] = [
                'title' => (string) ($item['title'] ?? ''),
                'status' => $status,
                'description' => (string) ($item['description'] ?? ''),
            ];
        }

        $gaps = [];
        foreach ($career['skill_fit']['gaps'] ?? [] as $gap) {
            if (is_string($gap)) {
                $gaps[] = ['title' => $gap, 'detail' => ''];
                continue;
            }
            if (! is_array($gap)) {
                continue;
            }
            $gaps[] = [
                'title' => (string) ($gap['title'] ?? 'Skill gap'),
                'detail' => (string) ($gap['detail'] ?? ''),
            ];
        }

        $experienceSuggestions = [];
        foreach ($career['experience_fit']['suggestions'] ?? [] as $suggestion) {
            if (is_string($suggestion)) {
                $experienceSuggestions[] = ['title' => $suggestion, 'detail' => ''];
                continue;
            }
            if (! is_array($suggestion)) {
                continue;
            }
            $experienceSuggestions[] = [
                'title' => (string) ($suggestion['title'] ?? 'Saran'),
                'detail' => (string) ($suggestion['detail'] ?? ''),
            ];
        }

        return [
            'suggested_role' => (string) ($career['suggested_role'] ?? ''),
            'match_score' => max(0, min(100, (int) ($career['match_score'] ?? 0))),
            'alternatives' => array_values(array_filter(array_map('strval', $career['alternatives'] ?? []))),
            'note' => (string) ($career['note'] ?? ''),
            'job_fit' => [
                'score' => max(0, min(100, (int) ($career['job_fit']['score'] ?? 0))),
                'analysis' => (string) ($career['job_fit']['analysis'] ?? ''),
                'criteria' => $criteria,
            ],
            'skill_fit' => [
                'score' => max(0, min(100, (int) ($career['skill_fit']['score'] ?? 0))),
                'analysis' => (string) ($career['skill_fit']['analysis'] ?? ''),
                'ideal' => (string) ($career['skill_fit']['ideal'] ?? ''),
                'requirements' => array_values(array_filter(array_map('strval', $career['skill_fit']['requirements'] ?? []))),
                'tools' => array_values(array_filter(array_map('strval', $career['skill_fit']['tools'] ?? []))),
                'gaps' => $gaps,
            ],
            'experience_fit' => [
                'score' => max(0, min(100, (int) ($career['experience_fit']['score'] ?? 0))),
                'analysis' => (string) ($career['experience_fit']['analysis'] ?? ''),
                'ideal_conditions' => array_values(array_filter(array_map('strval', $career['experience_fit']['ideal_conditions'] ?? []))),
                'suggestions' => $experienceSuggestions,
            ],
        ];
    }
}
