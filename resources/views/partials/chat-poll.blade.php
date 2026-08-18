<script>
(function () {
    const thread = document.querySelector('[data-chat-thread]');
    if (! thread) return;

    const pollUrl = thread.dataset.pollUrl;
    let lastId = parseInt(thread.dataset.lastId || '0', 10);

    thread.scrollTop = thread.scrollHeight;

    const append = (msg) => {
        const wrap = document.createElement('div');
        wrap.className = 'flex ' + (msg.mine ? 'justify-end' : 'justify-start');
        const bubble = document.createElement('div');
        bubble.className = 'max-w-[80%] rounded-2xl px-3 py-2 text-sm ' + (msg.mine
            ? 'rounded-br-md bg-ink text-white'
            : 'rounded-bl-md border border-brand/15 bg-white text-ink shadow-sm');
        if (! msg.mine && msg.name) {
            const name = document.createElement('p');
            name.className = 'text-[11px] font-semibold text-brand-deeper';
            name.textContent = msg.name;
            bubble.appendChild(name);
        }
        const body = document.createElement('p');
        body.className = 'whitespace-pre-line';
        body.textContent = msg.body || '';
        const time = document.createElement('p');
        time.className = 'mt-1 text-right text-[10px] ' + (msg.mine ? 'text-white/70' : 'text-ink-soft');
        time.textContent = msg.time || '';
        bubble.append(body, time);
        wrap.appendChild(bubble);
        thread.appendChild(wrap);
        thread.scrollTop = thread.scrollHeight;
        lastId = msg.id;
    };

    setInterval(async () => {
        try {
            const res = await fetch(pollUrl + (pollUrl.includes('?') ? '&' : '?') + 'after=' + lastId, {
                headers: { 'Accept': 'application/json' },
            });
            if (! res.ok) return;
            const data = await res.json();
            (data.messages || []).forEach(append);
        } catch (e) {}
    }, 8000);
})();
</script>
