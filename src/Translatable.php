<?php

namespace SShiryaev\LaravelTranslatable;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

trait Translatable
{
    public function __get($key)
    {
        if (
            property_exists($this, 'translatable')
            && is_array($this->translatable)
            && in_array($key, $this->translatable)
        ) {
            $locale = App::getLocale();
            $fallbackLocale = config('app.fallback_locale');

            if ($translatedValue = parent::__get("{$key}_$locale")) {
                return $translatedValue;
            }

            if ($defaultValue = parent::__get("{$key}_$fallbackLocale")) {
                return $defaultValue;
            }
        }

        return parent::__get($key);
    }

    public function attributesToArray(bool $withTranslate = true): array
    {
        $locale = App::getLocale();
        $fallbackLocale = config('app.fallback_locale');
        $attributes = parent::attributesToArray();

        if (!$withTranslate) {
            return $attributes;
        }

        if (property_exists($this, 'translatable') && is_array($this->translatable)) {
            foreach ($this->translatable as $field) {
                if ($translatedValue = parent::__get("{$field}_$locale")) {
                    $attributes[$field] = $translatedValue;
                    unset($attributes["{$field}_$locale"]);

                    continue;
                }

                if ($defaultValue = parent::__get("{$field}_$fallbackLocale")) {
                    $attributes[$field] = $defaultValue;
                    unset($attributes["{$field}_$fallbackLocale"]);

                    continue;
                }

                if ($value = parent::__get($field)) {
                    $attributes[$field] = $value;
                }
            }
        }

        return $attributes;
    }

    public function relationsToArray(bool $withTranslate = true): array
    {
        $attributes = [];

        foreach ($this->getArrayableRelations() as $key => $value) {
            if ($value instanceof Arrayable) {
                $relation = $value->toArray($withTranslate);
            } elseif (is_null($value)) {
                $relation = $value;
            }

            if (static::$snakeAttributes) {
                $key = Str::snake($key);
            }

            if (isset($relation) || is_null($value)) {
                $attributes[$key] = $relation;
            }

            unset($relation);
        }

        return $attributes;
    }

    public function toArray(bool $withTranslate = true): array
    {
        return array_merge($this->attributesToArray($withTranslate), $this->relationsToArray($withTranslate));
    }

    public function newCollection(array $models = []): TranslatableCollection
    {
        return new TranslatableCollection($models);
    }
}
