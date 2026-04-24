<?php

return [
    'credential_period' => env('EXAM_CREDENTIAL_PERIOD', now()->format('ym')),
];
