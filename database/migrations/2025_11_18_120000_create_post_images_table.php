<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });

        // Migrar las imágenes existentes desde la columna antigua
        if (Schema::hasColumn('posts', 'image')) {
            $postsWithImages = DB::table('posts')
                ->whereNotNull('image')
                ->get(['id', 'image']);

            foreach ($postsWithImages as $post) {
                DB::table('post_images')->insert([
                    'post_id' => $post->id,
                    'path' => $post->image,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('posts', 'image')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->string('image')->nullable()->after('content');
            });

            $postImages = DB::table('post_images')
                ->select('post_id', DB::raw('MIN(id) as first_image_id'))
                ->groupBy('post_id')
                ->get();

            foreach ($postImages as $entry) {
                $imagePath = DB::table('post_images')
                    ->where('id', $entry->first_image_id)
                    ->value('path');

                if ($imagePath) {
                    DB::table('posts')
                        ->where('id', $entry->post_id)
                        ->update(['image' => $imagePath]);
                }
            }
        }

        Schema::dropIfExists('post_images');
    }
};

