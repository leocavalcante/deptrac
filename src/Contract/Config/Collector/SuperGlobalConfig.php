<?php

namespace Qossmic\Deptrac\Contract\Config\Collector;

use Qossmic\Deptrac\Contract\Config\CollectorConfig;
use Qossmic\Deptrac\Contract\Config\CollectorType;

final class SuperGlobalConfig extends CollectorConfig
{
    protected CollectorType $collectorType = CollectorType::TYPE_SUPERGLOBAL;

    /**
     * @param string[] $config
     */
    private function __construct(
        protected array $config,
    ) {
    }

    public static function create(string ...$config): self
    {
        return new self($config);
    }

    public function private(): self
    {
        $this->private = true;

        return $this;
    }

    /**
     * @return array{'private': bool, 'type': string, 'value': string[]}
     */
    public function toArray(): array
    {
        return [
            'private' => $this->private,
            'type' => CollectorType::TYPE_SUPERGLOBAL->value,
            'value' => $this->config,
        ];
    }
}
