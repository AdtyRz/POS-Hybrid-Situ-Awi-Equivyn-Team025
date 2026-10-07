<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Daftar Session Meja - Kasir</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#f5f3ec] text-stone-800">
<div class="max-w-4xl mx-auto px-4 py-6">
<div class="flex items-center justify-between flex-wrap gap-3">
<h1 class="text-2xl font-bold text-[#0b3b2c]">Session Meja</h1>
<a href="{{ route('kasir.dashboard') }}" class="text-sm font-semibold underline text-[#0b3b2c]">Kembali ke Dashboard</a>
</div>

@if (session('sukses'))
<div class="mt-4 bg-emerald-100 text-emerald-900 rounded-xl px-4 py-3 text-sm">{{ session('sukses') }}</div>
@endif

<div class="mt-5 bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
<table class="w-full text-sm">
<thead>
<tr class="text-left text-stone-500 border-b bg-stone-50">
<th class="py-2.5 px-4">Meja</th>
<th class="py-2.5 px-4">Status</th>
<th class="py-2.5 px-4">Session Code</th>
<th class="py-2.5 px-4">Mulai</th>
<th class="py-2.5 px-4">Aksi</th>
</tr>
</thead>
<tbody>
@forelse ($meja as $m)
<tr class="border-b last:border-0">
<td class="py-2.5 px-4">{{ $m->kode_meja }} <span class="text-xs text-stone-400">({{ $m->area }})</span></td>
<td class="py-2.5 px-4">
<span class="text-xs font-semibold rounded-full px-2 py-1
@if ($m->status_meja === 'terisi') bg-indigo-100 text-indigo-800
@elseif ($m->status_meja === 'kosong') bg-emerald-100 text-emerald-800
@else bg-amber-100 text-amber-800 @endif">{{ $m->status_meja }}</span>
</td>
<td class="py-2.5 px-4 font-mono">{{ $m->session_code ?? '-' }}</td>
<td class="py-2.5 px-4 text-stone-500">{{ $m->session_started_at ? $m->session_started_at->format('d/m/Y H:i') : '-' }}</td>
<td class="py-2.5 px-4">
@if ($m->session_code && !$m->session_ended_at)
<form method="POST" action="{{ route('kasir.session.tutup', $m) }}">
@csrf
@method('PATCH')
<button type="submit" class="text-sm font-semibold text-rose-700 hover:underline">Akhiri Session</button>
</form>
@else
<span class="text-xs text-stone-400">-</span>
@endif
</td>
</tr>
@empty
<tr><td colspan="5" class="py-4 px-4 text-center text-stone-500">Tidak ada meja.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
</body>
</html>
