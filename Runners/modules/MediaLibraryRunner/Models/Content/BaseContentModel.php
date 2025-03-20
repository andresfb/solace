<?php

namespace Modules\MediaLibraryRunner\Models\Content;

use Modules\MediaLibraryRunner\Models\BaseMediaRunnerModel;

abstract class BaseContentModel extends BaseMediaRunnerModel
{
    abstract public static function getRandom(): ?self;
}
