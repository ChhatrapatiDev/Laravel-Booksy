<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    // 1. Define the name property so wire:model works
    public string $name = '';
    public string $address_name = '';
    public string $address_line_1 = '';
    public string $address_line_2 = '';
    public string $city = '';
    public string $state = '';
    public string $country = '';
    public string $pincode = '';

    // 2. Load the user's current name when the component initializes
public function mount()
{
    $this->name = Auth::user()->name;

    $address = Auth::user()->addresses()->first();

    if ($address) {
        $this->address_name   = $address->name;
        $this->address_line_1 = $address->address_line_1;
        $this->address_line_2 = $address->address_line_2;
        $this->city           = $address->city;
        $this->state          = $address->state;
        $this->country        = $address->country;
        $this->pincode        = $address->pincode;
    }
}

public function updateAddress()
{
    $validated = $this->validate([
        'address_name'   => 'required|string|max:255',
        'address_line_1' => 'required|string|max:255',
        'address_line_2' => 'nullable|string|max:255',
        'city'           => 'required|string|max:100',
        'state'          => 'required|string|max:100',
        'country'        => 'required|string|max:100',
        'pincode'        => 'required|string|max:20',
    ]);

    Auth::user()->addresses()->updateOrCreate(
        [],
        [
            'name'            => $validated['address_name'],
            'address_line_1'  => $validated['address_line_1'],
            'address_line_2'  => $validated['address_line_2'],
            'city'            => $validated['city'],
            'state'           => $validated['state'],
            'country'         => $validated['country'],
            'pincode'         => $validated['pincode'],
        ]
    );

    session()->flash('success', 'Shipping address updated successfully.');
}

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return $this->redirectRoute('login', navigate: true);
    }

    // 3. Updated update method (no arguments needed!)
    public function updateProfile()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255|min:2',
        ]);

        $user = Auth::user();

        // Update database record
        $user->update([
            'name' => $validated['name']
        ]);

        session()->flash('success', 'Profile updated successfully.');
    }

    public function userAddress()
    {
        $addresses = Auth::user()->addresses()->first();

        return [
            'addresses' => $addresses,
        ];
    }
};
?>

<div class="w-full max-w-5xl mx-auto space-y-8">

    @if (session()->has('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50"
        role="alert">
        {{ session('success') }}
    </div>
    @endif

    <div
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Account Settings</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage your profile information and account
                configurations.</p>
        </div>

        <button wire:click="logout" wire:loading.attr="disabled"
            class="relative flex items-center justify-center px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all active:scale-[0.98] disabled:opacity-50">
            <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span>Sign Out</span>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-2 space-y-6">
            <form wire:submit="updateProfile"
                class="bg-white dark:bg-slate-900/50 dark:backdrop-blur-md border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Personal Details</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Full
                            Name</label>
                        <input wire:model="name" type="text"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-medium text-slate-900 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:focus:border-indigo-400 transition-all" />
                        @error('name') <span
                            class="block mt-1.5 text-xs font-medium text-rose-500 dark:text-rose-400">{{ $message
                            }}</span> @enderror
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Email
                            Address</label>
                        <div
                            class="px-4 py-3 bg-slate-100/80 dark:bg-slate-950/50 border border-slate-200/60 dark:border-slate-800/40 rounded-xl text-sm font-medium text-slate-400 dark:text-slate-500 select-none cursor-not-allowed">
                            {{ Auth::user()->email }}
                        </div>
                        <p class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500">Email addresses cannot be
                            modified at this time.</p>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800/60">
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-md shadow-indigo-600/10 transition-all active:scale-[0.98] disabled:opacity-50">
                        Save Changes
                    </button>
                </div>
            </form>

            <form wire:submit="updateAddress"
                class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-6">

                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                    Shipping Address
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider mb-2">
                            Recipient Name
                        </label>
                        <input wire:model="address_name" type="text"
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider mb-2">
                            Address Line 1
                        </label>
                        <input wire:model="address_line_1" type="text"
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider mb-2">
                            Address Line 2
                        </label>
                        <input wire:model="address_line_2" type="text"
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider mb-2">
                            City
                        </label>
                        <input wire:model="city" type="text"
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider mb-2">
                            State
                        </label>
                        <input wire:model="state" type="text"
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider mb-2">
                            Country
                        </label>
                        <input wire:model="country" type="text"
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider mb-2">
                            Pincode
                        </label>
                        <input wire:model="pincode" type="text"
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                    </div>

                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-md shadow-indigo-600/10 transition-all">
                        Save Address
                    </button>
                </div>

            </form>
        </div>

        <div class="space-y-6">
            <div
                class="bg-linear-to-br from-indigo-600 to-violet-700 rounded-2xl p-6 text-white shadow-xl shadow-indigo-600/10">
                <div
                    class="h-12 w-12 rounded-xl bg-white/10 flex items-center justify-center text-xl font-bold backdrop-blur-md mb-4">
                    {{ strtoupper(substr($name ?: Auth::user()->name, 0, 1)) }}
                </div>
                <h4 class="text-xl font-bold truncate">{{ $name ?: Auth::user()->name }}</h4>
                <p class="text-xs text-indigo-200 mt-1">Member since {{ Auth::user()->created_at?->format('F Y') ??
                    'Recent' }}</p>

                <div class="mt-6 pt-6 border-t border-white/10 flex justify-between text-sm text-indigo-100">
                    <span>Account Status</span>
                    <span class="font-medium text-white flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                        Active
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>