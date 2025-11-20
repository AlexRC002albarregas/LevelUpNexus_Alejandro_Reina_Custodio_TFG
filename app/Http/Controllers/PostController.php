<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Group;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Genera el feed de publicaciones visibles para el usuario.
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $userId = auth()->id();
        
        // Obtener IDs de amigos del usuario autenticado
        $friendIds = DB::table('friendships')
            ->where(function($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('friend_id', $userId);
            })
            ->where('status', 'accepted')
            ->get()
            ->map(function($friendship) use ($userId) {
                return $friendship->user_id === $userId 
                    ? $friendship->friend_id 
                    : $friendship->user_id;
            })
            ->toArray();
        
        // Filtrar publicaciones según visibilidad del post y perfil del autor
        // Excluir publicaciones de grupo (group_id no null)
        $postsQuery = Post::with(['user', 'group', 'game', 'reactions.user', 'comments.user', 'images'])
            ->whereNull('group_id') // Solo publicaciones generales, no de grupos
            ->where(function($query) use ($userId, $friendIds) {
                // 1. Propias publicaciones (siempre visibles)
                $query->where('user_id', $userId)
                    // 2. Publicaciones públicas de perfiles públicos
                    ->orWhere(function($q) use ($userId, $friendIds) {
                        $q->where('visibility', 'public')
                            ->whereHas('user', function($userQ) use ($userId, $friendIds) {
                                $userQ->where('is_private', false)
                                    ->orWhereIn('id', $friendIds);
                            });
                    })
                    // 3. Publicaciones públicas de perfiles privados (solo si es amigo)
                    ->orWhere(function($q) use ($userId, $friendIds) {
                        $q->where('visibility', 'public')
                            ->whereHas('user', function($userQ) use ($friendIds) {
                                $userQ->where('is_private', true)
                                    ->whereIn('id', $friendIds);
                            });
                    })
                    // 4. Publicaciones privadas (solo si es amigo del autor)
                    ->orWhere(function($q) use ($friendIds) {
                        $q->where('visibility', 'private')
                            ->whereHas('user', function($userQ) use ($friendIds) {
                                $userQ->whereIn('id', $friendIds);
                            });
                    });
            });

        // Construir listado de juegos disponibles para filtro
        $availableGames = (clone $postsQuery)
            ->select('id', 'game_title', 'game_id')
            ->with(['game:id,title'])
            ->where(function($query) {
                $query->whereNotNull('game_title')
                    ->orWhereNotNull('game_id');
            })
            ->get()
            ->map(function($post) {
                return $post->game_title ?: optional($post->game)->title;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Filtro por categoría (juego)
        if ($request->filled('game')) {
            $selectedGame = $request->input('game');
            $postsQuery->where(function($query) use ($selectedGame) {
                $query->where('game_title', $selectedGame)
                    ->orWhereHas('game', function($gameQuery) use ($selectedGame) {
                        $gameQuery->where('title', $selectedGame);
                    });
            });
        }

        // Filtro por usuario (búsqueda por nombre)
        if ($request->filled('user')) {
            $userSearch = $request->input('user');
            $postsQuery->whereHas('user', function($userQuery) use ($userSearch) {
                $userQuery->where('name', 'like', '%' . $userSearch . '%');
            });
        }

        // Filtro por antigüedad
        $sort = $request->input('sort', 'recent');
        if ($sort === 'oldest') {
            $postsQuery->orderBy('created_at', 'asc');
        } else {
            $postsQuery->orderBy('created_at', 'desc');
        }

        $posts = $postsQuery->paginate(10)->withQueryString();
        
        // Ordenar comentarios por fecha (más recientes primero) para cada post
        foreach($posts as $post) {
            $post->comments = $post->comments->sortByDesc('created_at')->values();
        }
        
        return view('posts.index', [
            'posts' => $posts,
            'availableGames' => $availableGames,
            'currentSort' => $sort,
            'currentGame' => $request->input('game'),
            'currentUser' => $request->input('user'),
        ]);
    }

    /**
     * Muestra el formulario para crear una publicación.
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Guarda una nueva publicación y gestiona archivos adjuntos.
     */
    public function store(StorePostRequest $request)
    {
        $data = [
            'user_id' => auth()->id(),
            'content' => $request->content,
            'group_id' => $request->group_id,
            'game_id' => $request->game_id,
            'rawg_game_id' => $request->rawg_game_id,
            'game_title' => $request->game_title,
            'game_image' => $request->game_image,
            'game_platform' => $request->game_platform,
            'visibility' => $request->group_id ? 'group' : ($request->visibility ?? 'public'),
        ];

        $post = Post::create($data);
        $this->storePostImages($post, $request->file('images', []));
        $post->load(['user', 'group', 'images']);

        $redirectRoute = $request->group_id 
            ? route('groups.show', $request->group_id)
            : route('posts.show', $post);

        if ($request->expectsJson()) {
            $canDelete = $this->canUserDeletePost($post);

            return response()->json([
                'message' => [
                    'id' => $post->id,
                    'content' => $post->content,
                    'created_at' => $post->created_at->toIso8601String(),
                    'images' => $post->images->map(fn($image) => [
                        'id' => $image->id,
                        'url' => asset('storage/' . $image->path),
                    ]),
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
                        'avatar' => $post->user->avatar ? asset('storage/' . $post->user->avatar) : null,
                        'profile_url' => route('users.show', $post->user),
                    ],
                ],
                'delete_url' => route('posts.destroy', $post),
                'can_delete' => $canDelete,
            ], 201);
        }

        return redirect($redirectRoute)
            ->with('status', 'Publicación creada correctamente');
    }

    /**
     * Muestra el detalle de una publicación verificando permisos.
     */
    public function show(Post $post)
    {
        $currentUser = auth()->user();
        
        // Verificar si el usuario puede ver esta publicación
        // 1. Si es el propio autor, siempre puede ver
        if($post->user_id === auth()->id()) {
            $post->load(['user', 'group', 'game', 'comments.user', 'reactions.user', 'images']);
            return view('posts.show', compact('post'));
        }
        
        // 2. Verificar perfil privado del autor
        if(!$currentUser->canViewProfile($post->user)){
            abort(403, 'No tienes permiso para ver esta publicación');
        }
        
        // 3. Verificar visibilidad específica del post
        if($post->visibility === 'private') {
            // Solo amigos pueden ver publicaciones privadas
            if(!$currentUser->isFriendWith($post->user)) {
                abort(403, 'Esta es una publicación privada. Solo los amigos del autor pueden verla.');
            }
        } elseif($post->visibility === 'group') {
            // Solo miembros del grupo pueden ver
            if(!$post->group || !$post->group->members->contains(auth()->id())) {
                abort(403, 'Esta es una publicación de grupo. Solo los miembros pueden verla.');
            }
        }
        // 'public' y otros valores: ya pasaron la verificación del perfil
        
        $post->load(['user', 'group', 'game', 'comments.user', 'reactions.user', 'images']);
        
        return view('posts.show', compact('post'));
    }

    /**
     * Muestra el formulario de edición de una publicación.
     */
    public function edit(Post $post)
    {
        // Verificar autorización
        abort_unless(
            auth()->id() === $post->user_id || auth()->user()->role === 'admin',
            403
        );
        
        $post->load('images');

        return view('posts.edit', compact('post'));
    }

    /**
     * Actualiza los datos y archivos de una publicación existente.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $isGroupPost = !is_null($post->group_id);
        $post->load('images');

        $data = [
            'content' => $request->content,
            'group_id' => $post->group_id,
            'visibility' => $isGroupPost ? 'group' : ($request->visibility ?? 'public'),
        ];

        $post->update($data);

        $removeIds = collect($request->input('remove_images', []))
            ->filter()
            ->map(fn ($id) => (int) $id);

        $imagesToRemove = $post->images->whereIn('id', $removeIds);
        $remainingCount = $post->images->count() - $imagesToRemove->count();
        $newImages = $request->file('images', []);

        if ($remainingCount + count($newImages) > 4) {
            return back()
                ->withErrors(['images' => 'Puedes adjuntar hasta 4 imágenes por publicación.'])
                ->withInput();
        }

        foreach ($imagesToRemove as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();
        }

        $post->unsetRelation('images');
        $this->storePostImages($post, $newImages);

        return redirect()
            ->route('posts.show', $post)
            ->with('status', 'Publicación actualizada correctamente');
    }

    /**
     * Elimina una publicación si el usuario está autorizado.
     */
    public function destroy(Post $post)
    {
        $redirectRoute = 'posts.index';
        $canDelete = $this->canUserDeletePost($post);

        abort_unless($canDelete, 403, 'No tienes permisos para eliminar esta publicación');

        if($post->group_id) {
            $redirectRoute = 'groups.show';
            $redirectParam = $post->group_id;
        }
        
        $post->load('images');
        foreach ($post->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();
        }

        $post->delete();

        if(isset($redirectParam)) {
            return redirect()->route($redirectRoute, $redirectParam)->with('status', 'Mensaje eliminado correctamente');
        }

        return redirect()->route($redirectRoute)->with('status', 'Publicación eliminada correctamente');
    }

    /**
     * Determina si el usuario autenticado puede eliminar una publicación.
     */
    protected function canUserDeletePost(Post $post, $user = null): bool
    {
        $user = $user ?: auth()->user();

        if(!$user) {
            return false;
        }

        if($user->id === $post->user_id || $user->role === 'admin') {
            return true;
        }

        if($post->group_id) {
            $group = $post->group ?: Group::find($post->group_id);

            if(!$group) {
                return false;
            }

            if($group->owner_id === $user->id) {
                return true;
            }

            $memberRole = $group->members()
                ->where('user_id', $user->id)
                ->first()
                ->pivot
                ->member_role ?? null;

            return $memberRole === 'moderator';
        }

        return false;
    }

    /**
     * Almacena nuevas imágenes asociadas a un post respetando el límite.
     */
    protected function storePostImages(Post $post, array $images = []): void
    {
        if (empty($images)) {
            return;
        }

        $existingCount = $post->images()->count();
        $availableSlots = max(0, 4 - $existingCount);

        if ($availableSlots === 0) {
            return;
        }

        $uploads = array_slice($images, 0, $availableSlots);

        foreach ($uploads as $imageFile) {
            $path = $imageFile->store('posts', 'public');
            $post->images()->create(['path' => $path]);
        }
    }
}
