<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiEcosystemTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. API Clients (Registered client apps like Android, iOS, Web)
        if (!Schema::hasTable('api_clients')) {
            Schema::create('api_clients', function (Blueprint $table) {
                $table->increments('id');
                $table->string('client_id', 64)->unique();
                $table->string('client_secret_hash', 64);
                $table->string('name', 100);
                $table->string('platform', 30)->default('android'); // 'android', 'ios', 'web', 'third_party'
                $table->text('allowed_origins')->nullable();
                $table->integer('rate_limit_per_minute')->default(60);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. API Devices (Hardware / device instances)
        if (!Schema::hasTable('api_devices')) {
            Schema::create('api_devices', function (Blueprint $table) {
                $table->increments('id');
                $table->string('device_uuid', 100)->unique();
                $table->unsignedInteger('client_id')->nullable()->index();
                $table->string('platform', 30)->default('android');
                $table->string('device_model', 100)->nullable();
                $table->string('os_version', 50)->nullable();
                $table->string('app_version', 30)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('fcm_token', 255)->nullable();
                $table->boolean('is_blocked')->default(false);
                $table->string('block_reason', 255)->nullable();
                $table->timestamp('last_active_at')->nullable();
                $table->timestamps();

                $table->foreign('client_id')->references('id')->on('api_clients')->onDelete('set null');
            });
        }

        // 3. API Tokens (SHA-256 hashed bearer & refresh tokens)
        if (!Schema::hasTable('api_tokens')) {
            Schema::create('api_tokens', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('device_id')->index();
                $table->string('access_token_hash', 64)->unique();
                $table->string('refresh_token_hash', 64)->unique();
                $table->timestamp('access_expires_at')->nullable()->index();
                $table->timestamp('refresh_expires_at')->nullable()->index();
                $table->timestamp('revoked_at')->nullable();
                $table->string('last_used_ip', 45)->nullable();
                $table->timestamps();

                $table->foreign('device_id')->references('id')->on('api_devices')->onDelete('cascade');
            });
        }

        // 4. API Dynamic Settings
        if (!Schema::hasTable('api_settings')) {
            Schema::create('api_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->string('type', 20)->default('string'); // 'string', 'boolean', 'integer', 'json'
                $table->boolean('is_public')->default(false);
                $table->string('group', 50)->default('general');
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        // 5. API Telemetry & Audit Logs
        if (!Schema::hasTable('api_logs')) {
            Schema::create('api_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('device_id')->nullable()->index();
                $table->string('endpoint', 191)->index();
                $table->string('method', 10);
                $table->integer('status_code')->index();
                $table->integer('duration_ms')->default(0);
                $table->string('ip_address', 45)->index();
                $table->string('user_agent', 255)->nullable();
                $table->mediumText('request_payload')->nullable();
                $table->mediumText('response_summary')->nullable();
                $table->timestamp('created_at')->nullable()->index();
            });
        }

        // 6. API Download Jobs
        if (!Schema::hasTable('api_download_jobs')) {
            Schema::create('api_download_jobs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('job_uuid', 64)->unique();
                $table->unsignedInteger('device_id')->nullable()->index();
                $table->text('video_url');
                $table->string('video_title', 255)->nullable();
                $table->string('format_id', 100)->nullable();
                $table->string('format_type', 30)->default('video'); // 'video', 'audio'
                $table->string('quality_label', 50)->nullable();
                $table->string('status', 30)->default('pending')->index(); // 'pending', 'processing', 'completed', 'failed', 'expired'
                $table->string('download_token', 64)->nullable()->index();
                $table->timestamp('download_token_expires_at')->nullable();
                $table->string('file_path', 255)->nullable();
                $table->string('file_name', 255)->nullable();
                $table->bigInteger('file_size')->default(0);
                $table->integer('progress')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();

                $table->foreign('device_id')->references('id')->on('api_devices')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_download_jobs');
        Schema::dropIfExists('api_logs');
        Schema::dropIfExists('api_settings');
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('api_devices');
        Schema::dropIfExists('api_clients');
    }
}
