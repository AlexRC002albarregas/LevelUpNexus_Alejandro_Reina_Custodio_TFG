<x-layouts.app :title="'Editar Publicación - LevelUp Nexus'">
	<div class="max-w-3xl mx-auto">
		<h1 class="text-4xl font-black bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent mb-8">
			<i class="fas fa-edit"></i> Editar Publicación
		</h1>

		<form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data" class="p-8 rounded-2xl bg-slate-800/50 border border-purple-500 backdrop-blur-sm glow">
			@csrf
			@method('PUT')
			
			<div class="mb-6">
				<label class="block text-sm mb-2 text-purple-300 font-semibold">
					<i class="fas fa-edit"></i> Contenido de la publicación
				</label>
				<textarea 
					name="content" 
					required 
					rows="8"
					class="w-full bg-slate-900 border border-purple-500/50 rounded-lg px-4 py-3 text-white placeholder-purple-400/50 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 resize-none"
					placeholder="¿Qué quieres compartir con la comunidad? (máx. 5000 caracteres)"
				>{{ old('content', $post->content) }}</textarea>
				@error('content')
					<p class="text-red-400 text-sm mt-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
				@enderror
			</div>

			@php
				$currentImages = $post->images ?? collect();
				$maxImages = 4;
				$remainingSlots = max(0, $maxImages - $currentImages->count());
			@endphp

			@if($currentImages->count())
				<div class="mb-6">
					<label class="block text-sm mb-2 text-purple-300 font-semibold">
						<i class="fas fa-images"></i> Imágenes actuales
					</label>
					<p class="text-xs text-purple-400 mb-3">Selecciona las imágenes que quieras eliminar. Puedes mantener hasta {{ $maxImages }} en total.</p>
					<div class="flex flex-wrap gap-4">
						@foreach($currentImages as $image)
							<div class="relative">
								<label class="block w-24 h-24 cursor-pointer group">
									<input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="sr-only peer">
									<img 
										src="{{ asset('storage/' . $image->path) }}" 
										alt="Imagen de la publicación" 
										class="w-24 h-24 object-cover rounded-lg border border-purple-500/30 peer-checked:opacity-40 peer-checked:border-red-400 transition cursor-pointer"
									>
									<span class="absolute inset-0 hidden peer-checked:flex items-center justify-center text-[10px] font-bold uppercase bg-red-600/80 text-white rounded-lg">
										Eliminar
									</span>
								</label>
								<button 
									type="button" 
									class="absolute bottom-1 right-1 w-7 h-7 rounded-full bg-black/70 text-white flex items-center justify-center text-xs hover:bg-black/90 transition"
									onclick="event.preventDefault(); event.stopPropagation(); openImagePreview('{{ asset('storage/' . $image->path) }}')"
									title="Ver en grande"
								>
									<i class="fas fa-search-plus"></i>
								</button>
							</div>
						@endforeach
					</div>
					@error('remove_images.*')
						<p class="text-red-400 text-sm mt-2"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
					@enderror
				</div>
			@endif

			<!-- Nuevas imágenes -->
			<div class="mb-6">
				<label class="block text-sm mb-2 text-purple-300 font-semibold">
					<i class="fas fa-image"></i> Añadir imágenes (opcional)
				</label>
				<input 
					type="file" 
					name="images[]" 
					id="postImagesInput"
					accept="image/*"
					multiple
					data-max="{{ $maxImages }}"
					class="w-full bg-slate-900 border border-purple-500/50 rounded-lg px-4 py-3 text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-600 file:text-white hover:file:bg-purple-700"
				>
				<p class="text-sm text-purple-400 mt-1">
					<i class="fas fa-info-circle"></i> Puedes adjuntar hasta {{ $maxImages }} imágenes en total (5MB por archivo). Actualmente puedes añadir {{ $remainingSlots > 0 ? $remainingSlots : 0 }} más.
				</p>
				@if($remainingSlots <= 0)
					<p class="text-xs text-yellow-300 mt-1">
						<i class="fas fa-exclamation-triangle"></i> Si ya alcanzaste el límite, marca alguna imagen para eliminar y luego añade las nuevas.
					</p>
				@endif
				@error('images')
					<p class="text-red-400 text-sm mt-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
				@enderror
				@error('images.*')
					<p class="text-red-400 text-sm mt-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
				@enderror
				
				<div id="postImagesPreview" class="hidden mt-3">
					<label class="block text-sm mb-2 text-purple-300 font-semibold">
						Vista previa
					</label>
					<div class="flex flex-wrap gap-3" id="postImagesPreviewList"></div>
					<button type="button" id="clearImagesSelection" class="mt-3 text-xs text-red-300 hover:text-red-200 font-semibold hidden">
						<i class="fas fa-times"></i> Limpiar selección
					</button>
				</div>
			</div>

			<div class="mb-6">
				<label class="block text-sm mb-2 text-purple-300 font-semibold">
					<i class="fas fa-eye"></i> Visibilidad
				</label>
				<select 
					name="visibility" 
					class="w-full bg-slate-900 border border-purple-500/50 rounded-lg px-4 py-3 text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50"
				>
					<option value="public" {{ old('visibility', $post->visibility ?? 'public') == 'public' ? 'selected' : '' }}>Pública</option>
					<option value="private" {{ old('visibility', $post->visibility) == 'private' ? 'selected' : '' }}>Privada (solo amigos)</option>
				</select>
			</div>

			<div class="flex gap-4">
				<button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 font-bold text-white transition glow-sm">
					<i class="fas fa-save"></i> Guardar Cambios
				</button>
				<a href="{{ route('posts.show', $post) }}" class="px-6 py-3 rounded-lg border border-purple-500 hover:bg-purple-500/20 transition font-semibold text-purple-300">
					<i class="fas fa-times"></i> Cancelar
				</a>
			</div>
		</form>
	</div>

	<!-- Modal de preview de imagen -->
	<div id="imagePreviewModal" class="hidden fixed inset-0 bg-black/90 backdrop-blur-sm flex items-center justify-center z-[200] p-4" onclick="closeImagePreview()">
		<div class="relative max-w-7xl max-h-[90vh] w-full h-full flex items-center justify-center">
			<button 
				type="button" 
				onclick="closeImagePreview()" 
				class="absolute top-4 right-4 w-12 h-12 rounded-full bg-red-600 hover:bg-red-700 text-white transition flex items-center justify-center z-10 glow"
			>
				<i class="fas fa-times text-xl"></i>
			</button>
			<img 
				id="previewModalImage" 
				src="" 
				alt="Imagen ampliada" 
				class="max-w-full max-h-full object-contain rounded-lg"
				onclick="event.stopPropagation()"
			>
		</div>
	</div>

	<script>
		// Preview de nuevas imágenes
		const postImagesInput = document.getElementById('postImagesInput');
		const postImagesPreview = document.getElementById('postImagesPreview');
		const postImagesPreviewList = document.getElementById('postImagesPreviewList');
		const clearImagesSelection = document.getElementById('clearImagesSelection');

		function renderSelectedImages(files) {
			if(!postImagesPreviewList || !postImagesPreview) return;

			postImagesPreviewList.innerHTML = '';

			if(!files.length) {
				postImagesPreview.classList.add('hidden');
				if(clearImagesSelection) {
					clearImagesSelection.classList.add('hidden');
				}
				return;
			}

			files.forEach(file => {
				const wrapper = document.createElement('div');
				wrapper.className = 'relative w-20 h-20';
				const img = document.createElement('img');
				img.className = 'w-full h-full object-cover rounded-lg border border-purple-500/30';
				const reader = new FileReader();
				reader.onload = e => {
					img.src = e.target?.result;
				};
				reader.readAsDataURL(file);
				wrapper.appendChild(img);
				postImagesPreviewList.appendChild(wrapper);
			});

			postImagesPreview.classList.remove('hidden');
			if(clearImagesSelection) {
				clearImagesSelection.classList.remove('hidden');
			}
		}

		if(postImagesInput) {
			postImagesInput.addEventListener('change', function() {
				const max = parseInt(this.dataset.max || '4', 10);
				const files = Array.from(this.files || []).slice(0, max);
				renderSelectedImages(files);
			});
		}

		if(clearImagesSelection && postImagesInput) {
			clearImagesSelection.addEventListener('click', () => {
				postImagesInput.value = '';
				renderSelectedImages([]);
			});
		}

		// Modal de preview de imagen
		function openImagePreview(imageSrc) {
			document.getElementById('previewModalImage').src = imageSrc;
			document.getElementById('imagePreviewModal').classList.remove('hidden');
			document.body.style.overflow = 'hidden';
		}

		function closeImagePreview() {
			document.getElementById('imagePreviewModal').classList.add('hidden');
			document.body.style.overflow = 'auto';
		}

		// Cerrar modal con tecla ESC
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape') {
				closeImagePreview();
			}
		});
	</script>
</x-layouts.app>

