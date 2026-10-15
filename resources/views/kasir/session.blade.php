<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Session Meja</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-[#FBF8F1] p-4">
<div class="max-w-3xl mx-auto">
<header class="flex items-center justify-between mb-6">
<div class="flex items-center gap-3">
<a href="{{ route('kasir.dashboard') }}" class="flex items-center gap-2 text-[#6B6A63] hover:text-[#2B2A26] text-sm font-medium">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
Kembali
</a>
</div>
<form method="POST" action="{{ route('logout') }}" class="hidden md:block">
@csrf
<button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FDF5E6] hover:bg-[#F5ECD8] border border-[#E0B24E]/40 hover:border-[#E0B24E] active:scale-95 transition text-xs font-bold text-[#7A5C0F] whitespace-nowrap">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
<span>Keluar</span>
</button>
</form>
</header>

<h1 class="font-display font-bold text-2xl text-[#052E1B] mb-2">Session Meja</h1>
<p class="text-stone-500 text-sm mb-6">Kelola sesi meja aktif. Selesaikan sesi untuk menutup sesi pelanggan dan membuat kode unik baru.</p>

@foreach ($meja as $m)
<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5 mb-4">
<div class="flex items-center justify-between mb-3">
<div>
<p class="font-bold text-lg text-forest-900">{{ $m->kode_meja }} ({{ $m->area }})</p>
<p class="text-stone-500 text-sm">Status: <span class="font-semibold text-forest-700">{{ ucfirst($m->status_meja) }}</span></p>
<div class="mt-1 flex items-center gap-2">
<span class="text-xs text-stone-500">Kode sesi aktif:</span>
<span class="px-3 py-1 rounded-lg bg-forest-900 text-gold-500 font-mono font-extrabold text-lg tracking-[0.3em]">{{ ($sesiAktif[$m->id] ?? null)->kode_unik ?? '----' }}</span>
</div>
</div>
@if ($m->pesananAktif->count() > 0)
<form method="POST" action="{{ route('kasir.session.tutup', $m->kode_meja) }}">
@csrf
@method('PATCH')
<button type="submit" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white font-bold text-sm shadow-sm transition" onclick="return confirm('Selesaikan sesi meja ini? Semua pesanan aktif akan ditandai selesai dan kode unik baru akan dibuat.')">
Selesaikan Sesi
</button>
</form>
@else
<form method="POST" action="{{ route('kasir.session.tutup', $m->kode_meja) }}">
@csrf
@method('PATCH')
<button type="submit" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-600 font-bold text-sm shadow-sm transition" onclick="return confirm('Selesaikan sesi meja ini dan buat kode unik baru?')">
Selesaikan Sesi
</button>
</form>
@endif
</div>

@if ($m->pesananAktif->count() > 0)
<div class="space-y-2 mb-3">
@foreach ($m->pesananAktif as $p)
<div class="bg-cream-50 rounded-xl border border-stone-100 p-3">
<div class="flex items-center justify-between">
<div>
<p class="font-bold text-sm text-forest-900">{{ $p->kode_pesanan }}</p>
<p class="text-stone-500 text-xs">{{ $p->detailPesanan->sum('jumlah') }} item • Rp{{ number_format($p->total_bayar, 0, ',', '.') }}</p>
</div>
<span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $p->status_pembayaran === 'sudah_bayar' ? 'bg-forest-100 text-forest-700' : 'bg-gold-100 text-gold-700' }}">
{{ $p->status_pembayaran === 'sudah_bayar' ? 'Sudah Bayar' : 'Belum Bayar' }}
</span>
</div>
</div>
@endforeach
</div>
@else
<p class="text-stone-400 text-sm text-center py-4">Tidak ada pesanan aktif</p>
@endif
</div>
@endforeach

<p class="text-center text-stone-400 text-xs mt-8">© 2026 Saung Situ Awi Ciwidey</p>
</div>
</body>
</html>