@extends('layouts.admin')

@section('title', 'Cari Pesanan - Kasir')

@section('content')
<div class="max-w-2xl mx-auto p-6">
<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-6">
<h2 class="font-bold text-2xl text-forest-900 mb-4">Cari Pesanan untuk Bayar</h2>

<form method="GET" action="{{ route('kasir.pembayaran.cari') }}" class="space-y-4">
<div>
<label class="block text-sm font-medium text-stone-600 mb-1">Kode Pesanan / Kode Pembayaran</label>
<input type="text" name="kode_pembayaran" value="{{ request()->input('kode_pembayaran') ?? request()->input('kode_pesanan') }}"
       class="w-full px-4 py-3 rounded-xl border border-stone-200 focus:border-forest-500 focus:ring-2 focus:ring-forest-500/20 outline-none text-lg font-mono"
       placeholder="Contoh: SW-20261011123456-1 atau PAY-20261011123456-ABC123 (kode lama)" autofocus>
</div>
<button type="submit" class="w-full bg-forest-900 hover:bg-[#042616] text-white font-bold py-3 rounded-xl text-lg shadow-sm transition">
Cari Pesanan
</button>
</form>

@if(isset($error))
<div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
{{ $error }}
</div>
@endif
</div>

@if(isset($pesanan) && !isset($error))
<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
<div class="flex items-center justify-between mb-4">
<h2 class="font-bold text-xl text-forest-900">Detail Pesanan</h2>
<span class="px-3 py-1 rounded-full text-xs font-bold {{ $pesanan->status_pembayaran === 'pending_pembayaran' ? 'bg-gold-100 text-gold-700' : 'bg-forest-100 text-forest-700' }}">
{{ $pesanan->status_pembayaran }}
</span>
</div>

<div class="grid grid-cols-2 gap-4 mb-4 text-sm">
<div><span class="text-stone-500">Kode Pesanan / Bayar</span><p class="font-bold font-mono">{{ $pesanan->kode_pesanan }}</p></div>
<div><span class="text-stone-500">Meja</span><p class="font-bold">{{ $pesanan->meja->kode_meja }} ({{ $pesanan->meja->area }})</p></div>
<div><span class="text-stone-500">Metode</span><p class="font-bold">{{ ucfirst($pesanan->metode_pembayaran) }}</p></div>
<div><span class="text-stone-500">Status Bayar</span><p class="font-bold">{{ $pesanan->status_pembayaran }}</p></div>
<div><span class="text-stone-500">Status Pesanan</span><p class="font-bold">{{ $pesanan->status_pesanan }}</p></div>
</div>

<div class="space-y-2 mb-4">
@foreach($pesanan->detailPesanan as $item)
<div class="flex items-center justify-between py-2 border-b border-stone-100">
<div>
<p class="font-bold text-sm">{{ $item->jumlah }}x {{ $item->menu->nama_menu }}</p>
@if($item->catatan)
<p class="text-stone-400 text-xs">{{ $item->catatan }}</p>
@endif
</div>
<p class="font-bold text-sm">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p>
</div>
@endforeach
</div>

<div class="flex items-center justify-between border-t border-dashed border-stone-200 pt-3 mt-3">
<p class="font-bold text-lg">Total</p>
<p class="font-extrabold text-2xl text-forest-600">Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
</div>

@if($pesanan->status_pembayaran === 'pending_pembayaran')
<form method="POST" action="{{ route('kasir.pembayaran.store') }}" class="mt-6 space-y-4">
@csrf
<input type="hidden" name="kode_pesanan" value="{{ $pesanan->kode_pesanan }}">
<input type="hidden" name="metode_pembayaran" value="tunai">

<div>
<label class="block text-sm font-medium text-stone-600 mb-1">Jumlah Bayar (Rp)</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400">Rp</span>
<input type="number" name="jumlah_bayar" min="0" step="500" value="{{ $pesanan->total_bayar }}" required
       class="w-full pl-8 pr-4 py-3 rounded-xl border border-stone-200 focus:border-forest-500 focus:ring-2 focus:ring-forest-500/20 outline-none text-lg font-mono"
       placeholder="0">
</div>
</div>

<button type="submit" class="w-full bg-forest-900 hover:bg-[#042616] text-white font-bold py-4 rounded-xl text-lg shadow-sm transition">
Proses Pembayaran Tunai
</button>
</form>
@elseif($pesanan->status_pembayaran === 'sudah_bayar')
<div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-center">
<p class="font-bold">Pesanan sudah dibayar</p>
<p class="text-sm mt-1">Status: {{ $pesanan->status_pesanan }}</p>
</div>
@else
<div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-700 text-center">
<p class="font-bold">Status pembayaran: {{ $pesanan->status_pembayaran }}</p>
<p class="text-sm mt-1">Pesanan tidak dalam status menunggu pembayaran tunai</p>
</div>
@endif
</div>
@endif
</div>
@endsection