<script>
(function () {
    const thread = document.querySelector('[data-chat-thread]');
    if (! thread) return;

    const pollUrl = thread.dataset.pollUrl;
    let lastId = parseInt(thread.dataset.lastId || '0', 10);

    const append = (msg) => {
        const wrap = document.createElement('div');
        wrap.className = 'flex ' + (msg.mine ? 'justify-end' : 'justify-start');
        const bubble = document.createElement('div');
        bubble.className = 'max-w-[80%] rounded-2xl px-4 py-2.5 text-sm ' + (msg.mine
            ? 'rounded-br-md bg-ink text-white'
            : 'rounded-bl-md border border-brand/15 bg-white text-ink shadow-sm');
        const name = document.createElement('p');
        name.className = 'text-[11px] font-semibold ' + (msg.mine ? 'text-white/60' : 'text-brand-deeper');
        name.textContent = msg.mine ? 'Kamu' : (msg.name || '');
        const body = document.createElement('p');
        body.className = 'mt-0.5 whitespace-pre-line';
        body.textContent = msg.body || '';
        const time = document.createElement('p');
        time.className = 'mt-1 text-[10px] opacity-60';
        time.textContent = msg.time || '';
        bubble.append(name, body, time);
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
