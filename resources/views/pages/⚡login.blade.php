<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public string $email = '';
    public string $password = '';

    public function login()
    {
        $validated = $this->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($validated)) {
            // Fix: Properly regenerate the whole session ID for security
            session()->regenerate();

            session()->flash('success', 'Login successful.');
            return $this->redirectRoute('profile', navigate: true);
        }

        // Add an error if credentials don't match
        $this->addError('email', 'The provided credentials do not match our records.');
    }

    public function mount()
    {
        if (Auth::check()) {
            return $this->redirectRoute('profile', navigate: true);
        }
    }
};
?>

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Welcome back</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Please enter your details to sign in.</p>
    </div>

    <form wire:submit="login"
        class="space-y-5 bg-white dark:bg-slate-900/50 dark:backdrop-blur-md p-8 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-xl shadow-slate-100/50 dark:shadow-none">

        <div>
            <label
                class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Email
                Address</label>
            <input wire:model="email" type="email" autocomplete="email" placeholder="name@example.com"
                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:focus:border-indigo-400 transition-all text-slate-900 dark:text-white" />
            @error('email') <span class="block mt-1.5 text-xs font-medium text-rose-500 dark:text-rose-400">{{ $message
                }}</span> @enderror
        </div>

        <div>
            <div class="flex justify-between items-center mb-2">
                <label
                    class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Password</label>
                <a href="#" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Forgot?</a>
            </div>
            <input wire:model="password" type="password" placeholder="••••••••"
                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:focus:border-indigo-400 transition-all text-slate-900 dark:text-white" />
            @error('password') <span class="block mt-1.5 text-xs font-medium text-rose-500 dark:text-rose-400">{{
                $message }}</span> @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="w-full relative flex items-center justify-center px-6 py-3 font-medium text-sm text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-lg shadow-indigo-600/20 hover:shadow-indigo-500/30 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-wait">
            <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white absolute left-4" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span>Sign In</span>
        </button>
    </form>
</div>