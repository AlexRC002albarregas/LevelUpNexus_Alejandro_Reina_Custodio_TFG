<?php $errors = $errors ?? new \Illuminate\Support\ViewErrorBag(); ?>
<x-layouts.app :title="'Amigo no disponible - LevelUp Nexus'">
	<div class="max-w-3xl mx-auto text-center py-20">
		<div class="w-24 h-24 mx-auto rounded-3xl bg-rose-500/20 border border-rose-400/40 flex items-center justify-center text-rose-200 text-4xl mb-6 glow">
			<i class="fas fa-user-slash"></i>
		</div>
		<p class="uppercase tracking-[0.5em] text-sm text-rose-200/80 mb-2">Conexión perdida</p>
		<h1 class="text-4xl md:text-5xl font-black bg-gradient-to-r from-pink-400 to-purple-500 bg-clip-text text-transparent mb-4">
			Ese perfil ya no está disponible
		</h1>
		<p class="text-purple-200/80 max-w-2xl mx-auto mb-10">
			Es posible que la amistad se haya eliminado, que el usuario te haya bloqueado o que su perfil sea privado.
			Busca nuevos compañeros de batalla desde la sección de amigos.
		</p>
		<div class="flex flex-col sm:flex-row items-center justify-center gap-4">
			<a href="{{ route('friends.index') }}" class="w-full sm&w-auto px-6 py-3 rounded-xl border border-purple-500/40 text-purple-100 hover:bg-purple-500/10 transition">
				<i class="fas fa-user-friends"></i> Ir a mis amigos
			</a>
			<a href="{{ auth()->check() ? route('users.show', auth()->user()) : route('landing') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 font-semibold text-white transition glow">
				<i class="fas fa-id-card"></i> Ver mi perfil
			</a>
		</div>
	</div>
</x-layouts.app>

