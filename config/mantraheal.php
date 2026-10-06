<?php

return [
    'company_name' => env('APP_NAME', 'MantraHeal'),
    'tagline' => 'Ayurvedic & Healthcare E-Commerce CRM',
    'support_email' => env('MAIL_FROM_ADDRESS', 'support@mantraheal.com'),
    'support_phone' => '+91 98765 43210',
    'currency' => '₹',
    'currency_code' => 'INR',
    'gstin' => '07AAAAA0000A1Z5',
    
    'lead_sources' => [
        'Website',
        'Shopify',
        'Meta Ads',
        'Instagram',
        'Google Ads',
        'WhatsApp',
        'Phone',
        'Referral',
        'Offline',
        'Existing Customer',
        'Manual',
    ],

    'lead_stages' => [
        'New',
        'Contacted',
        'Interested',
        'Follow-up',
        'Quotation Sent',
        'Order Confirmed',
        'Payment Pending',
        'Won',
        'Lost',
    ],

    'lost_reasons' => [
        'Price',
        'Not Interested',
        'Competitor',
        'No Response',
        'Wrong Number',
        'Product Unavailable',
        'Timing',
        'Other',
    ],

    'call_outcomes' => [
        'Connected',
        'Interested',
        'Order Taken',
        'Follow-up Required',
        'Not Interested',
        'Busy',
        'No Answer',
        'Wrong Number',
        'Complaint',
        'Payment Discussion',
    ],

    'order_statuses' => [
        'New',
        'Confirmed',
        'Processing',
        'Packed',
        'Shipped',
        'Out for Delivery',
        'Delivered',
        'Cancelled',
        'Returned',
        'Refunded',
        'RTO',
    ],

    'payment_statuses' => [
        'Pending',
        'Paid',
        'Partially Paid',
        'Failed',
        'Refunded',
    ],

    'payment_methods' => [
        'COD' => 'Cash on Delivery',
        'Razorpay' => 'Razorpay Gateway',
        'Prepaid UPI' => 'UPI (Google Pay/PhonePe/Paytm)',
        'Net Banking' => 'Net Banking',
        'Bank Transfer' => 'Direct Bank Transfer',
    ],

    'couriers' => [
        'Delhivery',
        'Blue Dart',
        'Shiprocket',
        'DTDC',
        'XpressBees',
        'Shadowfax',
        'India Post',
    ],

    'return_reasons' => [
        'Damaged',
        'Wrong product',
        'Customer changed mind',
        'Product issue',
        'Delivery issue',
        'Other',
    ],

    'rto_reasons' => [
        'Customer Refused COD',
        'Customer Not Available',
        'Incomplete Address',
        'Phone Unreachable',
        'Delivery Delayed',
        'Self Cancellation',
    ],
];
