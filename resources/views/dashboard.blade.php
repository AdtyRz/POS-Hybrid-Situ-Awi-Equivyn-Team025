<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard - Saung Situ Awi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#f5f3ec] flex items-center justify-center px-4">
<div class="w-full max-w-md bg-white rounded-3xl shadow-xl p-8 text-center">
<div class="w-16 h-16 mx-auto rounded-2xl bg-[#0b3b2c] flex items-center justify-center">
<svg class="w-9 h-9 text-[#e9cf92]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a3 3 0 0 1 0 6h-1M4 8h14v6a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z"/><path stroke-linecap="round" d="M7 2v2M11 2v2M15 2v2"/></svg>
</div>
<h1 class="font-bold text-2xl text-[#0b3b2c] mt-4">Selamat datang, {{ $nama }}</h1>
<span class="inline-block bg-[#b9e8c4] text-[#0b3b2c] font-bold rounded-full px-4 py-1.5 text-sm mt-3">Role: {{ $role }}</span>
<p class="text-stone-500 text-sm mt-4">Halaman kerja {{ $role }} sedang dibangun. Login berhasil dan sesi tersimpan dengan benar.</p>
<form method="POST" action="{{ route('logout') }}" class="mt-6">
@csrf
<button type="submit" class="w-full bg-[#0b3b2c] hover:bg-[#0e4a37] text-white font-semibold rounded-2xl py-3">Keluar</button>
</form>
</div>
</body>
</html>
