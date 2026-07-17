<?php

// Dev and prod both point at the ToyyibPay sandbox for this project — no live
// merchant account exists, so the fallback below must never be the real
// https://toyyibpay.com host.
return[
   'key'=> env('TOYYIBPAY_KEY'),
   'category' => env('TOYYIBPAY_CATEGORY'),
    'base_url' => env('TOYYIBPAY_BASE_URL', 'https://dev.toyyibpay.com'),
];