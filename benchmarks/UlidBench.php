<?php

namespace Attla\Ulid\Benchmark;

use Attla\Ulid\Ulid;

class UlidBench
{
    /**
     * @Revs(10000)
     */
    public function benchConsume()
    {
        Ulid::generate();
    }
}
