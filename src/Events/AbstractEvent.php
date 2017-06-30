<?php

namespace Laravel\Achievements\Events;

use Illuminate\Queue\SerializesModels;

abstract class AbstractEvent
{
    use SerializesModels;

    /**
     * Achievement or criteria owner.
     *
     * @var mixed
     */
    public $owner;
}
