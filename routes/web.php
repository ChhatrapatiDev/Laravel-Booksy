<?php

use Illuminate\Support\Facades\Route;



Route::livewire('/login', 'pages::login')->name('login');

Route::livewire('/register', 'pages::register')->name('register');

Route::livewire('/profile', 'pages::profile')->name('profile');

Route::livewire('/', 'pages::home')->name('home');

Route::livewire('/books/{book:slug}', 'pages::book-detail')->name('books.show');

Route::livewire('/cart', 'pages::cart')->name('cart');

Route::livewire('/checkout', 'pages::checkout')->name('checkout');

Route::livewire('/orders', 'pages::orders')->name('orders');
