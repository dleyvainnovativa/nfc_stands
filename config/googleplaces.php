<?php

return [
    // Google Cloud API key with "Places API (New)" enabled. Key-only — no OAuth.
    // Add GOOGLE_PLACES_API_KEY=... to your .env.
    'key' => env('GOOGLE_PLACES_API_KEY'),

    // Places API (New) Place Details endpoint. {place_id} is substituted.
    'endpoint' => 'https://places.googleapis.com/v1/places/',

    // Fields we request (keeps billing to the cheapest tier that includes reviews).
    'field_mask' => 'id,displayName,rating,userRatingCount,googleMapsUri,reviews',

    // Request timeout (seconds).
    'timeout' => 10,

    // Write-a-review deep link (no API). {place_id} substituted.
    'write_review_url' => 'https://search.google.com/local/writereview?placeid=',
];
