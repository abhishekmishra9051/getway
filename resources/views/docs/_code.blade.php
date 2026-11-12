<div class="mt-5 rounded-xl bg-[#0d1117] ring-1 ring-white/10 overflow-hidden">
    <div class="flex items-center justify-between px-4 py-2.5 border-b border-white/10 bg-white/5">
        <div class="flex items-center gap-2">
            <div class="flex gap-1.5">
                <span class="w-3 h-3 rounded-full bg-red-500/70"></span>
                <span class="w-3 h-3 rounded-full bg-yellow-500/70"></span>
                <span class="w-3 h-3 rounded-full bg-green-500/70"></span>
            </div>
            <span class="text-xs font-medium text-slate-400 ml-1">{{ $title }}</span>
        </div>
        <button type="button" data-copy="{{ $code }}" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-white transition-colors">
            <x-icon name="copy" class="w-3.5 h-3.5" /><span data-copy-label>Copy</span>
        </button>
    </div>
    <pre class="p-5 text-[13px] leading-relaxed overflow-x-auto"><code class="language-{{ $lang ?? 'bash' }}">{{ $code }}</code></pre>
</div>
