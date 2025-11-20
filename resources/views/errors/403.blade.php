<x-layouts.app :title="'Acceso denegado - LevelUp Nexus'">
	<div class="max-w-3xl mx-auto text-center py-20">
		<div class="w-24 h-24 mx-auto rounded-3xl bg-red-600/20 border border-red-500/40 flex items-center justify-center text-red-400 text-4xl mb-6 glow">
			<i class="fas fa-lock"></i>
		</div>
		<p class="uppercase tracking-[0.5em] text-sm text-red-300 mb-2">Error 403</p>
		<h1 class="text-4xl md:text-5xl font-black bg-gradient-to-r from-pink-400 to-purple-500 bg-clip-text text-transparent mb-4">
			No tienes permiso para entrar aquí
		</h1>
		<p class="text-purple-200/80 max-w-2xl mx-auto mb-10">
			Parece que este contenido es privado o exclusivo para otros miembros. 
			Si crees que se trata de un error, habla con el propietario o un moderador del grupo.
		</p>
		<div class="flex flex-col sm:flex-row items-center justify-center gap-4">
			<a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('landing') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-purple-500/40 text-purple-100 hover:bg-purple-500/10 transition">
				<i class="fas fa-arrow-left"></i> Volver atrás
			</a>
			<a href="{{ route('groups.index') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 font-semibold text-white transition glow">
				<i class="fas fa-users"></i> Ver mis grupos
			</a>
		</div>
	</div>
</x-layouts.app>

