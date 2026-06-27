<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Payment;
use App\PaymentMethod;
use App\PaymentStatus;
use App\OrderStatus;

new class extends Component
{
    public $address;

    public $payment_method = 'cod';

    public function mount()
    {
        $this->address = Auth::user()->addresses()->first();
    }

    public function placeOrder()
    {
        $user = Auth::user();
        $cart = $user->cart()->with('cartItems.book')->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return;
        }

        if (!$this->address) {
            session()->flash('error', 'Please add an address.');
            return;
        }

        // 1. Create Order
$order = Order::create([
    'user_id' => $user->id,
    'total_amount' => $cart->cartItems->sum->subtotal,
    'status' => OrderStatus::Pending,

    'recipient_name' => $this->address->name,
    'address_line_1' => $this->address->address_line_1,
    'address_line_2' => $this->address->address_line_2,
    'city' => $this->address->city,
    'state' => $this->address->state,
    'country' => $this->address->country,
    'pincode' => $this->address->pincode,
]);

        // 2. Create Order Items
        foreach ($cart->cartItems as $item) {
            $order->orderItems()->create([
                'book_id' => $item->book_id,
                'quantity' => $item->quantity,
                'price' => $item->book->price,
            ]);
        }

        // 3. Payment (COD)
        $order->payment()->create([
            'method' => PaymentMethod::COD,
            'amount' => $order->total_amount,
            'status' => PaymentStatus::Pending,
        ]);

        // 4. Clear cart
        $cart->cartItems()->delete();

        // 5. Redirect
        return redirect()->route('orders', $order);
    }
};
?>

<div class="min-h-screen bg-slate-950 text-white py-10">
    <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- LEFT SIDE --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ADDRESS --}}
            <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-lg font-semibold mb-4">Shipping Address</h2>

                @if($address)
                <div class="space-y-1 text-sm text-slate-300">
                    <p class="font-medium text-white">{{ $address->name }}</p>
                    <p>{{ $address->address_line_1 }}</p>
                    <p>{{ $address->city }}, {{ $address->state }}</p>
                    <p>{{ $address->country }} - {{ $address->pincode }}</p>
                </div>
                @else
                <p class="text-slate-400">No address found. Please add one in profile.</p>
                @endif
            </div>

            {{-- PAYMENT --}}
            <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-lg font-semibold mb-4">Payment Method</h2>

                <div class="space-y-3 text-sm">

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" wire:model="payment_method" value="cod">
                        <span>Cash on Delivery</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer opacity-50">
                        <input type="radio" disabled>
                        <span>UPI (Coming soon)</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer opacity-50">
                        <input type="radio" disabled>
                        <span>Card (Coming soon)</span>
                    </label>

                </div>
            </div>

        </div>

        {{-- RIGHT SIDE --}}
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 h-fit">

            <h2 class="text-lg font-semibold mb-4">Order Summary</h2>

            @php
            $cart = auth()->user()->cart()->with('cartItems.book')->first();
            @endphp

            <div class="space-y-3 text-sm text-slate-300">
                @foreach($cart->cartItems as $item)
                <div class="flex justify-between gap-x-2">
                    <span>{{ $item->book->title }} × {{ $item->quantity }}</span>
                    <span>${{ $item->subtotal }}</span>
                </div>
                @endforeach
            </div>

            <div class="border-t border-slate-800 mt-4 pt-4 flex justify-between font-semibold">
                <span>Total</span>
                <span>${{ $cart->cartItems->sum->subtotal }}</span>
            </div>

            <button wire:click="placeOrder"
                class="w-full mt-6 bg-indigo-600 hover:bg-indigo-500 py-3 rounded-xl font-medium transition">
                Place Order (COD)
            </button>

        </div>

    </div>
</div>