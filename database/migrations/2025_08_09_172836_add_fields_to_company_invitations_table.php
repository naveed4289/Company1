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
        Schema::table('company_invitations', function (Blueprint $table) {
            $table->string('generated_password')->nullable()->after('token');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->after('generated_password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_invitations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['generated_password', 'user_id']);
        });
    }
};
