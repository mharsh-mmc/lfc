<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Optimization Settings
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings for database optimization
    | including query caching, connection pooling, and performance tuning.
    |
    */

    'query_cache' => [
        'enabled' => env('DB_QUERY_CACHE_ENABLED', true),
        'ttl' => env('DB_QUERY_CACHE_TTL', 3600), // 1 hour
        'prefix' => env('DB_QUERY_CACHE_PREFIX', 'db_query_'),
    ],

    'connection_pooling' => [
        'enabled' => env('DB_CONNECTION_POOLING_ENABLED', true),
        'max_connections' => env('DB_MAX_CONNECTIONS', 10),
        'min_connections' => env('DB_MIN_CONNECTIONS', 2),
    ],

    'query_optimization' => [
        'select_only_required_columns' => true,
        'use_indexes' => true,
        'limit_results' => true,
        'max_results_per_query' => 1000,
    ],

    'caching' => [
        'model_cache' => [
            'enabled' => true,
            'ttl' => 1800, // 30 minutes
        ],
        'relationship_cache' => [
            'enabled' => true,
            'ttl' => 900, // 15 minutes
        ],
    ],

    'performance_monitoring' => [
        'enabled' => env('DB_PERFORMANCE_MONITORING', false),
        'log_slow_queries' => env('DB_LOG_SLOW_QUERIES', true),
        'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1000), // milliseconds
    ],

    'indexes' => [
        'auto_create' => env('DB_AUTO_CREATE_INDEXES', false),
        'suggested_indexes' => [
            'deceased_profiles' => [
                'idx_public_death_date' => ['is_public', 'death_date'],
                'idx_creator_created' => ['created_by', 'created_at'],
                'idx_name' => ['name'],
                'idx_dates' => ['birth_date', 'death_date'],
                'idx_fulltext_search' => ['name', 'memorial_message', 'biography'],
            ],
            'users' => [
                'idx_email' => ['email'],
                'idx_created_at' => ['created_at'],
            ],
            'media' => [
                'idx_model_type' => ['model_type', 'model_id'],
                'idx_collection' => ['collection_name'],
            ],
        ],
    ],

    'maintenance' => [
        'auto_optimize' => env('DB_AUTO_OPTIMIZE', false),
        'optimize_schedule' => env('DB_OPTIMIZE_SCHEDULE', '0 2 * * 0'), // Weekly at 2 AM
        'vacuum_tables' => env('DB_VACUUM_TABLES', true),
        'analyze_tables' => env('DB_ANALYZE_TABLES', true),
    ],
];
