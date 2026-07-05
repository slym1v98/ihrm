<?php

namespace App\Modules\Workflow\Application\Services;

use App\Modules\Workflow\Application\Contracts\AssigneeResolver;
use App\Modules\Workflow\Domain\Exceptions\WorkflowResolverNotFoundException;

final class ResolverRegistry
{
    private array $resolvers = [];

    public function register(AssigneeResolver $resolver): void
    {
        $this->resolvers[$resolver->key()] = $resolver;
    }

    public function get(string $key): AssigneeResolver
    {
        if (! isset($this->resolvers[$key])) {
            throw new WorkflowResolverNotFoundException("Không tìm thấy assignee resolver: {$key}");
        }

        return $this->resolvers[$key];
    }
}
