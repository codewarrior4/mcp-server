<?php

namespace App\MCP\Actions;

use App\MCP\Contracts\ToolInterface;
use App\MCP\Contracts\ToolRegistryInterface;
use App\MCP\Exceptions\InvalidToolConfigurationException;
use App\MCP\Support\ToolDiscovery;
use Illuminate\Contracts\Container\Container;

class RegisterConfiguredTools
{
    public function __construct(private Container $container) {}

    public function handle(ToolRegistryInterface $registry): void
    {
        $toolClasses = ToolDiscovery::fromConfig(config('mcp.tool_discovery.tools', []));

        foreach ($toolClasses as $toolClass) {
            $tool = $this->container->make($toolClass);

            if (! $tool instanceof ToolInterface) {
                throw InvalidToolConfigurationException::forClass($toolClass);
            }

            $registry->register($tool);
        }
    }
}
