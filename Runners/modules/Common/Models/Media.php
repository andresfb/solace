<?php

declare(strict_types=1);

namespace Modules\Common\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Common\Libraries\MediaBasePath;

/**
 * @property-read int $id
 * @property-read string $model_type
 * @property-read int $model_id
 * @property-read string|null $uuid
 * @property-read string $collection_name
 * @property-read string $name
 * @property-read string $file_name
 * @property-read string|null $mime_type
 * @property-read string $disk
 * @property-read string|null $conversions_disk
 * @property-read int $size
 * @property-read string $manipulations
 * @property-read string $custom_properties
 * @property-read string $generated_conversions
 * @property-read string $responsive_images
 * @property-read int|null $order_column
 * @property-read CarbonImmutable|null $deleted_at
 * @property-read CarbonImmutable|null $created_at
 * @property-read CarbonImmutable|null $updated_at
 */
abstract class Media extends Model
{
    abstract protected function getMediaPath(): string;

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'order_column' => 'integer',
            'manipulations' => 'json',
            'custom_properties' => 'json',
            'generated_conversions' => 'json',
            'responsive_images' => 'json',
        ];
    }

    public function getFileInfo(): MediaItem
    {
        $mediaPath = MediaBasePath::getBasePath($this->model_id, $this->id, $this->collection_name);

        $filePath = Str::of($this->getMediaPath())
            ->append($mediaPath)
            ->append('/')
            ->append($this->file_name)
            ->toString();

        $data = $this->toArray();
        $data['filePath'] = $filePath;

        return MediaItem::from($data);
    }

    public function toArray(): array
    {
        return [
            'originalId' => $this->id,
            'originalName' => $this->name,
            'fileName' => $this->file_name,
            'mimeType' => $this->mime_type,
            'collectionName' => $this->collection_name,
            'fileSize' => $this->size,
        ];
    }
}
