<?php

use Livewire\Component;
use App\Models\Book;
use App\Models\Category;

new class extends Component
{
    public $search = '';

    public function with(): array
    {
        return [
            'categories' => Category::withCount('books')->get(),
            // Fetch random books for the carousel
            'carouselBooks' => Book::whereNotNull('cover_image')->inRandomOrder()->limit(3)->get(),
            // Instantly fetch matching books if the user has typed something
            'searchResults' => strlen($this->search) >= 2
                ? Book::where('title', 'ilike', '%' . $this->search . '%')
                      ->orWhere('author', 'ilike', '%' . $this->search . '%')
                      ->limit(5)
                      ->get()
                : []
        ];
    }
};
?>

<div class="bg-gradient-to-b from-indigo-50 via-white to-gray-50 py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">

        <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-6xl">
            Your Next Great Adventure <span class="text-indigo-600">Awaits</span>
        </h1>

        <div class="py-5 px-30" x-data="{ isOpen: true }" @click.away="isOpen = false"
            class="mt-10 max-w-xl mx-auto relative z-50">
            <div class="relative rounded-2xl shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.250ms="search" @input="isOpen = true" @focus="isOpen = true" type="text"
                    placeholder="Search books instantly by title or author..."
                    class="block w-full rounded-2xl border-0 py-4 pl-12 pr-4 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
            </div>

            @if(count($searchResults) > 0)
            <div x-show="isOpen" x-transition
                class="absolute left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden text-left max-h-96 overflow-y-auto">
                <div class="p-2 space-y-1">
                    @foreach($searchResults as $result)
                    <a href="/books/{{ $result->slug }}"
                        class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50/60 transition-colors group">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($result->cover_image) }}" alt=""
                            class="w-10 h-14 object-cover rounded-md shadow-sm">
                        <div>
                            <h4
                                class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                {{ $result->title }}
                            </h4>
                            <p class="text-xs text-gray-500">by {{ $result->author }}</p>
                        </div>
                        <div class="ml-auto text-sm font-bold text-gray-900">
                            ${{ number_format($result->price, 2) }}
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @elseif(strlen($search) >= 2)
            <div x-show="isOpen"
                class="absolute left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 p-4 text-gray-500 text-sm">
                No books found matching "{{ $search }}"
            </div>
            @endif
        </div>

        <div x-data="{
                activeSlide: 0,
                slides: {{ $carouselBooks->count() }},
                autoRotate() {
                    setInterval(() => {
                        this.activeSlide = (this.activeSlide + 1) % this.slides;
                    }, 4000);
                }
            }" x-init="autoRotate()"
            class="relative overflow-hidden rounded-3xl bg-gray-900 shadow-2xl group max-w-4xl mx-auto aspect-[16/7]">
            <div class="relative w-full h-full">
                @foreach($carouselBooks as $index => $book)
                <div x-show="activeSlide === {{ $index }}" x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-500 absolute inset-0"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-8"
                    class="w-full h-full flex items-center justify-between p-8 md:p-12 text-white bg-gradient-to-r from-gray-950 via-gray-900 to-transparent">
                    <div class="w-1/2 space-y-4 z-10 text-left">
                        <span
                            class="inline-block text-xs font-bold tracking-wider text-indigo-400 uppercase bg-indigo-500/10 px-3 py-1 rounded-full">
                            {{ $book->category->name ?? 'Featured' }}
                        </span>
                        <h2 class="text-2xl md:text-4xl font-extrabold tracking-tight line-clamp-1">
                            {{ $book->title }}
                        </h2>
                        <p class="text-sm text-gray-300 line-clamp-2">
                            {{ $book->description }}
                        </p>
                        <div class="pt-2">
                            <a href="/books/{{ $book->slug }}"
                                class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition-all">
                                View Details
                            </a>
                        </div>
                    </div>

                    <div class="w-1/3 h-full flex items-center justify-center relative">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($book->cover_image) }}"
                            alt="{{ $book->title }}"
                            class="h-4/5 object-cover rounded-xl shadow-2xl rotate-3 transform transition-transform group-hover:rotate-0 duration-500">
                    </div>
                </div>
                @endforeach
            </div>

            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2 z-20">
                @foreach($carouselBooks as $index => $book)
                <button x-on:click="activeSlide = {{ $index }}" class="h-2 rounded-full transition-all duration-300"
                    x-bind:class="activeSlide === {{ $index }} ? 'w-6 bg-indigo-500' : 'w-2 bg-white/40'"></button>
                @endforeach
            </div>
        </div>

    </div>
</div>