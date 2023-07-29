<?php

namespace v2\Jobs\Traits;

use v2\Models\Job as ModelJob;
use Illuminate\Database\Eloquent\Model;




/**
 * 
 */
trait Job
{

    public  function schedule()
    {
        $models = [];
        foreach (get_object_vars($this) as $property => $value) {
            $is_model = $this->$property instanceof Model;
            $type = $is_model ? 'model' : 'not_model';
            $models[] = [
                'name' => $property,
                'type' => $type,
                'value' => $is_model ? get_class($this->$property) : $value,
                'id' => $is_model ? $this->$property->getKey() : null,
            ];
        }

        $payload = [
            'properties' => $models,
            'class' => static::class,
        ];

        $job =  ModelJob::create([
            'payload' => json_encode($payload),
            'attempts' => 0
        ]);
    }

    public function execute($job)
    {
    }
}
