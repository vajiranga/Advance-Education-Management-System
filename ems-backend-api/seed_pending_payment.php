<?php
// seed_pending_payment.php

use App\Models\Payment;
use Illuminate\Support\Carbon;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

    'status' => 'pending',
    'paid_at' => null,
    'note' => 'Manual Seeded Payment for Testing',
    'slip_image' => null // No image for this test case
]);

echo "Pending Payment Created: ID {$payment->id}\n";
