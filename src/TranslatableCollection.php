<?php

namespace SShiryaev\LaravelTranslatable;

use Illuminate\Database\Eloquent\Collection;

class TranslatableCollection extends Collection
{
    public function toArray(bool $withTranslate = true): array
    {
        return $this->map(
            static function ($value) use ($withTranslate) {
                return $value->toArray($withTranslate);
            }
        )->all();
    }
}
