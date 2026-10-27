<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un lien File Drop partage un envoi entier : batch_uuid identifie le
     * groupe de fichiers déposés en une requête. Les lignes antérieures
     * (lien par fichier) sont rattachées à leur propre uuid.
     */
    public function up(): void
    {
        Schema::table('file_uploads', function (Blueprint $table): void {
            $table->uuid('batch_uuid')->nullable()->index()->after('uuid');
        });

        DB::table('file_uploads')
            ->whereNull('batch_uuid')
            ->update(['batch_uuid' => DB::raw('uuid')]);
    }

    public function down(): void
    {
        Schema::table('file_uploads', function (Blueprint $table): void {
            $table->dropColumn('batch_uuid');
        });
    }
};
