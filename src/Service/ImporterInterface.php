<?php

namespace App\Service;

interface ImporterInterface
{
    public function import();

    public function importOne();
}