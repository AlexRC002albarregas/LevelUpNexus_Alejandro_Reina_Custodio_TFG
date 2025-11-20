<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\{User, Message, Friendship};

class MessageController extends Controller
{
    /**
     * Recupera la conversación completa con un amigo.
     */
    public function index(User $friend)
    {
        $this->ensureAreFriends($friend);
        
        $messages = Message::with('sender')->where(function($q) use ($friend){
            $q->where('sender_id', auth()->id())->where('receiver_id', $friend->id);
        })->orWhere(function($q) use ($friend){
            $q->where('sender_id', $friend->id)->where('receiver_id', auth()->id());
        })->orderBy('created_at')->get();
        
        // Añadir image_url a cada mensaje
        $messages = $messages->map(function($message) {
            $data = $this->withImageUrl($message);
            // Incluir datos del sender
            if($message->sender) {
                $data['sender'] = [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                    'avatar' => $message->sender->avatar ? asset('storage/' . $message->sender->avatar) : null,
                ];
            }
            return $data;
        });
        
        // Marcar como leídos
        Message::where('sender_id', $friend->id)->where('receiver_id', auth()->id())->update(['is_read' => true]);
        
        return response()->json(['messages' => $messages->values(), 'friend' => $friend]);
    }

    /**
     * Envía un nuevo mensaje privado a un amigo.
     */
    public function store(Request $request, User $friend)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:5120',
        ], [
            'content.max' => 'El mensaje no puede exceder los 1000 caracteres',
            'image.image' => 'El archivo adjunto debe ser una imagen válida',
            'image.max' => 'La imagen no puede superar los 5 MB',
        ]);

        $validator->after(function($validator) use ($request) {
            if(!$request->filled('content') && !$request->hasFile('image')) {
                $validator->errors()->add('content', 'Debes escribir un mensaje o adjuntar una imagen.');
            }
        });

        $validator->validate();

        $this->ensureAreFriends($friend);

        $imagePath = null;
        if($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('messages', 'public');
        }
        
        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $friend->id,
            'content' => $request->input('content', ''),
            'image_path' => $imagePath,
        ]);
        
        $message->load('sender');
        $messageData = $this->withImageUrl($message);
        // Incluir datos del sender en el array
        if($message->sender) {
            $messageData['sender'] = [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'avatar' => $message->sender->avatar ? asset('storage/' . $message->sender->avatar) : null,
            ];
        }
        return response()->json(['message' => $messageData], 201);
    }

    /**
     * Elimina un mensaje enviado por el usuario autenticado.
     */
    public function destroy(Message $message)
    {
        abort_unless($message->sender_id === auth()->id(), 403);

        if($message->image_path && Storage::disk('public')->exists($message->image_path)) {
            Storage::disk('public')->delete($message->image_path);
        }

        $message->delete();

        return response()->json(['message' => 'Mensaje eliminado']);
    }

    protected function ensureAreFriends(User $friend): void
    {
        $areFriends = Friendship::where(function($q) use ($friend){
            $q->where('user_id', auth()->id())->where('friend_id', $friend->id)->where('status', 'accepted');
        })->orWhere(function($q) use ($friend){
            $q->where('user_id', $friend->id)->where('friend_id', auth()->id())->where('status', 'accepted');
        })->exists();
        
        if(!$areFriends) abort(403);
    }

    protected function withImageUrl(Message $message)
    {
        $data = $message->toArray();
        $data['image_url'] = $message->image_path
            ? asset('storage/' . ltrim($message->image_path, '/'))
            : null;
        // Asegurar que image_path también esté visible
        $data['image_path'] = $message->image_path;
        return $data;
    }
}

