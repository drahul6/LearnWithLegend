<?php

namespace v2\Jobs;

use v2\Jobs\Contracts\Job as JobInterface;


class Job
{


    public  static function schedule(JobInterface $job)
    {
        $job->schedule();
    }

    public  static function execute($db_job)
    {
        $payload = $db_job->DetailsArray;
        $class = $payload['class'];
        $job = (new $class);


        foreach ($payload['properties'] as $value) {

            if ($value['type'] == 'model') {
                $name = $value['name'];
                $$name = $value['value']::find($value['id']);
                $job->setUpWith($$name);
            } else {
                $name = $value['name'];
                $$name = $value['value'];
                $job->setUpWith($$name);
            }
        }
        $db_job->increment('attempts', 1);
        return   $job->execute() ? $db_job->markAsExecuted() :  $db_job->markAsFailed();
    }
}
