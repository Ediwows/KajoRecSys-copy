<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            Use an email address you can access. We will send a verification code; verify it to use your account.
        </p>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <flux:input name="first_name" :label="__('First name')" :value="old('first_name')" type="text" required autofocus autocomplete="given-name" />
            <flux:input name="last_name" :label="__('Last name')" :value="old('last_name')" type="text" required autocomplete="family-name" />
            <flux:input name="middle_initial" :label="__('M.I. (optional)')" :value="old('middle_initial')" type="text" autocomplete="additional-name" placeholder="e.g. A" />
            <flux:input name="name_extension" :label="__('Name extension (optional)')" :value="old('name_extension')" type="text" placeholder="e.g. Jr., III" />

            <!-- Student / Teacher ID -->
            <flux:input
                name="id_number"
                :label="__('Student / Teacher ID')"
                :value="old('id_number')"
                type="text"
                required
                autocomplete="off"
                :placeholder="__('Enter your ID number')"
            />

            <!-- Gender -->
            <flux:select name="gender" label="Gender" required>
                <flux:select.option value="">Select gender</flux:select.option>
                <flux:select.option value="male" :selected="old('gender') === 'male'">Male</flux:select.option>
                <flux:select.option value="female" :selected="old('gender') === 'female'">Female</flux:select.option>
            </flux:select>

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
