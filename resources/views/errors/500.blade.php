<x-layouts.app :title="'Algo salió mal - LevelUp Nexus'">
	<div class="max-w-3xl mx-auto text-center py-20">
		<div class="w-24 h-24 mx-auto rounded-3xl bg-orange-500/20 border border-orange-400/50 flex items-center justify-center text-orange-300 text-4xl mb-6 glow">
			<i class="fas fa-bug"></i>
		</div>
		<p class="uppercase tracking-[0.5em] text-sm text-orange-200 mb-2">Error 500</p>
		<h1 class="text-4xl md:text-5xl font-black bg-gradient-to-r from-pink-400 to-purple-500 bg-clip-text text-transparent mb-4">
			Algo se rompió en la nave
		</h1>
		<p class="text-purple-200/80 max-w-2xl mx-auto mb-10">
			Nuestro equipo ya está revisando los sistemas. Reintenta en unos segundos o vuelve a la base de operaciones.
			Si el problema persiste, cuéntanos qué estabas haciendo para poder arreglarlo.
		</p>
		<div class="flex flex-col sm:flex-row items-center justify-center gap-4">
			<button onclick="window.location.reload()" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-purple-500/40 text-purple-100 hover:bg-purple-500/10 transition">
				<i class="fas fa-sync-alt"></i> Reintentar
			</button>
			<a href="{{ route('landing') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 font-semibold text-white transition glow">
				<i class="fas fa-home"></i> Volver al inicio
			</a>
		</div>
		<p class="text-xs text-purple-300/70 mt-8">
			¿Sigues viendo este error? Escribe a <a href="mailto:hello@levelupnexus.com" class="underline hover:text-white">hello@levelupnexus.com</a>
		</p>
	</div>
</x-layouts.app>

