<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Workflow Resolvers
    |--------------------------------------------------------------------------
    |
    | Register workflow resolvers per template. The key is the template KEY
    | (the first three uppercase characters of a task `key`) and the value
    | must be a class implementing the WorkflowResolver contract.
    |
    | A WorkflowResolver decides:
    |   - Which stage transitions are allowed (allowedTransitions/canTransitionTo)
    |   - The error message for denied transitions (message)
    |   - An optional custom priority score for tasks (priorityScore)
    |
    | Example:
    | 'TEC' => \App\Resolvers\TechnicalSupportWorkflowResolver::class,
    |
    */

    'workflow_resolvers' => [
        // 'TEMPLATE_KEY' => \App\Resolvers\WorkflowResolver::class,
    ],

];
