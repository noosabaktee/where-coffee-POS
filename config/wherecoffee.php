<?php

return [
    'demo_reset_enabled' => filter_var(env('WHERE_COFFEE_DEMO_RESET', false), FILTER_VALIDATE_BOOL),
];
