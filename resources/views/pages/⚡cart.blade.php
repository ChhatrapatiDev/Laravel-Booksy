<?php

use Livewire\Component;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $cartItems = [];

    public function mount()
    {
        $this->loadCart();
    }

    private function loadCart()
    {
        $cart = Cart::with('cartItems.book')
            ->where('user_id', Auth::id())
            ->first();

        $this->cartItems = $cart ? $cart->cartItems : collect();
    }

    public function updateQuantity($itemId, $increment)
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) return;

        $item = $cart->cartItems()->find($itemId);
        if (!$item) return;

        $newQty = $item->quantity + $increment;

        if ($newQty <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $newQty]);
        }

        $this->loadCart();
    }

    public function removeItem($itemId)
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) return;

        $cart->cartItems()->where('id', $itemId)->delete();

        $this->loadCart();
    }
};
?>

<div class="w-full max-w-7xl mx-auto space-y-10">
    <div
        class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
                <span>Shopping Cart</span>
                <span
                    class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800">
                    {{ $cartItems->sum('quantity') }} {{ Str::plural('item', $cartItems->sum('quantity')) }}
                </span>
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review your collection before completing secure
                checkout.</p>
        </div>
        <a href="/" wire:navigate
            class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 group">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="w-4 h-4 transition-transform group-hover:-translate-x-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Continue Browsing
        </a>
    </div>

    @if (session()->has('success'))
    <div
        class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
        {{ session('success') }}
    </div>
    @endif

    @if($cartItems->isEmpty())
    <div
        class="bg-white dark:bg-slate-900/40 dark:backdrop-blur-md rounded-2xl p-16 text-center border border-slate-200/60 dark:border-slate-800/80 shadow-sm flex flex-col items-center justify-center space-y-6">
        <div
            class="h-16 w-16 bg-slate-100 dark:bg-slate-950 text-slate-400 dark:text-slate-600 rounded-2xl flex items-center justify-center border border-slate-200 dark:border-slate-800">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 10.5V6a3.75 3.75 0 1,0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0,1-1.12-1.243l1.264-12A1.125 1.125 0 0,1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1,1-.75 0 .375.375 0 0,1 .75 0Zm7.5 0a.375.375 0 1,1-.75 0 .375.375 0 0,1 .75 0Z" />
            </svg>
        </div>
        <div class="space-y-1 max-w-sm">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Your cart is pristine</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Looks like you haven't added any cosmic lore or titles
                to your cart shelf yet.</p>
        </div>
        <a href="/" wire:navigate
            class="px-6 py-3 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-lg shadow-indigo-600/20 transition-all active:scale-[0.98]">
            Discover Books
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        <div class="lg:col-span-2 space-y-4">
            <div
                class="bg-white dark:bg-slate-900/40 dark:backdrop-blur-md rounded-2xl border border-slate-200/60 dark:border-slate-800/80 divide-y divide-slate-100 dark:divide-slate-800/60 overflow-hidden shadow-sm">
                @foreach ($cartItems as $item)
                <div
                    class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 group transition-all duration-300">

                    <div class="flex items-center gap-5 w-full">
                        <div
                            class="w-20 h-26 sm:w-24 sm:h-32 bg-slate-100 dark:bg-slate-950 rounded-xl overflow-hidden flex-shrink-0 border border-slate-200/40 dark:border-slate-800/40 shadow-sm relative">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($item->book->cover_image) }}"
                                alt="{{ $item->book->title }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>

                        <div class="space-y-1.5 flex-1 min-w-0">
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                                {{ $item->book->category->name ?? 'Literature' }}
                            </span>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base sm:text-lg truncate">
                                {{ $item->book->title }}
                            </h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mb-2">by {{ $item->book->author }}</p>

                            <div class="flex items-center gap-4 pt-1">
                                <div
                                    class="inline-flex items-center p-1 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200/80 dark:border-slate-800/80 shadow-inner">
                                    <button wire:click="updateQuantity({{ $item->id }}, -1)"
                                        class="h-7 w-7 rounded-lg flex items-center justify-center text-slate-500 hover:bg-white dark:hover:bg-slate-900 hover:text-rose-500 transition-all active:scale-90">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                        </svg>
                                    </button>

                                    <span
                                        class="px-3 text-xs font-bold text-slate-800 dark:text-slate-200 min-w-8 text-center select-none">
                                        {{ $item->quantity }}
                                    </span>

                                    <button wire:click="updateQuantity({{ $item->id }}, 1)"
                                        class="h-7 w-7 rounded-lg flex items-center justify-center text-slate-500 hover:bg-white dark:hover:bg-slate-900 hover:text-indigo-500 transition-all active:scale-90">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </button>
                                </div>
                                <span class="text-xs text-slate-400 dark:text-slate-500 hidden sm:inline">
                                    × ${{ number_format($item->book->price, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-between w-full sm:w-auto h-auto sm:h-32 pt-4 sm:pt-0 border-t sm:border-0 border-slate-100 dark:border-slate-800/60">
                        <div class="text-left sm:text-right">
                            <span
                                class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Subtotal</span>
                            <span class="font-extrabold text-slate-900 dark:text-white text-lg sm:text-xl">
                                ${{ number_format($item->subtotal, 2) }}
                            </span>
                        </div>

                        <button wire:click="removeItem({{ $item->id }})" wire:loading.attr="disabled"
                            class="text-xs font-medium text-slate-400 dark:text-slate-500 hover:text-rose-500 dark:hover:text-rose-400 hover:underline flex items-center gap-1.5 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            <span>Remove</span>
                        </button>
                    </div>

                </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <div
                class="bg-white dark:bg-slate-900/40 dark:backdrop-blur-md rounded-2xl border border-slate-200/60 dark:border-slate-800/80 p-6 shadow-sm space-y-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Order Summary</h3>

                <div class="space-y-3.5 text-sm border-b border-slate-100 dark:border-slate-800/60 pb-5">
                    <div class="flex justify-between text-slate-500 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">${{
                            number_format($cartItems->sum->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-500 dark:text-slate-400">
                        <span>Shipping</span>
                        <span
                            class="text-emerald-500 dark:text-emerald-400 font-semibold uppercase text-xs tracking-wider bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded">Free</span>
                    </div>
                </div>

                <div class="flex justify-between items-baseline">
                    <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Grand Total</span>
                    <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        ${{ number_format($cartItems->sum->subtotal, 2) }}
                    </span>
                </div>

                <a href="/checkout" wire:navigate
                    class="w-full flex items-center justify-center px-6 py-3.5 font-semibold text-sm text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-lg shadow-indigo-600/20 transition-all active:scale-[0.98]">
                    Proceed to Checkout
                </a>

                <div class="flex items-center justify-center gap-2 text-[11px] text-slate-400 dark:text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-3.5 h-3.5 text-emerald-500">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    <span>Encrypted SSL Secure Checkout Connection</span>
                </div>
            </div>
        </div>

    </div>
    @endif
</div>