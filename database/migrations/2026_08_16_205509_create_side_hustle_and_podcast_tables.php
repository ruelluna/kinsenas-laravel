<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('side_hustle_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('side_hustles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('side_hustle_category_id')->constrained('side_hustle_categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('cover_image_url')->nullable();
            $table->string('difficulty');
            $table->string('capital_tier');
            $table->unsignedInteger('startup_capital_min')->nullable();
            $table->unsignedInteger('startup_capital_max')->nullable();
            $table->unsignedSmallInteger('time_commitment_hours_min')->nullable();
            $table->unsignedSmallInteger('time_commitment_hours_max')->nullable();
            $table->json('skills')->nullable();
            $table->json('equipment')->nullable();
            $table->string('publish_scope')->default('internal');
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'publish_scope', 'published_at']);
            $table->index(['side_hustle_category_id', 'sort_order']);
        });

        Schema::create('podcast_shows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('podcast_episodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('podcast_show_id')->constrained('podcast_shows')->cascadeOnDelete();
            $table->unsignedInteger('episode_number');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('show_notes')->nullable();
            $table->string('audio_embed_url')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('publish_scope')->default('internal');
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['podcast_show_id', 'episode_number']);
            $table->index(['status', 'publish_scope', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('podcast_episodes');
        Schema::dropIfExists('podcast_shows');
        Schema::dropIfExists('side_hustles');
        Schema::dropIfExists('side_hustle_categories');
    }
};
