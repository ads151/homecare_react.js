<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('template', 40)->default('builder');
            $table->longText('content')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_system')->default(false);
            $table->string('focus_keyword')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('schema_type', 60)->nullable();
            $table->boolean('in_sitemap')->default(true);
            $table->unsignedTinyInteger('seo_score')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('badge')->nullable();
            $table->string('label')->nullable();
            $table->string('category')->nullable();
            $table->text('text')->nullable();
            $table->json('features')->nullable();
            $table->string('price_prefix')->nullable();
            $table->string('price')->nullable();
            $table->string('old_price')->nullable();
            $table->string('price_note')->nullable();
            $table->string('button_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('item')->nullable();
            $table->string('form_name')->nullable();
            $table->string('page_url', 500)->nullable();
            $table->json('data')->nullable();
            $table->string('ip', 60)->nullable();
            $table->string('status', 20)->default('new');
            $table->text('notes')->nullable();
            $table->boolean('mail_sent')->default(false);
            $table->text('mail_error')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('path');
            $table->string('alt')->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('services');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('settings');
    }
};
