<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Friendship;
use App\Models\GroupInvitation;

class AccountController extends Controller
{
    /**
     * Muestra el formulario de edición de la cuenta del usuario.
     */
    public function edit()
    {
        return view('account.edit');
    }

    /**
     * Actualiza los datos básicos y de seguridad de la cuenta.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','unique:users,email,'.auth()->id()],
            'avatar' => ['nullable','image','mimes:jpeg,jpg,png,gif,webp','max:2048'],
            'bio' => ['nullable','string','max:500'],
            'password' => ['nullable','string','min:8','confirmed'],
            'remove_avatar' => ['nullable','boolean'],
        ], [
            'name.required' => 'El nombre es obligatorio',
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Debes proporcionar un correo electrónico válido',
            'email.unique' => 'Este correo electrónico ya está en uso',
            'avatar.image' => 'El archivo debe ser una imagen',
            'avatar.mimes' => 'El avatar debe ser de tipo: jpeg, jpg, png, gif o webp',
            'avatar.max' => 'El avatar no puede ser mayor de 2MB',
            'bio.max' => 'La biografía no puede exceder los 500 caracteres',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'La confirmación de la contraseña no coincide',
        ]);
        
        $user = auth()->user();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        // Manejar subida de avatar
        if($request->hasFile('avatar')){
            // Eliminar avatar anterior si existe
            if($user->avatar && Storage::disk('public')->exists($user->avatar)){
                Storage::disk('public')->delete($user->avatar);
            }
            // Guardar nuevo avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        } elseif($request->boolean('remove_avatar')) {
            if($user->avatar && Storage::disk('public')->exists($user->avatar)){
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        }
        
        if(isset($validated['bio'])){
            $user->bio = $validated['bio'];
        }
        
        // Actualizar privacidad (checkbox)
        $user->is_private = $request->has('is_private');
        
        if($validated['password'] ?? false){
            $user->password = Hash::make($validated['password']);
        }
        $user->save();
        
        return back()->with('status', 'Cuenta actualizada correctamente');
    }

    /**
     * Elimina definitivamente la cuenta tras validar credenciales.
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ], [
            'email.required' => 'Debes confirmar tu correo electrónico',
            'email.email' => 'Debes proporcionar un correo electrónico válido',
            'password.required' => 'Debes confirmar tu contraseña',
        ]);
        
        $user = auth()->user();
        
        // Verificar que el email coincide
        if($validated['email'] !== $user->email){
            return back()->withErrors(['email' => 'El correo electrónico no coincide con tu cuenta']);
        }
        
        // Verificar la contraseña
        if(!Hash::check($validated['password'], $user->password)){
            return back()->withErrors(['password' => 'La contraseña es incorrecta']);
        }
        
        DB::transaction(function () use ($user) {
            $userId = $user->id;

            if($user->avatar && Storage::disk('public')->exists($user->avatar)){
                Storage::disk('public')->delete($user->avatar);
            }

            // Eliminar grupos creados y sus avatares
            $user->ownedGroups()->each(function ($group) {
                if($group->avatar && Storage::disk('public')->exists($group->avatar)){
                    Storage::disk('public')->delete($group->avatar);
                }
                $group->delete();
            });

            // Eliminar publicaciones e imágenes
            $user->posts()->with('images')->chunkById(50, function ($posts) {
                foreach ($posts as $post) {
                    foreach ($post->images as $image) {
                        if($image->path && Storage::disk('public')->exists($image->path)) {
                            Storage::disk('public')->delete($image->path);
                        }
                    }
                    $post->delete();
                }
            });

            $user->games()->delete();
            $user->comments()->delete();
            $user->reactions()->delete();
            $user->sentMessages()->delete();
            $user->receivedMessages()->delete();

            Friendship::where('user_id', $userId)
                ->orWhere('friend_id', $userId)
                ->delete();

            GroupInvitation::where('sender_id', $userId)
                ->orWhere('recipient_id', $userId)
                ->delete();

            DB::table('group_user')->where('user_id', $userId)->delete();

            $user->delete();
        });
        
        // Cerrar sesión
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('landing')->with('status', 'Tu cuenta ha sido eliminada permanentemente');
    }
}

