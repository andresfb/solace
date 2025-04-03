<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\Cyphers\CypherFactory;
use App\Models\Riddles\Riddle;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Modules\Common\Traits\Screenable;
use RuntimeException;

class RiddlesService
{
    use Screenable;

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $category = (string) collect(Config::array('riddles.categories'))->random();

        $this->line('Loading category: '.$category);

        $endPoint = sprintf(
            Config::string('riddles.endpoint'),
            $category,
            Config::integer('riddles.max_items'),
        );

        $this->saveData(
            $category,
            $this->callApi($endPoint)
        );
    }

    /**
     * @throws Exception
     */
    private function callApi(string $url): Collection
    {
        $this->line('Calling '.$url);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->connectTimeout(60)
        ->timeout(120)
        ->get($url);

        if ($response->failed()) {
            throw new RuntimeException($response->body());
        }

        $data = $response->json();

        if (! array_key_exists('riddlesArray', $data)) {
            throw new RuntimeException('Invalid data');
        }

        $this->line('Got response from API');

        return collect($data['riddlesArray']);
    }

    private function saveData(string $category, Collection $riddles): void
    {
        $this->line('Saving the riddles...');

        foreach ($riddles as $riddle) {
            $hash = md5(sprintf(
                '%s:/%s:/%s',
                $category,
                $riddle['riddle'],
                $riddle['answer'],
            ));

            if (Riddle::where('hash', $hash)->exists()) {
                $this->character('x');

                continue;
            }

            $this->character('.');

            $cypherItem = CypherFactory::encodeWithRandom($riddle['riddle']);

            Riddle::create([
                'hash' => $hash,
                'cypher_id' => $cypherItem->id,
                'category' => $category,
                'question' => $riddle['riddle'],
                'encoded' => $cypherItem->encodedText,
                'answer' => $riddle['answer'],
            ]);
        }

        $this->line('');
    }
}
