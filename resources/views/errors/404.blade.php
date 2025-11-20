<x-layouts.app :title="'Página no encontrada - LevelUp Nexus'">
	<div class="max-w-3xl mx-auto text-center py-20">
		<div class="w-24 h-24 mx-auto rounded-3xl bg-yellow-400/10 border border-yellow-400/40 flex items-center justify-center text-yellow-300 text-4xl mb-6 glow">
			<i class="fas fa-compass"></i>
		</div>
		<p class="uppercase tracking-[0.5em] text-sm text-yellow-200/80 mb-2">Error 404</p>
		<h1 class="text-4xl md:text-5xl font-black bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent mb-4">
			La página que buscas se ha perdido en el mapa
		</h1>
		<p class="text-purple-200/80 max-w-2xl mx-auto mb-10">
			Quizá el enlace caducó, cambió de lugar o simplemente nunca existió. 
			No te preocupes, aquí tienes algunos caminos para seguir explorando LevelUp Nexus.
		</p>
		<div class="flex flex-col sm:flex-row items-center justify-center gap-4">
			<a href="{{ route('landing') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-purple-500/40 text-purple-100 hover:bg-purple-500/10 transition">
				<i class="fas fa-home"></i> Ir al inicio
			</a>
			<a href="{{ route('posts.index') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 font-semibold text-white transition glow">
				<i class="fas fa-newspaper"></i> Ver publicaciones
			</a>
		</div>
	</div>
</x-layouts.app>

