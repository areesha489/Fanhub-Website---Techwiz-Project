<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fan Hub Assistant
    |--------------------------------------------------------------------------
    |
    | Powers the floating AI chat widget. When no API key is configured the
    | assistant falls back to the offline knowledge base stored in the
    | chatbot_knowledge table, so the feature always works out of the box.
    |
    */

    'provider' => env('AI_PROVIDER', 'openai'),

    'api_key' => env('AI_API_KEY'),

    'model' => env('AI_MODEL', 'gpt-4o-mini'),

    'base_url' => env('AI_BASE_URL', 'https://api.openai.com/v1'),

    'max_tokens' => (int) env('AI_MAX_TOKENS', 512),

    'temperature' => (float) env('AI_TEMPERATURE', 0.7),

    'enabled' => env('CHATBOT_ENABLED', true),

    'timeout' => (int) env('AI_TIMEOUT', 20),

];
