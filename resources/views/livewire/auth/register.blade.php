<x-layouts::auth :title="__('Register')">

    <div class="flex flex-col gap-6">

        <x-auth-header
            :title="__('Create an Account')"
            :description="__('Enter your details below to create your KAJOREC account')"
        />

        <!-- Session Status -->
        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        <form
            method="POST"
            action="{{ route('register.store') }}"
            class="flex flex-col gap-6"
        >

            @csrf

            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Full Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Enter your full name')"
            />

         
           <!-- ID Number -->
            <flux:input
                name="id_number"
                :label="__('Student / Teacher ID')"
                :value="old('id_number')"
                type="text"
                required
                autocomplete="off"
                :placeholder="__('Enter your ID number')"
            />
            <flux:select name="gender" label="Gender" required>
                <flux:select.option value="male">Male</flux:select.option>
                <flux:select.option value="female">Female</flux:select.option>
            </flux:select>

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email Address')"
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
                :label="__('Confirm Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm your password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Create Account -->
            <div class="flex items-center justify-end">

                <flux:button
                    type="submit"
                    variant="primary"
                    class="w-full"
                    data-test="register-user-button"
                >
                    {{ __('Create Account') }}
                </flux:button>

            </div>

        </form>

        <!-- Login -->
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">

            <span>
                {{ __('Already have an account?') }}
            </span>

            <flux:link
                :href="route('login')"
                wire:navigate
            >
                {{ __('Log in') }}
            </flux:link>

        </div>

    </div>

</x-layouts::auth>