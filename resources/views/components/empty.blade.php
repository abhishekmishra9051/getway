@props(['icon' => 'receipt', 'title', 'text' => null])
<div class="flex flex-col items-center justify-center text-center px-6 py-14">
    <div class="grid place-items-center w-12 h-12 rounded-xl bg-slate-100 text-slate-400 mb-4"><x-icon :name="$icon" class="w-6 h-6" /></div>
    <div class="font-semibold text-slate-900">{{ $title }}</div>
    @if ($text)<p class="text-sm text-slate-500 mt-1 max-w-sm">{{ $text }}</p>@endif
    @if ($slot->isNotEmpty())<div class="mt-5">{{ $slot }}</div>@endif
</div>
