@php
    $score = (int) ($result['score'] ?? 0);
    $color = $score >= 80 ? '#16a34a' : ($score >= 50 ? '#d97706' : '#dc2626');
    $label = $score >= 80 ? 'Good' : ($score >= 50 ? 'Needs improvement' : 'Poor');
    $deg = round($score * 3.6);
    $url = rtrim(url('/'), '/') . (($result['url'] ?? '') === 'home' ? '' : '/' . ($result['url'] ?? ''));
    $icons = ['good' => ['✔', '#16a34a'], 'ok' => ['!', '#d97706'], 'bad' => ['✖', '#dc2626']];
@endphp
<div style="display:flex;flex-direction:column;gap:18px">
    <div style="display:flex;align-items:center;gap:16px">
        <div style="width:92px;height:92px;border-radius:50%;flex:none;background:conic-gradient({{ $color }} {{ $deg }}deg, rgba(148,163,184,.25) 0);display:grid;place-items:center">
            <div style="width:72px;height:72px;border-radius:50%;background:var(--color-white, #fff);display:grid;place-items:center" class="dark:bg-gray-900">
                <span style="font-size:1.6rem;font-weight:800;color:{{ $color }}">{{ $score }}</span>
            </div>
        </div>
        <div>
            <div style="font-weight:700;font-size:1.05rem;color:{{ $color }}">{{ $label }}</div>
            <div style="font-size:.85rem;opacity:.75">SEO score out of 100 · {{ $result['words'] ?? 0 }} words on page</div>
            <div style="font-size:.78rem;opacity:.6;margin-top:2px">Updates as you edit. Save the page to store the score.</div>
        </div>
    </div>

    <div>
        <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;opacity:.6;margin-bottom:6px">Google preview</div>
        <div style="border:1px solid rgba(148,163,184,.4);border-radius:12px;padding:12px 14px;background:rgba(148,163,184,.06)">
            <div style="font-size:.78rem;color:#4d5156;opacity:.9;word-break:break-all" class="dark:text-gray-400">{{ $url }}</div>
            <div style="font-size:1.08rem;color:#1a0dab;line-height:1.3;margin:3px 0" class="dark:text-blue-400">{{ \Illuminate\Support\Str::limit($result['title'] ?? '', 62) }}</div>
            <div style="font-size:.85rem;color:#4d5156;line-height:1.45" class="dark:text-gray-300">{{ \Illuminate\Support\Str::limit(($result['description'] ?? '') ?: 'No meta description — Google will pick some text from the page.', 162) }}</div>
        </div>
    </div>

    <div>
        <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;opacity:.6;margin-bottom:6px">Checks</div>
        <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px">
            @foreach (($result['checks'] ?? []) as $c)
                @php [$ic, $col] = $icons[$c['status']] ?? $icons['bad']; @endphp
                <li style="display:flex;gap:10px;align-items:flex-start">
                    <span style="flex:none;width:20px;height:20px;border-radius:50%;background:{{ $col }};color:#fff;font-size:.7rem;font-weight:700;display:grid;place-items:center;margin-top:1px">{{ $ic }}</span>
                    <span style="font-size:.86rem;line-height:1.4">
                        <b>{{ $c['label'] }}</b>
                        @if (($c['max'] ?? 0) > 0)<span style="opacity:.55;font-size:.75rem">({{ $c['points'] }}/{{ $c['max'] }})</span>@endif
                        <br><span style="opacity:.8">{{ $c['msg'] }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
</div>
