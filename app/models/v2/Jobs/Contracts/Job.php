<?php

namespace v2\Jobs\Contracts;



interface Job
{

    public  function schedule();

    public  function execute();
}
