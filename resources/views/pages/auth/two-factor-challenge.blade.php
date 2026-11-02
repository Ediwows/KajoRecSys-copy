<x-layouts::auth :title="__('Two-factor authentication')">
    <div class="flex flex-col gap-6">
        <div
            class="relative w-full h-auto"
            x-cloak
            x-data="{
                showRecoveryInput: @js($errors->has('recovery_code')),
                code: '',
                recovery_code: '',
                focusOtp() {
                    this.$nextTick(() => this.$refs.otp?.querySelector('input')?.focus());
                },
                init() {
                    if (! this.showRecoveryInput) {
                        this.focusOtp();
                    }
                },
                toggleInput() {
                    this.showRecoveryInput = !this.showRecoveryInput;

                    this.code = '';
                    this.recovery_code = '';

                    $nextTick(() => {
                        this.showRecoveryInput
                            ? this.$refs.recovery_code?.focus()
                            : this.focusOtp();
                    });
                },
            }"
        >
            <div x-show="!showRecoveryInput">
                <x-auth-header
                    :title="__('Authentication code')"
                    :description="__('Enter your authenticator code, or receive a one-time code at your registered email address.')"
                />
            </div>

            <div x-show="showRecoveryInput">
                <x-auth-header
                    :title="__('Recovery code')"
                    :description="__('Please confirm access to your account by entering one of your emergency recovery codes.')"
                />
            </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}">
                @csrf

                <div class="space-y-5 text-center">
                    <div x-show="!showRecoveryInput">
                        <div class="flex items-center justify-center my-5" x-ref="otp">
                            <flux:otp
                                x-model="code"
                                length="6"
                                name="code"
                                label="OTP Code"
                                label:sr-only
                                class="mx-auto"
                             />
                        </div>
                    </div>

                    <div x-show="showRecoveryInput">
                        <div class="my-5">
                            <flux:input
                                type="text"
                                name="recovery_code"
                                x-ref="recovery_code"
                                x-bind:required="showRecoveryInput"
                                autocomplete="one-time-code"
                                x-model="recovery_code"
                            />
                        </div>

                        @error('recovery_code')
                            <flux:text color="red">
                                {{ $message }}
                            </flux:text>
                        @enderror
                    </div>

                    <flux:button
                        variant="primary"
                        type="submit"
                        class="w-full"
                    >
                        {{ __('Continue') }}
                    </flux:button>
                </div>

                <div class="mt-5 space-x-0.5 text-sm leading-5 text-center">
                    <span class="opacity-50">{{ __('or you can') }}</span>
                    <div class="inline font-medium underline cursor-pointer opacity-80">
                        <span x-show="!showRecoveryInput" @click="toggleInput()">{{ __('login using a recovery code') }}</span>
                        <span x-show="showRecoveryInput" @click="toggleInput()">{{ __('login using an authentication code') }}</span>
                    </div>
                </div>
            </form>

            <div class="mt-6 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                @if (session('status'))
                    <flux:text class="mb-4 text-center font-medium !dark:text-green-400 !text-green-600">
                        {{ session('status') }}
                    </flux:text>
                @endif

                @if ($errors->has('otp'))
                    <flux:text color="red" class="mb-4 text-center">
                        {{ $errors->first('otp') }}
                    </flux:text>
                @endif

                @if (session('email_otp_sent'))
                    <form method="POST" action="{{ route('two-factor.email.verify') }}" class="mb-4 space-y-4">
                        @csrf
                        <flux:input name="otp" :label="__('Email code')" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" />
                        <flux:button variant="primary" type="submit" class="w-full">
                            {{ __('Sign in with email code') }}
                        </flux:button>
                    </form>
                @endif

                <form method="POST" action="{{ route('two-factor.email.send') }}">
                    @csrf
                    <flux:button variant="ghost" type="submit" class="w-full">
                        {{ __('Send a code to my email') }}
                    </flux:button>
                </form>
            </div>
        </div>
    </div>
</x-layouts::auth>
