@extends('layouts.guest')
@section('title', 'Confirm your password')
@section('subtitle', 'Please re-enter your password to continue.')
@section('content')
<form method="POST" action="{{ route('password.confirm') }}">
    @csrf
    <div class="mb-5">
        <label for="f-password" class="label">Your password</label>
        <input id="f-password" type="password" name="password" required autofocus
               autocomplete="current-password" @class(['input py-2.5', 'ring-red-400 focus:ring-red-500' => $errors->has('password')]) placeholder="••••••••">
        @error('password')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <button class="btn btn-primary w-full py-2.5">
        Confirm <x-icon name="arrow-right" class="w-4 h-4" />
    </button>
</form>
<p class="text-sm text-center text-slate-500 mt-8">
    <a href="{{ route('dashboard') }}" class="font-semibold text-brand-600 hover:text-brand-700">Back to dashboard</a>
</p>
@endsection
