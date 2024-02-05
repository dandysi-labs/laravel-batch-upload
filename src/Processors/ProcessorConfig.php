<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Processors;

use Illuminate\Support\Arr;
use RuntimeException;

class ProcessorConfig
{
    private array $options;
    private array $columns = [];
    private array $rules = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function column(string $key, string $name, string|array $validationRules = null): self
    {
        $this->columns[$key] = $name;
        if ($validationRules) {
            $this->rules[$key] = $validationRules;
        }
        return $this;
    }

    private function columns(): array
    {
        if (empty($this->columns)) {
            throw new RuntimeException('No columns defined');
        }

        return $this->columns;
    }

    public function option(string $key, string|bool $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function __invoke(string $name, mixed $default = null): mixed
    {
        if ('columns' === $name) {
            return $this->columns();
        } elseif ('rules' === $name) {
            return $this->rules;
        }

        return Arr::get($this->options, $name, $default);
    }

    public static function create(): self
    {
        return new self(config('batch-upload.processor_options', []));
    }
}
