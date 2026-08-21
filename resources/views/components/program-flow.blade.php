@props([
    'title' => '1 Bulan Durasi Program',
    'steps' => null,
])

@php
    $defaultSteps = [
        [
            'number' => 1,
            'duration' => 'Est. 1 Week',
            'items' => [
                [
                    'title' => 'Onboarding & Learning Path',
                    'desc' => 'Perkenalan program, mentor, dan lingkungan kerja. Mulai mempelajari learning path sesuai divisi masing-masing.',
                ]
            ]
        ],
        [
            'number' => 2,
            'duration' => 'Est. 1 Week',
            'items' => [
                [
                    'title' => 'Learning & Project Development',
                    'desc' => 'Melanjutkan learning path dan mulai mengerjakan project dengan bimbingan mentor.',
                ]
            ]
        ],
        [
            'number' => 3,
            'duration' => 'Est. 1 Week',
            'items' => [
                [
                    'title' => 'Project Development & Review',
                    'desc' => 'Melanjutkan pengerjaan project dan melakukan review bersama mentor untuk mendapatkan feedback dan arahan.',
                ]
            ]
        ],
        [
            'number' => 4,
            'duration' => 'Est. 1 Week',
            'items' => [
                [
                    'title' => 'Final Project & Presentation',
                    'desc' => 'Menyelesaikan project, melakukan presentasi, dan mendapatkan sertifikat setelah menyelesaikan program.',
                ]
            ]
        ],
    ];

    $flowSteps = $steps ?? $defaultSteps;
@endphp

<style>
    .pf-container {
        width: 100%;
        max-width: 1024px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }
    .pf-title {
        font-size: 1.75rem;
        font-weight: 800;
        text-align: center;
        margin-bottom: 2.5rem;
        color: var(--color-ink, #0b1f2a);
    }
    .pf-timeline-wrapper {
        position: relative;
        width: 100%;
        margin-bottom: 2rem;
    }
    .pf-track-bg {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        left: 12.5%;
        right: 12.5%;
        height: 6px;
        background-color: #e5e7eb;
        border-radius: 9999px;
        z-index: 1;
    }
    .pf-track-teal {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        left: 12.5%;
        width: 75%;
        height: 6px;
        background-color: #00A896;
        border-radius: 9999px;
        z-index: 2;
    }
    .pf-steps-grid {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        position: relative;
        z-index: 4;
        width: 100%;
    }
    .pf-step-node {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .pf-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-color: #ffffff;
        border: 2px solid #00A896;
        color: #00A896;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    }
    .pf-columns-grid {
        display: flex !important;
        flex-direction: row !important;
        gap: 1.5rem !important;
        width: 100%;
        align-items: flex-start;
    }
    .pf-col {
        flex: 1;
        min-width: 0;
    }
    .pf-duration {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #9ca3af;
        margin-bottom: 0.5rem;
        display: block;
    }
    .pf-item-title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: var(--color-ink, #111827);
        margin-bottom: 0.375rem;
        line-height: 1.35;
    }
    .pf-item-desc {
        font-size: 0.8125rem;
        color: var(--color-ink-soft, #4b5563);
        line-height: 1.5;
    }
    .pf-item-group + .pf-item-group {
        margin-top: 1.25rem;
    }
</style>

<div class="pf-container">
    @if ($title)
        <h2 class="pf-title">
            {{ $title }}
        </h2>
    @endif

    <div style="overflow-x: auto; width: 100%;">
        <div style="min-width: 640px;">
            <!-- Timeline Horizontal Bar -->
            <div class="pf-timeline-wrapper" style="height: 50px;">
                <div class="pf-track-bg"></div>
                <div class="pf-track-teal"></div>

                <div class="pf-steps-grid" style="height: 100%;">
                    @foreach ($flowSteps as $step)
                        <div class="pf-step-node">
                            <div class="pf-circle">
                                {{ $step['number'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Content Grid Underneath Timeline (4 Columns) -->
            <div class="pf-columns-grid">
                @foreach ($flowSteps as $step)
                    <div class="pf-col">
                        <span class="pf-duration">
                            {{ $step['duration'] }}
                        </span>

                        <div>
                            @foreach ($step['items'] as $item)
                                <div class="pf-item-group">
                                    <h3 class="pf-item-title">
                                        {{ $item['title'] }}
                                    </h3>
                                    <p class="pf-item-desc">
                                        {{ $item['desc'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
