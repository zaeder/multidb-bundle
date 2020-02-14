<?php

namespace Zaeder\MultiDbBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class MultiDbEvent extends Event
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}