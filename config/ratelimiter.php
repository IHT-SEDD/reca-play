<?php

return [
 'scan-qr' => env('RATE_LIMIT_SCAN_QR', 100),
 'add-data-creator' => env('RATE_LIMIT_ADD_CREATOR', 100),
 'stop-record' => env('RATE_LIMIT_STOP_RECORD', 50),
];
