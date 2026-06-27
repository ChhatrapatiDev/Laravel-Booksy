<?php

use Livewire\Component;
use App\Models\Book;
use App\Models\Cart;
use App\Models\Category;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithPagination;

    public $selectedCategory = null;

    public function updatingSelectedCategory() { $this->resetPage(); }

    public function selectCategory($id)
    {
        $this->selectedCategory = ($this->selectedCategory === $id) ? null : $id;
    }

    public function with(): array
    {
        $query = Book::with('category');

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        return [
            'books' => $query->latest()->paginate(9),
            'categories' => Category::all()
        ];
    }

    // FIX: Accept the $bookId directly into the function
    public function addToCart($bookId)
    {
        // 1. Force Login Check
        if (!Auth::check()) {
            session()->flash('error', 'Please log in to add items to your cart.');
            return $this->redirectRoute('login', navigate: true);
        }

        // 2. Fetch the specific book from the ID passed
        $book = Book::findOrFail($bookId);

        // 3. Find or Create the User's Cart
        $cart = Cart::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        // 4. Check if this book is already in the cart
        $cartItem = $cart->cartItems()->where('book_id', $book->id)->first();

        if ($cartItem) {
            // Update quantity if it already exists (defaulting to +1 since this is a quick action button)
            $cartItem->update([
                'quantity' => $cartItem->quantity + 1
            ]);
        } else {
            // Create a new cart item record
            $cart->cartItems()->create([
                'book_id' => $book->id,
                'quantity' => 1
            ]);
        }

        session()->flash('success', '"' . $book->title . '" has been added to your cart!');
        return $this->redirectRoute('cart', navigate: true);
    }
};
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8 w-full">

    @if (session()->has('error'))
    <div
        class="p-4 mb-6 text-sm text-rose-800 rounded-xl bg-rose-50 dark:bg-rose-950/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50">
        {{ session('error') }}
    </div>
    @endif

    <div
        class="mb-10 flex items-center border-b border-slate-200 dark:border-slate-800 pb-6 overflow-x-auto scrollbar-none">
        <div class="flex gap-2 whitespace-nowrap">
            <button wire:click="$set('selectedCategory', null)"
                class="px-4 py-2 rounded-xl text-sm font-medium tracking-wide transition-all {{ is_null($selectedCategory) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                All Books
            </button>

            @foreach($categories as $category)
            <button wire:click="selectCategory({{ $category->id }})"
                class="px-4 py-2 rounded-xl text-sm font-medium tracking-wide border transition-all {{ $selectedCategory === $category->id ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-600/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                {{ $category->name }}
            </button>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-3 xl:gap-x-8">
        @foreach ($books as $book)
        <div
            class="group relative bg-white dark:bg-slate-900/40 dark:backdrop-blur-md rounded-2xl border border-slate-200/60 dark:border-slate-800/80 flex flex-col overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-slate-100/50 dark:hover:shadow-none hover:-translate-y-1">

            <div class="aspect-[4/5] bg-slate-100 dark:bg-slate-950 overflow-hidden relative">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($book->cover_image) }}" alt="{{ $book->title }}"
                    class="w-full h-full object-cover object-center transition-transform duration-500 group-hover:scale-105">
            </div>

            <div class="flex-1 p-6 flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div
                        class="flex items-center justify-between text-[11px] font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                        <span>{{ $book->category->name ?? 'Uncategorized' }}</span>
                        <span class="text-slate-400 dark:text-slate-500 font-normal normal-case">by {{ $book->author
                            }}</span>
                    </div>
                    <h3
                        class="text-lg font-bold text-slate-900 dark:text-white line-clamp-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <a href="/books/{{ $book->slug }}" wire:navigate>{{ $book->title }}</a>
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">{{
                        $book->description }}</p>
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-slate-100 dark:border-slate-800/50">
                    <span class="text-xl font-extrabold text-slate-900 dark:text-white">${{ number_format($book->price,
                        2) }}</span>

                    <button wire:click="addToCart({{ $book->id }})" wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2.5 text-xs font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/10 transition-all active:scale-95 disabled:opacity-50">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-12 dark:opacity-90">
        {{ $books->links() }}
    </div>
</div>