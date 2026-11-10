<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assistance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('demarche_id')->nullable()->constrained('demarches')->nullOnDelete();
            $table->string('demarche_title');
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('nationality')->nullable();
            $table->string('location_status')->nullable();
            $table->string('city_country')->nullable();
            $table->string('purpose');
            $table->text('details')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('admin_feedback')->nullable();
            $table->string('admin_attachment_url')->nullable();
            $table->string('admin_attachment_name')->nullable();
            $table->string('admin_attachment_public_id')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('assistance_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistance_request_id')
                ->constrained('assistance_requests')
                ->cascadeOnDelete();
            $table->string('doc_key')->nullable();
            $table->string('doc_label');
            $table->string('file_url');
            $table->string('public_id')->nullable();
            $table->string('resource_type')->default('auto');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assistance_documents');
        Schema::dropIfExists('assistance_requests');
    }
};

