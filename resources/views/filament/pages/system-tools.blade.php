<x-filament-panels::page>
    <x-filament::section :icon="count($pending) ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'"
                         :icon-color="count($pending) ? 'warning' : 'success'">
        <x-slot name="heading">
            {{ count($pending) ? 'Database update available' : 'Database is up to date' }}
        </x-slot>
        <x-slot name="description">
            After uploading a new version of the website files, click <b>Update database</b> (top right). No SSH or terminal needed.
        </x-slot>

        @if (count($pending))
            <p style="margin-bottom:8px;font-size:.9rem">{{ count($pending) }} update(s) waiting:</p>
            <ul style="font-family:monospace;font-size:.82rem;line-height:1.7;list-style:disc;padding-left:20px">
                @foreach ($pending as $m)
                    <li>{{ $m }}</li>
                @endforeach
            </ul>
        @else
            <p style="font-size:.9rem;opacity:.8">All updates are installed. Nothing to do.</p>
        @endif
    </x-filament::section>

    @if ($output !== '')
        <x-filament::section heading="Last result" icon="heroicon-o-command-line">
            <pre style="white-space:pre-wrap;font-size:.8rem;line-height:1.55;background:rgba(15,23,42,.92);color:#e2e8f0;padding:14px 16px;border-radius:10px;max-height:360px;overflow:auto">{{ $output }}</pre>
        </x-filament::section>
    @endif

    <x-filament::section heading="System information" icon="heroicon-o-server-stack">
        <dl style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px 24px">
            @foreach ($info as $label => $value)
                <div>
                    <dt style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;opacity:.6">{{ $label }}</dt>
                    <dd style="font-weight:600;font-size:.95rem">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </x-filament::section>

    <x-filament::section heading="How to update the website later" icon="heroicon-o-question-mark-circle" collapsible collapsed>
        <ol style="list-style:decimal;padding-left:20px;font-size:.9rem;line-height:1.8">
            <li>Click <b>Download backup</b> (keeps a copy of all content and enquiries).</li>
            <li>Upload the new files with Hostinger File Manager (do <b>not</b> replace the <code>.env</code> file or the <code>public/uploads</code> folder).</li>
            <li>Come back here and click <b>Update database</b>, then <b>Clear cache</b>.</li>
        </ol>
    </x-filament::section>
</x-filament-panels::page>
