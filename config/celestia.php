<?php

return [
    // Static bootstrap surface. Replace with an authoritative registry provider
    // without changing the v1 capability names or Celestia clients.
    'mesh' => [
        'heartbeat_ttl_seconds' => (int) env('CELESTIA_MESH_HEARTBEAT_TTL', 45),
        'election' => [
            'minimum_quorum' => (int) env('CELESTIA_MESH_MINIMUM_QUORUM', 1),
            'criteria' => ['operator_priority', 'health', 'capabilities', 'uptime', 'instance_id'],
        ],
    ],
    'updates' => [
        'channel' => env('CELESTIA_UPDATE_CHANNEL', 'stable'),
        'version' => env('CELESTIA_UPDATE_VERSION', '0.0.0'),
        'package_url' => env('CELESTIA_UPDATE_PACKAGE_URL'),
        'sha256' => env('CELESTIA_UPDATE_SHA256'),
        'signature_url' => env('CELESTIA_UPDATE_SIGNATURE_URL'),
        'minimum_version' => env('CELESTIA_UPDATE_MINIMUM_VERSION', '0.0.0'),
    ],
];
