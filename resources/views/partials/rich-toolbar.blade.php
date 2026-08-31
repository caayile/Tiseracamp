<div class="flex flex-wrap items-center gap-0.5 border-b border-ink/10 bg-slate-50 p-2" data-rich-toolbar>
    <button type="button" data-rich-command="undo" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-ink transition hover:bg-brand/15" title="Undo (Ctrl+Z)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
            <path d="m9 14-5-5 5-5"/>
            <path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"/>
        </svg>
    </button>
    <button type="button" data-rich-command="redo" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-ink transition hover:bg-brand/15" title="Redo (Ctrl+Y)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
            <path d="m15 14 5-5-5-5"/>
            <path d="M20 9H9.5A5.5 5.5 0 0 0 4 14.5v0A5.5 5.5 0 0 0 9.5 20H13"/>
        </svg>
    </button>

    <span class="mx-1 h-6 w-px bg-ink/10"></span>

    <button type="button" data-rich-command="bold" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 font-bold text-ink transition hover:bg-brand/15" title="Bold (Ctrl+B)">B</button>
    <button type="button" data-rich-command="italic" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 italic text-ink transition hover:bg-brand/15" title="Italic (Ctrl+I)">I</button>
    <button type="button" data-rich-command="underline" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-ink underline transition hover:bg-brand/15" title="Underline (Ctrl+U)">U</button>
    <button type="button" data-rich-command="strikeThrough" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-ink transition hover:bg-brand/15" title="Strikethrough">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
            <path d="M16 4H9a3 3 0 0 0-2.83 4"/>
            <path d="M14 12a4 4 0 0 1 0 8H6"/>
            <path d="M4 12h16"/>
        </svg>
    </button>

    <span class="mx-1 h-6 w-px bg-ink/10"></span>

    <button type="button" data-rich-link class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-ink transition hover:bg-brand/15" title="Tautan">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
        </svg>
    </button>
    <button type="button" data-rich-image-btn class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-ink transition hover:bg-brand/15" title="Sisipkan gambar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
            <circle cx="9" cy="9" r="2"/>
            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
        </svg>
    </button>
    <input type="file" data-rich-image accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" class="hidden">

    <span class="mx-1 h-6 w-px bg-ink/10"></span>

    <button type="button" data-rich-command="formatBlock" data-rich-value="BLOCKQUOTE" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-ink transition hover:bg-brand/15" title="Kutipan / Blockquote">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
            <path d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"/>
            <path d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"/>
        </svg>
    </button>
    <button type="button" data-rich-command="formatBlock" data-rich-value="PRE" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-ink transition hover:bg-brand/15" title="Blok kode">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
            <polyline points="16 18 22 12 16 6"/>
            <polyline points="8 6 2 12 8 18"/>
        </svg>
    </button>
    <button type="button" data-rich-code class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 font-mono text-xs font-bold text-ink transition hover:bg-brand/15" title="Kode inline">&lt;/&gt;</button>

    <span class="mx-1 h-6 w-px bg-ink/10"></span>

    <button type="button" data-rich-command="insertUnorderedList" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-ink transition hover:bg-brand/15" title="Daftar poin (bullets)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
            <path d="M3 12h.01"/>
            <path d="M3 18h.01"/>
            <path d="M3 6h.01"/>
            <path d="M8 12h13"/>
            <path d="M8 18h13"/>
            <path d="M8 6h13"/>
        </svg>
    </button>
    <button type="button" data-rich-command="insertOrderedList" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-ink transition hover:bg-brand/15" title="Daftar bernomor">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
            <path d="M10 12h11"/>
            <path d="M10 18h11"/>
            <path d="M10 6h11"/>
            <path d="M4 10h2"/>
            <path d="M4 6h1v4"/>
            <path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>
        </svg>
    </button>

    <span class="mx-1 h-6 w-px bg-ink/10"></span>

    <select data-rich-size class="rounded-lg border border-ink/10 bg-white px-2 py-1.5 text-xs text-ink outline-none focus:border-brand">
        <option value="">Ukuran font</option>
        <option value="2">Kecil</option>
        <option value="3">Normal</option>
        <option value="4">Sedang</option>
        <option value="5">Besar</option>
        <option value="6">Sangat besar</option>
    </select>
</div>