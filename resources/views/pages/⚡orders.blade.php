<?php

use Livewire\Component;
use App\Models\Order;
use App\OrderStatus;
use Barryvdh\DomPDF\Facade\Pdf;

new class extends Component
{
    public $orders = [];
    public $confirmingCancelOrderId = null;

    public function mount()
    {
        $this->loadOrders();
    }

    private function loadOrders()
    {
        $this->orders = auth()->user()
            ->orders()
            ->with(['orderItems.book', 'payment'])
            ->latest()
            ->get();
    }

    public function confirmCancelOrder($orderId)
    {
        $this->confirmingCancelOrderId = $orderId;
    }

    public function cancelOrder()
    {
        $order = auth()->user()
            ->orders()
            ->findOrFail($this->confirmingCancelOrderId);

        if ($order->status !== OrderStatus::Pending) {
            session()->flash('error', 'Only pending orders can be cancelled.');
            $this->confirmingCancelOrderId = null;
            return;
        }

        $order->update([
            'status' => OrderStatus::Cancelled
        ]);

        session()->flash('success', 'Order cancelled successfully.');

        $this->confirmingCancelOrderId = null;

        $this->loadOrders();
    }

    public function downloadInvoice($orderId)
    {
        $order = auth()->user()
            ->orders()
            ->with(['orderItems.book', 'payment'])
            ->findOrFail($orderId);

        $pdf = Pdf::loadView('pdf.invoice', [
            'order' => $order
        ]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "invoice-{$order->id}.pdf"
        );
    }
};

?>

<div class="min-h-screen bg-slate-950 text-white py-10">
    <div class="max-w-6xl mx-auto px-6">

        {{-- HEADER --}}
        <div class="mb-10">
            <a href="/" class="text-indigo-400 hover:text-indigo-300 text-sm">
                ← Back to Home
            </a>

            <h1 class="text-3xl font-bold mt-4">Your Orders</h1>
            <p class="text-slate-400 text-sm mt-1">
                Track your purchases and delivery status
            </p>
        </div>

        {{-- EMPTY --}}
        @if($orders->isEmpty())
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-10 text-center">
            <p class="text-slate-400">You haven’t placed any orders yet.</p>
        </div>
        @else

        <div class="space-y-6">

            @foreach($orders as $order)
            <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6">

                {{-- TOP --}}
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="font-semibold">Order #{{ $order->id }}</h2>
                        <p class="text-xs text-slate-400">
                            {{ $order->created_at->format('d M Y, h:i A') }}
                        </p>
                    </div>

                    {{-- STATUS --}}
                    @php $status = $order->status->value; @endphp

                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        @if($status === 'pending') bg-yellow-500/10 text-yellow-400
                        @elseif($status === 'processing') bg-blue-500/10 text-blue-400
                        @elseif($status === 'shipped') bg-indigo-500/10 text-indigo-400
                        @elseif($status === 'delivered') bg-emerald-500/10 text-emerald-400
                        @else bg-red-500/10 text-red-400 @endif">
                        {{ ucfirst($status) }}
                    </span>
                </div>

                {{-- ITEMS --}}
                <div class="mt-5 space-y-2 text-sm text-slate-300">
                    @foreach($order->orderItems as $item)
                    <div class="flex justify-between">
                        <span>{{ $item->book->title }} × {{ $item->quantity }}</span>
                        <span>${{ number_format($item->price * $item->quantity, 2) }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- FOOTER --}}
                <div class="mt-5 pt-4 border-t border-slate-800 flex justify-between">
                    <div class="text-sm text-slate-400">
                        Payment: <span class="text-white">{{ $order->payment?->method }}</span>
                    </div>

                    <div class="font-bold">
                        ${{ number_format($order->total_amount, 2) }}
                    </div>
                </div>

                {{-- ACTIONS (ONLY PENDING) --}}
                @if($order->status === \App\OrderStatus::Pending)
                <div class="flex gap-3 justify-center mt-4">

                    <button wire:click="confirmCancelOrder({{ $order->id }})"
                        class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-sm">
                        Cancel Order
                    </button>

                    <button wire:click="downloadInvoice({{ $order->id }})"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-sm">
                        Download Invoice
                    </button>

                </div>
                @endif

            </div>
            @endforeach

        </div>
        @endif
    </div>

    {{-- CONFIRM MODAL --}}
    @if($confirmingCancelOrderId)
    <div class="fixed inset-0 bg-black/70 flex items-center justify-center z-50">

        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-md">

            <h2 class="text-lg font-bold">Cancel Order?</h2>
            <p class="text-sm text-slate-400 mt-2">
                This action cannot be undone.
            </p>

            <div class="flex justify-end gap-3 mt-6">
                <button wire:click="$set('confirmingCancelOrderId', null)" class="px-4 py-2 bg-slate-700 rounded-lg">
                    No
                </button>

                <button wire:click="cancelOrder" class="px-4 py-2 bg-red-600 rounded-lg">
                    Yes, Cancel
                </button>
            </div>

        </div>
    </div>
    @endif
</div>