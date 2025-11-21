<?php $errors = $errors ?? new \Illuminate\Support\ViewErrorBag(); ?>
<x-layouts.app :title="'Página no encontrada - LevelUp Nexus'">
	<div class="max-w-3xl mx-auto text-center py-20">
		<div class="w-24 h-24 mx-auto rounded-3xl bg-indigo-500/20 border border-indigo-400/40 flex items-center justify-center text-indigo-200 text-4xl mb-6 glow">
			<i class="fas fa-map-marked-alt"></i>
		</div>
		<p class="uppercase tracking-[0.5em] text-sm text-indigo-200/80 mb-2">Error 404</p>
		<h1 class="text-4xl md:text-5xl font-black bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-4">
			Esta ruta no existe en LevelUp Nexus
		</h1>
		<p class="text-purple-200/80 max-w-2xl mx-auto mb-10">
			La página que buscas no se encuentra en nuestro mapa. 
			Puede que hayas escrito mal la URL o que la ruta haya sido eliminada. 
			Vuelve al inicio para seguir explorando.
		</p>
		<div class="flex flex-col sm:flex-row items-center justify-center gap-4">
			<a href="{{ route('landing') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-purple-500/40 text-purple-100 hover:bg-purple-500/10 transition">
				<i class="fas fa-home"></i> Ir al inicio
			</a>
			@auth
			<a href="{{ route('posts.index') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 font-semibold text-white transition glow">
				<i class="fas fa-newspaper"></i> Ver publicaciones
			</a>
			@else
			<a href="{{ route('auth.login') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 font-semibold text-white transition glow">
				<i class="fas fa-sign-in-alt"></i> Iniciar sesión
			</a>
			@endauth
		</div>
	</div>
</x-layouts.app>

