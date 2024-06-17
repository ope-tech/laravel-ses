<?php

namespace OpeTech\LaravelSes\Contracts;

interface Batchable
{
    public function getBatch(): string;
}
