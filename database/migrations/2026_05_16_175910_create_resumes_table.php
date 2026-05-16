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
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');                           // e.g. "Senior PM at Grab"
            $table->string('target_role')->nullable();
            $table->text('job_description')->nullable();       // pasted JD for ATS optimisation
            $table->json('resume_data');                       // structured JSON from AI
            $table->longText('raw_content')->nullable();       // plain text version for ATS
            $table->string('status')->default('draft');        // draft | complete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
