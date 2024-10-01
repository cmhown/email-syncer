<?php
declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class CreateFoldersIndex implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Index::create('email_folders', function (Mapping $mapping, Settings $settings) {
            $mapping->keyword('id');
            $mapping->keyword('user_id');
            $mapping->keyword('oauth_id');
            $mapping->keyword('provider');
            $mapping->keyword('date');
        });
    }

    /**
     * Reverse the migration.
     */ 
    public function down(): void
    {
        Index::dropIfExists('email_folders');
    }
}
