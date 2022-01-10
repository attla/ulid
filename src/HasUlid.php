<?php

namespace Attla\Ulid;

trait HasUlid
{
    /**
     * Boot the trait on the model
     *
     * @return void
     */
    protected static function bootHasUlid()
    {
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = Ulid::generate();
            }
        });

        static::saving(function ($model) {
            $originalUlid = $model->getOriginal('id');
            if ($originalUlid && $originalUlid !== $model->id) {
                $model->id = $originalUlid;
            }
        });
    }

    /**
     * Get the value indicating whether the IDs are incrementing
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Get the auto-incrementing key type
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }
}
