@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null])
<div class="mb-5">
    <label for="f-{{ $name }}" class="label">{{ $label }}</label>
    <input id="f-{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $type === 'password' ? '' : old($name, $value) }}"
        @if ($errors->has($name)) aria-invalid="true" aria-describedby="err-{{ $name }}" @elseif ($help) aria-describedby="help-{{ $name }}" @endif
        {{ $attributes->class(['input', 'ring-red-400 focus:ring-red-500' => $errors->has($name)]) }}>
    @error($name)<p id="err-{{ $name }}" class="mt-1.5 flex items-start gap-1.5 text-xs text-red-600"><x-icon name="alert" class="mt-px w-3.5 h-3.5" /> <span>{{ $message }}</span></p>@else @if ($help)<p id="help-{{ $name }}" class="help">{{ $help }}</p>@endif @enderror
</div>
