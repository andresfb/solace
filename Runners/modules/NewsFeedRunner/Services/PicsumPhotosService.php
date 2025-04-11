<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Modules\Common\Traits\ImageCatchable;
use Modules\Common\Traits\Screenable;
use Modules\NewsFeedRunner\Dtos\PicsumItem;
use RuntimeException;

class PicsumPhotosService
{
    use ImageCatchable;
    use Screenable;

    private int $maxCalls = 5;

    private int $calls = 1;

    public function getImage(): PicsumItem
    {
        try {
            return $this->getServiceImage();
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return PicsumItem::empty();
        }
    }

    /**
     * @throws Exception
     */
    private function getServiceImage(): PicsumItem
    {
        $response = $this->callApi();
        if (! $response instanceof Response) {
            $this->error('We did not get an image from Picsum');

            return PicsumItem::empty();
        }

        $this->line(sprintf('Api call finished at %s', now()));

        $responses = collect($response->json());
        $maxUsages = config('picsum-article-importer.max_empty_runs');

        foreach ($responses as $result) {
            $item = PicsumItem::fromResponse($result);

            $image = $this->checkImage(
                $item->downloadUrl,
                $maxUsages
            );

            if ($image === '') {
                continue;
            }

            if ($image === '0') {
                continue;
            }

            $item->imageUrl = $image;

            return $item;
        }

        return PicsumItem::empty();
    }

    private function callApi(): ?Response
    {
        try {
            $page = random_int(1, config('picsum-article-importer.max_page_number'));

            $url = sprintf(
                config('picsum-article-importer.api_url'),
                $page
            );

            $this->line('Calling the picsum.photos API '.now());

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->timeout(60)
                ->get($url);

            if ($response->failed()) {
                $this->error('Error found '.$response->body());

                throw new RuntimeException($response->body());
            }

            return $response;
        } catch (Exception) {
            if ($this->calls >= $this->maxCalls) {
                return null;
            }

            $this->calls++;

            return $this->callApi();
        }
    }
}
