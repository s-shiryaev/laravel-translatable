<?php

namespace SShiryaev\LaravelTranslatable;

use Illuminate\Database\Eloquent\Collection;

class TranslatableCollection extends Collection
{
    public function toArray(bool $withTranslate = true): array
    {
        return $this->map(
            static function ($value) use ($withTranslate): array {
                return $value->toArray($withTranslate); // @phpstan-ignore-line
            }
        )->all();
    }
}
