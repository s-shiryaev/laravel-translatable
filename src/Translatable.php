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

            if (!is_null($translatedValue = $this->getAttribute("{$key}_$locale"))) {
                return $translatedValue;
            }

            if ($fallbackLocale && !is_null($defaultValue = $this->getAttribute("{$key}_$fallbackLocale"))) {
                return $defaultValue;
            }
        }

        return $this->getAttribute($key);
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @param bool $withTranslate FALSE to get original attributes without translation; defaults to TRUE
     * @return array
     */
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
                if (!is_null($translatedValue = $this->getAttribute("{$field}_$locale"))) {
                    $attributes[$field] = $translatedValue;
                    unset($attributes["{$field}_$locale"]);

                    continue;
                }

                if ($fallbackLocale && !is_null($defaultValue = $this->getAttribute("{$field}_$fallbackLocale"))) {
                    $attributes[$field] = $defaultValue;
                    unset($attributes["{$field}_$fallbackLocale"]);

                    continue;
                }

                if ($value = $this->getAttribute($field)) {
                    $attributes[$field] = $value;
                }
            }
        }

        return $attributes;
    }

    /**
     * Get the model's relationships in array form.
     *
     * @param bool $withTranslate FALSE to get original values without translation; defaults to TRUE
     * @return array
     */
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

    /**
     * Convert the model instance to an array.
     *
     * @param bool $withTranslate FALSE to get original values without translation; defaults to TRUE
     * @return array
     */
    public function toArray(bool $withTranslate = true): array
    {
        return array_merge($this->attributesToArray($withTranslate), $this->relationsToArray($withTranslate));
    }

    public function newCollection(array $models = []): TranslatableCollection
    {
        return new TranslatableCollection($models);
    }
}
