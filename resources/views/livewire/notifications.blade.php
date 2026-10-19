<div>
    <x-page-header title="Notifications" :back="route('dashboard')" />

    @php
        $tones = [
            'green' => 'var(--green-lt)', 'blue' => 'var(--primary-lt)', 'orange' => 'var(--orange-lt)',
        ];
    @endphp

    @forelse ($items as $n)
        <div style="display:flex; align-items:flex-start; gap:12px; padding:14px 16px; background:var(--surface); border:1px solid var(--border); border-radius:16px; box-shadow:var(--sh-sm); margin-bottom:10px;">
            <div style="width:40px; height:40px; border-radius:12px; background:{{ $tones[$n['tone']] ?? 'var(--surface-tint)' }}; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;">{{ $n['icon'] }}</div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:13.5px; font-weight:600; color:var(--text);">{{ $n['title'] }}</div>
                <div style="font-size:12px; color:var(--text-mid); margin-top:2px; line-height:1.5;">{{ $n['body'] }}</div>
            </div>
            <div style="font-size:10.5px; color:var(--text-meta); flex-shrink:0;">{{ $n['when'] }}</div>
        </div>
    @empty
        <div style="text-align:center; padding:56px 24px; color:var(--text-meta);">
            <div style="font-size:40px; margin-bottom:12px;">🔔</div>
            <div style="font-size:15px; font-weight:600; color:var(--text); margin-bottom:6px;">You're all caught up</div>
            <div style="font-size:12.5px; line-height:1.5;">Complete a Bit and your wins will show up here.</div>
        </div>
    @endforelse
</div>
