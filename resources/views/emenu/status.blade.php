<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pelacakan {{ $pesanan->kode_pesanan }} - Saung Situ Awi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#f5f3ec] text-stone-800 antialiased">
<div class="max-w-md mx-auto px-4 pt-4 pb-8">
<div class="flex items-center justify-between">
<a href="{{ route('emenu.meja', $pesanan->meja->kode_meja) }}" class="w-9 h-9 flex items-center justify-center">
<svg class="w-6 h-6 text-stone-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
</a>
<div class="text-center">
<p class="text-[11px] font-bold tracking-widest text-[#8a6d1f]">• SAUNG SITU AWI</p>
<p class="font-bold text-lg leading-tight">Pelacakan &amp; Status Pesanan</p>
</div>
<div class="flex items-center gap-2">
<span class="bg-white border border-stone-200 rounded-full px-3 py-1.5 text-xs font-bold">{{ $pesanan->meja->kode_meja }}</span>
<div class="w-9 h-9 rounded-full bg-[#0b3b2c] flex items-center justify-center">
<svg class="w-5 h-5 text-[#e9cf92]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a3 3 0 0 1 0 6h-1M4 8h14v6a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z"/></svg>
</div>
</div>
</div>
<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-4">
<p class="text-[11px] font-bold tracking-widest text-[#8a6d1f]">LIVE TRACKER &amp; PAYMENT</p>
<div class="flex items-start justify-between gap-3 mt-1">
<div>
<p class="font-bold text-lg leading-snug">Order #{{ $pesanan->kode_pesanan }}</p>
<p class="text-stone-500 text-xs mt-0.5">{{ $pesanan->meja->kode_meja }} • Dine-in</p>
</div>
<div class="text-right shrink-0">
<p class="text-stone-400 text-xs">Total Tagihan</p>
<p class="text-[#1e6b4f] font-extrabold text-xl">Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
</div>
</div>
@if ($pesanan->status_pesanan === 'dibatalkan')
<span class="inline-block bg-red-100 text-red-700 text-xs font-bold rounded-full px-3 py-1 mt-2">Pesanan Dibatalkan</span>
@else
<span class="inline-block bg-[#f3e2b6] text-[#7a5c14] text-xs font-bold rounded-full px-3 py-1 mt-2">{{ $pesanan->status_pembayaran === 'sudah_bayar' ? 'Sudah Dibayar' : 'Menunggu Pembayaran' }}</span>
@endif
</div>
<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-3">
<div class="flex items-center justify-between">
<h2 class="font-bold">Status Pesanan {{ $pesanan->meja->kode_meja }}</h2>
<span class="bg-stone-100 text-stone-500 text-[11px] font-bold rounded-full px-3 py-1">Realtime MQTT</span>
</div>
<div class="mt-3">
<div class="flex gap-3">
<div class="flex flex-col items-center">
<span class="w-7 h-7 rounded-full {{ $tahap >= 1 ? 'bg-[#1e6b4f] text-white' : 'bg-stone-200 text-stone-400' }} flex items-center justify-center text-sm font-bold">✓</span>
<span class="w-0.5 flex-1 {{ $tahap >= 2 ? 'bg-[#1e6b4f]' : 'bg-stone-200' }} min-h-[28px]"></span>
</div>
<div class="pb-4">
<p class="font-bold text-sm">Pesanan Diterima</p>
<p class="text-stone-500 text-xs">Tiket order masuk ke sistem antrean dapur</p>
<p class="text-stone-400 text-[11px] mt-0.5">{{ $pesanan->waktu_pesan->format('H.i') }} WIB</p>
</div>
</div>
<div class="flex gap-3">
<div class="flex flex-col items-center">
<span class="w-7 h-7 rounded-full {{ $tahap >= 2 ? 'bg-[#e9a13b] text-white' : 'bg-stone-200 text-stone-400' }} flex items-center justify-center text-sm font-bold">{{ $tahap >= 3 ? '✓' : '◷' }}</span>
<span class="w-0.5 flex-1 {{ $tahap >= 3 ? 'bg-[#1e6b4f]' : 'bg-stone-200' }} min-h-[28px]"></span>
</div>
<div class="pb-4">
<div class="flex items-center gap-2">
<p class="font-bold text-sm">Diproses di Dapur &amp; Bar</p>
@if ($tahap === 2)
<span class="bg-[#f3e2b6] text-[#7a5c14] text-[10px] font-bold rounded-full px-2.5 py-0.5">Aktif</span>
@endif
</div>
<p class="text-stone-500 text-xs">Sedang dimasak oleh Chef Saung Situ Awi</p>
<p class="text-stone-400 text-[11px] mt-0.5">Estimasi 15 - 20 menit</p>
</div>
</div>
<div class="flex gap-3">
<div class="flex flex-col items-center">
<span class="w-7 h-7 rounded-full {{ $tahap >= 3 ? 'bg-[#1e6b4f] text-white' : 'bg-stone-200 text-stone-400' }} flex items-center justify-center text-sm font-bold">{{ $tahap >= 4 ? '✓' : '◷' }}</span>
<span class="w-0.5 flex-1 {{ $tahap >= 4 ? 'bg-[#1e6b4f]' : 'bg-stone-200' }} min-h-[28px]"></span>
</div>
<div class="pb-4">
<p class="font-bold text-sm">Siap Diantarkan Pelayan</p>
<p class="text-stone-500 text-xs">Penyajian dari meja pass bar</p>
</div>
</div>
<div class="flex gap-3">
<div class="flex flex-col items-center">
<span class="w-7 h-7 rounded-full {{ $tahap >= 5 ? 'bg-[#1e6b4f] text-white' : 'bg-stone-200 text-stone-400' }} flex items-center justify-center text-sm font-bold">{{ $tahap >= 5 ? '✓' : '◷' }}</span>
</div>
<div>
<p class="font-bold text-sm">Pesanan Tiba di {{ $pesanan->meja->kode_meja }}</p>
<p class="text-stone-500 text-xs">Selamat menikmati hidangan khas Sunda!</p>
</div>
</div>
</div>
</div>
<div class="bg-[#0b3b2c] text-white rounded-2xl p-4 mt-3">
<div class="flex items-center justify-between">
<h2 class="font-bold text-sm">IoT Table Node {{ $pesanan->meja->kode_meja }}</h2>
<span class="bg-white/15 text-[11px] font-bold rounded-full px-3 py-1">• MQTT Synced</span>
</div>
<div class="font-mono text-[13px] mt-3 leading-relaxed text-[#b9e8c4]">
<p>SAUNG-IOT-{{ str_replace('-', '', $pesanan->meja->kode_meja) }} // ESP32 CH-02 WIFI: -54dBm</p>
<p class="text-white font-bold tracking-widest">STATUS: {{ strtoupper($pesanan->status_pesanan) }}</p>
<p>{{ $pesanan->meja->kode_meja }} &nbsp;&nbsp; {{ now()->format('H:i') }} WIB</p>
</div>
<p class="text-white/70 text-xs mt-3">Layar LCD fisik di saung Anda telah terupdate otomatis via MQTT WebSocket.</p>
</div>
@if ($pesanan->metode_pembayaran === 'qris')
<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-3">
<div class="flex items-center justify-between">
<h2 class="font-bold">Pembayaran QRIS</h2>
<span class="bg-stone-100 text-stone-500 text-[11px] font-bold rounded-full px-3 py-1">Midtrans Sandbox</span>
</div>
<div class="bg-[#f3e2b6] rounded-2xl p-4 mt-3 text-center">
<p class="font-bold text-[#7a5c14]">QRIS segera hadir</p>
<p class="text-[#7a5c14] text-xs mt-1">Integrasi Midtrans belum aktif. Sambil menunggu, selesaikan dengan tunai di kasir.</p>
</div>
<p class="text-stone-500 text-xs mt-3">Cara Pembayaran Scan: Buka aplikasi BCA Mobile, GoPay, OVO, Dana, ShopeePay, atau m-Banking Anda, lalu scan QR di atas.</p>
</div>
@else
<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-3">
<h2 class="font-bold">Pembayaran Tunai</h2>
<div class="bg-[#faf8f1] rounded-2xl p-4 mt-3 text-center">
<p class="font-extrabold text-2xl text-[#0b3b2c]">Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
<p class="text-stone-500 text-xs mt-1">Sebutkan kode <span class="font-mono font-bold text-stone-700">{{ $pesanan->kode_pesanan }}</span> di kasir utama.</p>
</div>
</div>
@endif
<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-3">
<h2 class="font-bold text-sm">Rincian Item Pesanan</h2>
<div class="divide-y divide-stone-100 mt-1">
@foreach ($pesanan->detailPesanan as $item)
<div class="flex items-center justify-between py-2.5">
<div>
<p class="font-bold text-sm">{{ $item->jumlah }}× {{ $item->menu->nama_menu }}</p>
@if ($item->catatan)
<p class="text-stone-400 text-xs">{{ $item->catatan }}</p>
@endif
</div>
<p class="font-bold text-sm">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p>
</div>
@endforeach
</div>
<div class="flex items-center justify-between border-t border-dashed border-stone-200 pt-3 mt-1">
<p class="font-bold text-sm">Total Pembayaran</p>
<p class="font-extrabold text-[#1e6b4f]">Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
</div>
</div>
<a href="{{ route('emenu.meja', $pesanan->meja->kode_meja) }}" class="block text-center w-full mt-4 bg-white border border-[#0b3b2c] text-[#0b3b2c] font-bold rounded-full py-3">Kembali ke Katalog</a>
</div>
</body>
</html>
