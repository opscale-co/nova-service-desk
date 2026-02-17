<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Priority Score Resolvers
    |--------------------------------------------------------------------------
    |
    | Register custom priority score resolvers for specific templates.
    | The key should be the template KEY and the value should be a class
    | implementing the PriorityScoreResolver contract.
    |
    | Example:
    | 'INC' => \App\Services\IncidentPriorityResolver::class,
    |
    */

    'priority_score_resolvers' => [
        // 'TEMPLATE_KEY' => \App\Services\CustomPriorityResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Statuses Resolvers
    |--------------------------------------------------------------------------
    |
    | Register custom statuses resolvers for specific templates.
    | The key should be the template KEY and the value should be a class
    | implementing the StatusesResolver contract.
    |
    | Example:
    | 'INC' => \App\Services\IncidentStatusesResolver::class,
    |
    */

    'custom_statuses_resolvers' => [
        // 'TEMPLATE_KEY' => \App\Services\StatusesResolver::class,
    ],

];
