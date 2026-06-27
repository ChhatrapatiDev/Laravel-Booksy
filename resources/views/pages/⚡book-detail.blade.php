<?php

use Livewire\Component;
use App\Models\Book;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public Book $book;
    public $quantity = 1; // Added to bind with the input field

    public function mount(Book $book)
    {
        $this->book = $book->load('category');
    }

    public function addToCart()
    {
        // 1. Force Login Check
        if (!Auth::check()) {
            session()->flash('error', 'Please log in to add items to your cart.');
            return redirect()->route('login');
        }

        // 2. Validate Quantity
        $validated = $this->validate([
            'quantity' => 'required|integer|min:1|max:' . $this->book->stock
        ]);

        // 3. Find or Create the User's Cart
        $cart = Cart::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        // 4. Check if this book is already in the cart
        $cartItem = $cart->cartItems()->where('book_id', $this->book->id)->first();

        if ($cartItem) {
            // Update quantity if it already exists
            $cartItem->update([
                'quantity' => $cartItem->quantity + $validated['quantity']
            ]);
        } else {
            // Create a new cart item record
            $cart->cartItems()->create([
                'book_id' => $this->book->id,
                'quantity' => $validated['quantity']
            ]);
        }

        // 5. Flash Message & Redirect
        session()->flash('success', '"' . $this->book->title . '" has been added to your cart!');

        return redirect()->route('cart');
    }
};
?>

<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
    <div class="mb-6">
        <a href="/" class="text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center gap-2">
            ← Back to Catalog
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden md:flex">
        <div class="md:w-1/3 bg-gray-100 aspect-square md:aspect-auto">
            <img src="{{ \Illuminate\Support\Facades\Storage::url($book->cover_image) }}" alt="{{ $book->title }}"
                class="w-full h-full object-cover">
        </div>

        <div class="p-8 md:w-2/3 flex flex-col justify-between">
            <div>
                <div
                    class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-indigo-600 mb-2">
                    <span>{{ $book->category->name ?? 'Uncategorized' }}</span>
                    <span class="text-gray-400 font-normal">by {{ $book->author }}</span>
                </div>

                <h1 class="text-3xl font-black text-gray-900 mb-4">{{ $book->title }}</h1>

                <p class="text-gray-600 leading-relaxed">{{ $book->description }}</p>
            </div>

            <div
                class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <span class="text-3xl font-black text-gray-900">${{ number_format($book->price, 2) }}</span>

                <div class="flex items-center gap-3">
                    <div class="flex flex-col">
                        <input type="number" wire:model="quantity" min="1" max="{{ $book->stock }}"
                            class="w-20 px-3 py-2.5 text-center border border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 @error('quantity') border-red-500 @enderror">
                        @error('quantity')
                        <span class="text-xs text-red-500 mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <button wire:click="addToCart"
                        class="inline-flex items-center px-6 py-3 text-base font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition-colors">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>