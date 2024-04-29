<?php

return [

    'issuer' => env('JWT_ISSUER', 'http://example.com'),
    'key' => env('JWT_SECRET_KEY', 'example_key'),
    'access_ttl' => env('JWT_ACCESS_TTL', 60), // in minutes
    'refresh_ttl' => env('JWT_REFRESH_TTL', 43200), // in minutes
    /**
     * You can add a leeway to account for when there is a clock skew times between
     * the signing and verifying servers. It is recommended that this leeway should
     * not be bigger than a few minutes.
     *
     * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
     */
    'leeway' => env('JWT_LEEWAY', '60'), // in seconds

];
