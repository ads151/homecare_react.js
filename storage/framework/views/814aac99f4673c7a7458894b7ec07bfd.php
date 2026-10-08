<?php
    $score = (int) ($result['score'] ?? 0);
    $color = $score >= 80 ? '#16a34a' : ($score >= 50 ? '#d97706' : '#dc2626');
    $label = $score >= 80 ? 'Good' : ($score >= 50 ? 'Needs improvement' : 'Poor');
    $deg = round($score * 3.6);
    $url = rtrim(url('/'), '/') . (($result['url'] ?? '') === 'home' ? '' : '/' . ($result['url'] ?? ''));
    $icons = ['good' => ['✔', '#16a34a'], 'ok' => ['!', '#d97706'], 'bad' => ['✖', '#dc2626']];
?>
<div style="display:flex;flex-direction:column;gap:18px">
    <div style="display:flex;align-items:center;gap:16px">
        <div style="width:92px;height:92px;border-radius:50%;flex:none;background:conic-gradient(<?php echo e($color); ?> <?php echo e($deg); ?>deg, rgba(148,163,184,.25) 0);display:grid;place-items:center">
            <div style="width:72px;height:72px;border-radius:50%;background:var(--color-white, #fff);display:grid;place-items:center" class="dark:bg-gray-900">
                <span style="font-size:1.6rem;font-weight:800;color:<?php echo e($color); ?>"><?php echo e($score); ?></span>
            </div>
        </div>
        <div>
            <div style="font-weight:700;font-size:1.05rem;color:<?php echo e($color); ?>"><?php echo e($label); ?></div>
            <div style="font-size:.85rem;opacity:.75">SEO score out of 100 · <?php echo e($result['words'] ?? 0); ?> words on page</div>
            <div style="font-size:.78rem;opacity:.6;margin-top:2px">Updates as you edit. Save the page to store the score.</div>
        </div>
    </div>

    <div>
        <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;opacity:.6;margin-bottom:6px">Google preview</div>
        <div style="border:1px solid rgba(148,163,184,.4);border-radius:12px;padding:12px 14px;background:rgba(148,163,184,.06)">
            <div style="font-size:.78rem;color:#4d5156;opacity:.9;word-break:break-all" class="dark:text-gray-400"><?php echo e($url); ?></div>
            <div style="font-size:1.08rem;color:#1a0dab;line-height:1.3;margin:3px 0" class="dark:text-blue-400"><?php echo e(\Illuminate\Support\Str::limit($result['title'] ?? '', 62)); ?></div>
            <div style="font-size:.85rem;color:#4d5156;line-height:1.45" class="dark:text-gray-300"><?php echo e(\Illuminate\Support\Str::limit(($result['description'] ?? '') ?: 'No meta description — Google will pick some text from the page.', 162)); ?></div>
        </div>
    </div>

    <div>
        <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;opacity:.6;margin-bottom:6px">Checks</div>
        <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($result['checks'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php [$ic, $col] = $icons[$c['status']] ?? $icons['bad']; ?>
                <li style="display:flex;gap:10px;align-items:flex-start">
                    <span style="flex:none;width:20px;height:20px;border-radius:50%;background:<?php echo e($col); ?>;color:#fff;font-size:.7rem;font-weight:700;display:grid;place-items:center;margin-top:1px"><?php echo e($ic); ?></span>
                    <span style="font-size:.86rem;line-height:1.4">
                        <b><?php echo e($c['label']); ?></b>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($c['max'] ?? 0) > 0): ?><span style="opacity:.55;font-size:.75rem">(<?php echo e($c['points']); ?>/<?php echo e($c['max']); ?>)</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <br><span style="opacity:.8"><?php echo e($c['msg']); ?></span>
                    </span>
                </li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </ul>
    </div>
</div>
<?php /**PATH D:\Downloads\Home care\homecare-headless\backend\resources\views/filament/seo-panel.blade.php ENDPATH**/ ?>