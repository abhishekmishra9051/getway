@php $left = \App\Models\Order::MAX_UTR_ATTEMPTS - $order->utr_attempts; @endphp
<form method="POST" action="{{ route('pay.utr', $order) }}" class="rounded-xl ring-1 ring-slate-200 p-4 text-left {{ $class ?? '' }}">
    @csrf
    @if ($order->review_note)
        <div class="mb-3 flex items-start gap-2 rounded-lg bg-red-50 ring-1 ring-red-200 px-3 py-2 text-xs text-red-700">
            <x-icon name="alert" class="w-4 h-4 mt-px" />
            <div><b>Couldn't verify your last UTR.</b> {{ $order->review_note }} Please check the 12-digit UTR in your UPI app and try again.</div>
        </div>
    @endif
    <label for="utr" class="text-sm font-semibold text-slate-900">Already paid? Enter your UTR</label>
    <p class="text-xs text-slate-500 mt-0.5 mb-3">Find the 12-digit <b>UTR / UPI Ref. No.</b> in your UPI app's transaction details.</p>
    <div class="flex gap-2">
        <input id="utr" name="utr" inputmode="numeric" autocomplete="off" maxlength="14" required value="{{ old('utr') }}"
            placeholder="e.g. 412345678901" @class(['input font-mono tracking-wider', 'ring-red-400 focus:ring-red-500' => $errors->has('utr')])>
        <button class="btn btn-dark shrink-0">Submit</button>
    </div>
    @error('utr')<p class="mt-1.5 flex items-start gap-1.5 text-xs text-red-600"><x-icon name="alert" class="mt-px w-3.5 h-3.5" /> <span>{{ $message }}</span></p>@enderror
    <p class="mt-2 text-[11px] text-slate-400">{{ $left }} {{ Str::plural('attempt', $left) }} left. The merchant confirms this UTR against their bank account before your order is marked paid.</p>
</form>
