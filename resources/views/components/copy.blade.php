@props(['text', 'label' => 'Copy'])
<button type="button" data-copy="{{ $text }}" {{ $attributes->merge(['class' => 'btn btn-secondary btn-sm shrink-0']) }}>
    <x-icon name="copy" class="w-4 h-4" /><span data-copy-label>{{ $label }}</span>
</button>
