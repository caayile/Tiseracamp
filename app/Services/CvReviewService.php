<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Smalot\PdfParser\Parser;
use Throwable;

class CvReviewService
{
    public function isConfigured(): bool
    {
        return filled(config('services.gemini.key'))
            || filled(config('services.groq.key'))
            || filled(config('services.openai.key'));
    }

    /**
     * @param  array{target_position?:string,company_name?:string,education_level?:string,preferred_field?:string,location?:string,experience_level?:string}  $context
     * @return array{score:int,summary:string,strengths:array,weaknesses:array,suggestions:array,points:array,career:array,provider:string}
     */
    public function review(UploadedFile $file, array $context = []): array
    {
        $lastError = null;

        if (filled(config('services.gemini.key'))) {
            try {
                return $this->reviewWithGemini($file, $context);
            } catch (RuntimeException $e) {
                $lastError = $e;
                Log::warning('Gemini CV review fallback', ['error' => $e->getMessage()]);
            }
        }

        if (filled(config('services.groq.key'))) {
            try {
                return $this->reviewWithOpenAiCompatible(
                    $file,
                    $context,
                    (string) config('services.groq.key'),
                    (string) config('services.groq.model', 'llama-3.3-70b-versatile'),
                    rtrim((string) config('services.groq.base_url'), '/').'/chat/completions',
                    'groq',
                );
            } catch (RuntimeException $e) {
                $lastError = $e;
            }
        }

        if (filled(config('services.openai.key'))) {
            try {
                return $this->reviewWithOpenAiCompatible(
                    $file,
                    $context,
                    (string) config('services.openai.key'),
                    (string) config('services.openai.model', 'gpt-4o-mini'),
                    'https://api.openai.com/v1/chat/completions',
                    'openai',
                );
            } catch (RuntimeException $e) {
                $lastError = $e;
            }
        }

        throw $lastError ?? new RuntimeException('API AI belum dikonfigurasi. Set GEMINI_API_KEY, GROQ_API_KEY, atau OPENAI_API_KEY di .env.');
    }

    private function reviewWithGemini(UploadedFile $file, array $context = []): array
    {
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

        $text = $this->generateGeminiContent($payload, 'Gemini CV review');
        $parsed = $this->parseJsonPayload($text);
        $parsed['provider'] = 'gemini';

        return $parsed;
    }

    /**
     * @return list<string>
     */
    private function geminiModels(): array
    {
        $legacy = ['gemini-2.5-flash', 'gemini-2.5-flash-lite'];
        $preferred = [
            'gemini-3.6-flash',
            'gemini-3.5-flash-lite',
            'gemini-flash-latest',
        ];
        $configured = config('services.gemini.model');
        $head = (is_string($configured) && $configured !== '' && ! in_array($configured, $legacy, true))
            ? [$configured]
            : [];

        return array_values(array_unique(array_filter(array_merge($head, $preferred, $legacy))));
    }

    private function generateGeminiContent(array $payload, string $logContext = 'Gemini request'): string
    {
        $key = (string) config('services.gemini.key');
        $lastStatus = null;

        foreach ($this->geminiModels() as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

            try {
                $response = Http::timeout(50)
                    ->connectTimeout(12)
                    ->withHeaders(['x-goog-api-key' => $key])
                    ->acceptJson()
                    ->post($url, $payload);
            } catch (ConnectionException $e) {
                Log::warning($logContext.' timeout', ['model' => $model]);
                $lastStatus = 0;
                continue;
            }

            if ($response->successful()) {
                $text = $this->geminiResponseText($response->json());
                if ($text !== '') {
                    return $text;
                }

                Log::warning($logContext.' empty response', [
                    'model' => $model,
                    'finish' => data_get($response->json(), 'candidates.0.finishReason'),
                ]);
                continue;
            }

            $lastStatus = $response->status();
            Log::warning($logContext.' failed', [
                'model' => $model,
                'status' => $lastStatus,
                'body' => mb_substr((string) $response->body(), 0, 500),
            ]);

            if (in_array($lastStatus, [401, 403], true)) {
                throw new RuntimeException('API key Gemini tidak valid. Buat ulang key di https://aistudio.google.com/apikey');
            }

            // Model deprecated, overloaded, atau kuota — coba kandidat berikutnya.
            if (in_array($lastStatus, [400, 404, 429, 503], true)) {
                continue;
            }
        }

        if ($lastStatus === 429) {
            throw new RuntimeException('Kuota Gemini habis. Coba lagi nanti, atau set GROQ_API_KEY di .env sebagai cadangan.');
        }

        if ($lastStatus === 0) {
            throw new RuntimeException('Koneksi ke Gemini timeout. Coba lagi, atau set GROQ_API_KEY di .env sebagai cadangan.');
        }

        throw new RuntimeException('Gagal menghubungi Gemini. Coba lagi beberapa menit lagi.');
    }

    /**
     * @param  array<string, mixed>|null  $body
     */
    private function geminiResponseText(?array $body): string
    {
        $parts = data_get($body, 'candidates.0.content.parts', []);
        if (! is_array($parts)) {
            return '';
        }

        $text = '';
        foreach ($parts as $part) {
            if (! is_array($part) || ! empty($part['thought'])) {
                continue;
            }
            $text .= (string) ($part['text'] ?? '');
        }

        return trim($text);
    }

    private function reviewWithOpenAiCompatible(
        UploadedFile $file,
        array $context,
        string $key,
        string $model,
        string $url,
        string $provider,
    ): array {
        $textContent = $this->extractPdfTextFallback($file);
        $maxChars = $provider === 'groq' ? 6000 : 12000;
        $textContent = mb_substr($textContent, 0, $maxChars);

        $attempts = [
            ['strict_json' => $provider !== 'groq', 'cv' => $textContent, 'temperature' => 0.1],
            ['strict_json' => false, 'cv' => mb_substr($textContent, 0, 3500), 'temperature' => 0.05],
        ];

        $lastError = null;

        foreach ($attempts as $attempt) {
            try {
                $payload = [
                    'model' => $model,
                    'temperature' => $attempt['temperature'],
                    // Jangan melebihi TPM free tier llama-3.3-70b-versatile (12000 token/menit).
                    'max_tokens' => 4000,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Kamu adalah reviewer CV profesional untuk mahasiswa Indonesia. '
                                .'Balas HANYA satu objek JSON valid. '
                                .'Jangan ulang isi CV. Jangan markdown. Jangan teks di luar JSON. '
                                .'Karakter pertama harus { dan karakter terakhir harus }.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->prompt($context)
                                ."\n\n--- CV TEXT START ---\n".$attempt['cv']."\n--- CV TEXT END ---\n\n"
                                .'Review CV di atas. OUTPUT JSON SAJA sesuai skema.',
                        ],
                    ],
                ];

                if ($attempt['strict_json']) {
                    $payload['response_format'] = ['type' => 'json_object'];
                }

                $response = Http::timeout(120)
                    ->withToken($key)
                    ->acceptJson()
                    ->post($url, $payload);

                $body = $response->json();
                $text = (string) data_get($body, 'choices.0.message.content', '');
                $failed = (string) data_get($body, 'error.failed_generation', '');

                if ($response->successful() && $text !== '') {
                    $parsed = $this->parseJsonPayload($text);
                    $parsed['provider'] = $provider;

                    return $parsed;
                }

                if ($failed !== '') {
                    try {
                        $parsed = $this->parseJsonPayload($failed);
                        $parsed['provider'] = $provider;

                        return $parsed;
                    } catch (RuntimeException $e) {
                        $lastError = $e;
                    }
                }

                Log::warning(strtoupper($provider).' CV review failed', [
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 800),
                ]);

                if (in_array($response->status(), [401, 403], true)) {
                    throw new RuntimeException('API key '.strtoupper($provider).' tidak valid. Cek GROQ_API_KEY di .env.');
                }

                if ($response->status() === 429) {
                    throw new RuntimeException('Kuota '.strtoupper($provider).' habis / rate limit. Tunggu 1–2 menit lalu coba lagi.');
                }

                $lastError = new RuntimeException(
                    'AI '.strtoupper($provider).' gagal memproses CV (HTTP '.$response->status().'). Coba unggah ulang atau ganti file PDF berbasis teks.'
                );
            } catch (RuntimeException $e) {
                // Error kunci/kuota: jangan retry. Error parse (Respons AI tidak valid) boleh lanjut ke percobaan berikutnya.
                if (str_contains($e->getMessage(), 'API key') || str_contains($e->getMessage(), 'rate limit') || str_contains($e->getMessage(), 'Kuota')) {
                    throw $e;
                }
                $lastError = $e;
            } catch (Throwable $e) {
                Log::warning(strtoupper($provider).' CV review exception', ['message' => $e->getMessage()]);
                $lastError = new RuntimeException('Tidak bisa terhubung ke '.strtoupper($provider).'. Cek koneksi internet / API, lalu coba lagi.');
            }
        }

        throw $lastError ?? new RuntimeException('Gagal menghubungi '.strtoupper($provider).'. Coba lagi nanti.');
    }

    private function reviewWithOpenAi(UploadedFile $file, array $context = []): array
    {
        return $this->reviewWithOpenAiCompatible(
            $file,
            $context,
            (string) config('services.openai.key'),
            (string) config('services.openai.model', 'gpt-4o-mini'),
            'https://api.openai.com/v1/chat/completions',
            'openai',
        );
    }

    private function extractPdfTextFallback(UploadedFile $file): string
    {
        $text = '';

        // Prioritas: parser PDF asli (smalot/pdfparser). Hasilnya bersih untuk PDF Canva/dll.
        try {
            $pdf = (new Parser)->parseContent($file->get());
            $text = trim((string) $pdf->getText());
        } catch (Throwable $e) {
            Log::warning('PDF parser gagal, pakai regex fallback', ['error' => $e->getMessage()]);
            $text = '';
        }

        // Fallback: best-effort scan string literal dari biner PDF.
        if (mb_strlen($text) < 80) {
            $raw = $file->get();
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
        }

        if (mb_strlen($text) < 80) {
            throw new RuntimeException('Tidak bisa membaca teks CV. Unggah PDF berbasis teks (bukan hasil scan gambar), atau format ulang CV dari Canva/Word ke PDF standar.');
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
        $data = $this->extractJsonObject($text);
        if (! is_array($data)) {
            Log::warning('AI JSON parse failed', ['text' => mb_substr($text, 0, 2000)]);

            throw new RuntimeException('Respons AI tidak valid. Coba lagi; jika terus gagal, gunakan file CV PDF berbasis teks.');
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
        $lastError = null;

        if (filled(config('services.gemini.key'))) {
            try {
                $pdfBytes = null;
                if ($cvFilePath && Storage::disk(media_disk())->exists($cvFilePath)) {
                    $pdfBytes = Storage::disk(media_disk())->get($cvFilePath);
                }

                $data = $this->geminiJson($prompt, $pdfBytes);
                $data['_provider'] = 'gemini';

                return $data;
            } catch (RuntimeException $e) {
                $lastError = $e;
                Log::warning('Gemini career AI fallback', ['error' => $e->getMessage()]);
            }
        }

        if (filled(config('services.groq.key'))) {
            try {
                $data = $this->openAiCompatibleJson(
                    $prompt,
                    (string) config('services.groq.key'),
                    (string) config('services.groq.model', 'llama-3.3-70b-versatile'),
                    rtrim((string) config('services.groq.base_url'), '/').'/chat/completions',
                    'groq',
                );
                $data['_provider'] = 'groq';

                return $data;
            } catch (RuntimeException $e) {
                $lastError = $e;
            }
        }

        if (filled(config('services.openai.key'))) {
            try {
                $data = $this->openAiCompatibleJson(
                    $prompt,
                    (string) config('services.openai.key'),
                    (string) config('services.openai.model', 'gpt-4o-mini'),
                    'https://api.openai.com/v1/chat/completions',
                    'openai',
                );
                $data['_provider'] = 'openai';

                return $data;
            } catch (RuntimeException $e) {
                $lastError = $e;
            }
        }

        throw $lastError ?? new RuntimeException('API AI belum dikonfigurasi. Set GEMINI_API_KEY, GROQ_API_KEY, atau OPENAI_API_KEY di .env.');
    }

    /**
     * @return array<string, mixed>
     */
    private function geminiJson(string $prompt, ?string $pdfBytes = null): array
    {
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

        return $this->decodeJsonObject($this->generateGeminiContent($payload, 'Gemini career AI'));
    }

    /**
     * @return array<string, mixed>
     */
    private function openAiCompatibleJson(string $prompt, string $key, string $model, string $url, string $provider): array
    {
        $attempts = [
            ['strict_json' => $provider !== 'groq', 'temperature' => 0.2],
            ['strict_json' => false, 'temperature' => 0.1],
        ];

        $lastError = null;

        foreach ($attempts as $attempt) {
            $payload = [
                'model' => $model,
                'temperature' => $attempt['temperature'],
                // Jangan melebihi TPM free tier llama-3.3-70b-versatile (12000 token/menit).
                'max_tokens' => 4000,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Kamu adalah coach karier untuk mahasiswa Indonesia. '
                            .'Balas HANYA satu objek JSON valid. Jangan teks lain di luar JSON.',
                    ],
                    ['role' => 'user', 'content' => $prompt."\n\nBalas HANYA JSON."],
                ],
            ];

            if ($attempt['strict_json']) {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            try {
                $response = Http::timeout(120)
                    ->withToken($key)
                    ->acceptJson()
                    ->post($url, $payload);

                $body = $response->json();
                $text = (string) data_get($body, 'choices.0.message.content', '');
                $failed = (string) data_get($body, 'error.failed_generation', '');

                if ($response->successful() && $text !== '') {
                    return $this->decodeJsonObject($text);
                }

                if ($failed !== '') {
                    try {
                        return $this->decodeJsonObject($failed);
                    } catch (RuntimeException $e) {
                        $lastError = $e;
                    }
                }

                Log::warning(strtoupper($provider).' career AI failed', [
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 800),
                ]);

                if (in_array($response->status(), [401, 403], true)) {
                    throw new RuntimeException('API key '.strtoupper($provider).' tidak valid.');
                }

                if ($response->status() === 429) {
                    throw new RuntimeException('Kuota '.strtoupper($provider).' habis / rate limit. Tunggu sebentar lalu coba lagi.');
                }

                $lastError = new RuntimeException('Gagal menghubungi '.strtoupper($provider).'. Coba lagi nanti.');
            } catch (RuntimeException $e) {
                // Error kunci/kuota: jangan retry.
                if (str_contains($e->getMessage(), 'API key') || str_contains($e->getMessage(), 'rate limit') || str_contains($e->getMessage(), 'Kuota')) {
                    throw $e;
                }
                $lastError = $e;
            } catch (Throwable $e) {
                $lastError = new RuntimeException('Tidak bisa terhubung ke '.strtoupper($provider).'. Coba lagi nanti.');
            }
        }

        throw $lastError ?? new RuntimeException('Gagal menghubungi '.strtoupper($provider).'. Coba lagi nanti.');
    }

    private function openAiJson(string $prompt): array
    {
        return $this->openAiCompatibleJson(
            $prompt,
            (string) config('services.openai.key'),
            (string) config('services.openai.model', 'gpt-4o-mini'),
            'https://api.openai.com/v1/chat/completions',
            'openai',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonObject(string $text): array
    {
        $data = $this->extractJsonObject($text);
        if (! is_array($data)) {
            Log::warning('AI JSON parse failed (career/interview)', ['text' => mb_substr($text, 0, 2000)]);

            throw new RuntimeException('Respons AI tidak valid. Coba lagi.');
        }

        return $data;
    }

    /**
     * Ambil objek JSON dari teks (termasuk kasus Groq failed_generation yang ada teks CV di depan).
     *
     * @return array<string, mixed>|null
     */
    private function extractJsonObject(string $text): ?array
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        // Lepas pembungkus markdown ```json ... ``` jika model menambahkan.
        $text = preg_replace('/^\s*```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/```\s*$/', '', $text);
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        $direct = json_decode($text, true);
        if (is_array($direct)) {
            return $direct;
        }

        // Cari objek yang punya key "score" (hasil review) atau key umum lain.
        $anchors = ['"score"', '"subject"', '"questions"', '"feedback"', '"body"'];
        $start = false;
        foreach ($anchors as $anchor) {
            $pos = strpos($text, $anchor);
            if ($pos === false) {
                continue;
            }
            $brace = strrpos(substr($text, 0, $pos), '{');
            if ($brace !== false) {
                $start = $brace;
                break;
            }
        }

        if ($start === false) {
            $start = strpos($text, '{');
        }

        if ($start === false) {
            return null;
        }

        $slice = $this->balancedJsonSlice($text, $start);
        if ($slice !== null) {
            $decoded = json_decode($slice, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Coba perbaiki JSON yang terpotong (respons AI kepotong di tengah).
        $snippet = substr($text, $start);
        $repaired = $this->repairTruncatedJson($snippet);
        if ($repaired !== null) {
            return $repaired;
        }

        return null;
    }

    private function balancedJsonSlice(string $text, int $start): ?string
    {
        $depth = 0;
        $inString = false;
        $escape = false;
        $len = strlen($text);

        for ($i = $start; $i < $len; $i++) {
            $ch = $text[$i];

            if ($inString) {
                if ($escape) {
                    $escape = false;
                } elseif ($ch === '\\') {
                    $escape = true;
                } elseif ($ch === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($ch === '"') {
                $inString = true;

                continue;
            }

            if ($ch === '{' || $ch === '[') {
                $depth++;
            } elseif ($ch === '}' || $ch === ']') {
                $depth--;
                if ($depth === 0) {
                    return substr($text, $start, $i - $start + 1);
                }
            }
        }

        return null;
    }

    private function repairTruncatedJson(string $text): ?array
    {
        $stack = [];
        $inString = false;
        $escape = false;
        $len = strlen($text);
        $result = '';

        for ($i = 0; $i < $len; $i++) {
            $ch = $text[$i];

            if ($inString) {
                $result .= $ch;
                if ($escape) {
                    $escape = false;
                } elseif ($ch === '\\') {
                    $escape = true;
                } elseif ($ch === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($ch === '"') {
                $inString = true;
                $result .= $ch;

                continue;
            }

            if ($ch === '{' || $ch === '[') {
                $stack[] = $ch;
                $result .= $ch;

                continue;
            }

            if ($ch === '}' || $ch === ']') {
                if ($stack) {
                    array_pop($stack);
                    $result .= $ch;
                } else {
                    break;
                }

                continue;
            }

            $result .= $ch;
        }

        if ($inString) {
            $lastQuote = strrpos($result, '"');
            if ($lastQuote !== false) {
                $result = substr($result, 0, $lastQuote + 1);
            }
        }

        $result = rtrim($result);
        while ($result !== '' && in_array(substr($result, -1), [',', ':'], true)) {
            $result = rtrim(substr($result, 0, -1));
        }

        while ($stack) {
            $open = array_pop($stack);
            $result .= $open === '{' ? '}' : ']';
        }

        $decoded = json_decode($result, true);

        return is_array($decoded) ? $decoded : null;
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
