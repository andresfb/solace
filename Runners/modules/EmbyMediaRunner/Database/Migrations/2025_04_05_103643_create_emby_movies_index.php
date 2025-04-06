<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Meilisearch\Client;

return new class extends Migration
{
    public function up(): void
    {
        $client = new Client(
            Config::string('meilisearch.host'),
            Config::string('meilisearch.key'),
        );

        $indexName = Config::string('meilisearch.movies_index');

        // Create index if it doesn't exist
        try {
            $client->getIndex($indexName);
        } catch (\Exception) {
            $client->createIndex($indexName, ['primaryKey' => 'Id']);
        }

        // Update settings
        $index = $client->index($indexName);

        $index->updateFilterableAttributes(['Genres', 'ProductionYear']);
        $index->updateSortableAttributes(['Name', 'CriticRating', 'ProductionYear']);
        $index->updateSearchableAttributes(['Name', 'Overview', 'People', 'Genres', 'Taglines', 'ProductionYear']);
    }

    public function down(): void
    {
        $client = new Client(
            Config::string('meilisearch.host'),
            Config::string('meilisearch.key'),
        );

        $client->deleteIndex(
            Config::string('meilisearch.movies_index')
        );
    }
};
