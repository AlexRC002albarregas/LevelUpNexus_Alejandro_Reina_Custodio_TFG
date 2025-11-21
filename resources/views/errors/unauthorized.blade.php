<?php $errors = $errors ?? new \Illuminate\Support\ViewErrorBag(); ?>
<x-layouts.app :title="'Acceso restringido - LevelUp Nexus'">
	<div class="max-w-3xl mx-auto text-center py-20">
		<div class="w-24 h-24 mx-auto rounded-3xl bg-amber-500/20 border border-amber-400/40 flex items-center justify-center text-amber-200 text-4xl mb-6 glow">
			<i class="fas fa-lock"></i>
		</div>
		<p class="uppercase tracking-[0.5em] text-sm text-amber-200/80 mb-2">Acceso restringido</p>
		<h1 class="text-4xl md:text-5xl font-black bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent mb-4">
			Necesitas iniciar sesión para continuar
		</h1>
		<p class="text-purple-200/80 max-w-2xl mx-auto mb-10">
			Esta sección está reservada para miembros de LevelUp Nexus. 
			Inicia sesión con tu cuenta o regístrate para acceder a todas las funcionalidades de la plataforma.
		</p>
		<div class="flex flex-col sm:flex-row items-center justify-center gap-4">
			<a href="{{ route('auth.login') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-purple-500/40 text-purple-100 hover:bg-purple-500/10 transition">
				<i class="fas fa-sign-in-alt"></i> Iniciar sesión
			</a>
			<a href="{{ route('auth.register') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 font-semibold text-white transition glow">
				<i class="fas fa-user-plus"></i> Registrarse
			</a>
		</div>
	</div>
</x-layouts.app>

