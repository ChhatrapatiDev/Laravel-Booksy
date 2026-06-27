<?php

use Livewire\Component;

new class extends Component
{
    public function isAdmin(): bool
{
    return auth()->check() && auth()->user()->hasRole('super_admin');
}
};
?>


<div>
    <header class="w-full border-b border-slate-900 bg-slate-950/80 backdrop-blur-md sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-6 h-16 flex items-center justify-between">

            <div class="flex items-center gap-3">
                <span
                    class="h-6 w-6 rounded-md bg-indigo-500 flex items-center justify-center text-white text-xs font-bold tracking-wider shadow-md shadow-indigo-500/20">A</span>
                <a href="/" wire:navigate class=" inline-flex text-xl font-semibold tracking-tight text-white">Aura<span
                        class="text-indigo-400">.</span>
                </a>
            </div>

            <nav class="flex items-center gap-8">
                <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-400">
                    <a href="/" wire:navigate class="transition-colors hover:text-white">Home</a>
                </div>

                @auth()
                <div class="flex items-center gap-4">
                    <a href="/cart" wire:navigate
                        class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Cart
                    </a>
                    <a href="/orders" wire:navigate
                        class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Orders
                    </a>
                    <a href="/profile" wire:navigate
                        class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Profile
                    </a>
                    @role('super_admin')
                    <a href="/admin" class="text-sm text-indigo-400 hover:text-white">
                        Admin
                    </a>
                    @endrole
                </div>
                @else
                <div class="flex items-center gap-4">
                    <a href="/login" wire:navigate
                        class="text-sm font-medium text-slate-400 hover:text-white transition-colors hidden sm:block">Log
                        in</a>
                    <a href="/register" wire:navigate
                        class="rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-indigo-500 hover:shadow-lg hover:shadow-indigo-600/20">
                        Get Started
                    </a>
                </div>
                @endauth

            </nav>

        </div>
    </header>
</div>