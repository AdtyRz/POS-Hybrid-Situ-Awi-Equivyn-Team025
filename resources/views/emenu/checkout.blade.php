@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Checkout Meja {{ $meja->kode_meja }}</h2>
<<<<<<< HEAD
    <form action="{{ route('emenu.checkout') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="meja" value="{{ $meja->id }}">
=======

    <form action="{{ route('emenu.checkout') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="meja" value="{{ $meja->id }}">
        
>>>>>>> cb5d76bcf80a6ac4a6967a7d33b1e8862486431a
        <label for="metode" class="block font-medium">Metode Pembayaran</label>
        <select name="metode" id="metode" required class="border rounded w-full p-2">
            <option value="tunai">Tunai</option>
            <option value="qris">QR‑IS</option>
        </select>
<<<<<<< HEAD
=======
        
>>>>>>> cb5d76bcf80a6ac4a6967a7d33b1e8862486431a
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Lanjutkan Pembayaran
        </button>
    </form>
</div>
@endsection
