<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Services;

use DateTime;
use SplFileInfo;

class BatchOptions
{
    private array $values;

    public function __construct(string $processor, string $file)
    {
        $this->values = [
            'file' => new SplFileInfo($file),
            'processor' => $processor
        ];
    }

    public function user(string $user = null): self
    {
        $this->values['user'] = $user;
        return $this;
    }

    public function schedule(DateTime $schedule = null): self
    {
        $this->values['schedule'] = $schedule;
        return $this;
    }

    public function __invoke(string $item): mixed
    {
        return $this->values[$item] ?? null;
    }
}
