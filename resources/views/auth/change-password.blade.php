@extends('layouts.app')
@section('title', 'Change password')
@section('content')
<div class="max-w-lg">
    <div class="card p-6 space-y-5">
        <form method="POST" action="{{ route('password.change') }}" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label for="current_password" class="label">Current password</label>
                <input id="current_password" type="password" name="current_password"
                       required autocomplete="current-password" @class(['input', 'ring-red-400 focus:ring-red-500' => $errors->has('current_password')])>
                @error('current_password')
                    <p class="mt-1.5 flex items-start gap-1.5 text-xs text-red-600"><x-icon name="alert" class="mt-px w-3.5 h-3.5" /> <span>{{ $message }}</span></p>
                @enderror
            </div>
            <div>
                <label for="password" class="label">New password</label>
                <input id="password" type="password" name="password"
                       required autocomplete="new-password" @class(['input', 'ring-red-400 focus:ring-red-500' => $errors->has('password')])>
                @error('password')
                    <p class="mt-1.5 flex items-start gap-1.5 text-xs text-red-600"><x-icon name="alert" class="mt-px w-3.5 h-3.5" /> <span>{{ $message }}</span></p>
                @enderror
                <p class="help">Minimum 10 characters.</p>
            </div>
            <div>
                <label for="password_confirmation" class="label">Confirm new password</label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       required autocomplete="new-password" class="input">
            </div>
            <div class="flex justify-end">
                <button class="btn btn-primary">Update password</button>
            </div>
        </form>
    </div>
</div>
@endsection
