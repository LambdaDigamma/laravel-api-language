<?php

return [

    'supported_locales' => ['en'],

    'fallback_locale' => null,

    'use_autoscan_lang_folder' => false,

    'cache' => [
        'supported_locales_key' => 'api-language.supported-locales',
    ],

    'request_attributes' => [
        'accepted_locales' => 'api_language.accepted_locales',
        'excluded_locales' => 'api_language.excluded_locales',
        'resolved_locale' => 'api_language.resolved_locale',
        'result' => 'api_language.result',
    ],

    'prefer_user_locale' => true,

    'user_locale_attribute' => 'locale',

    'automatic_vary_header' => true,

];
