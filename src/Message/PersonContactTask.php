<?php

namespace App\Message;

class PersonContactTask
{
    public function __construct(
        private string $externalId,
    ) {
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }
}