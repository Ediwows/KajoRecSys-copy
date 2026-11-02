<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot password')" :description="__('Enter your registered email address to receive a one-time reset code')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        @if ($errors->any())
            <div role="alert" class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session()->has('password_reset_email'))
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                Enter the code sent to your registered email and choose a new password.
            </p>

            <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
                @csrf
                <flux:input name="otp" :label="__('6-digit code')" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" />
                <flux:input name="password" :label="__('New password')" type="password" required autocomplete="new-password" passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}" viewable />
                <flux:input name="password_confirmation" :label="__('Confirm new password')" type="password" required autocomplete="new-password" passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}" viewable />
                <flux:button variant="primary" type="submit" class="w-full">{{ __('Reset password') }}</flux:button>
            </form>

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('password_reset_email') }}">
                <flux:button variant="ghost" type="submit" class="w-full">{{ __('Send another code') }}</flux:button>
            </form>
        @else
            <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
                @csrf
                <flux:input
                    name="email"
                    :label="__('Email address')"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    :value="old('email')"
                    placeholder="email@example.com"
                />
                <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-otp-button">
                    {{ __('Send reset code') }}
                </flux:button>
            </form>
        @endif

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('Or, return to') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
