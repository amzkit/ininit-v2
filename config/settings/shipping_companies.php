<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */
    0 => [
        'id'        =>  "flash",
        'company'   =>   "Flash Express",
        'th'        =>  "Flash แฟลช เอกซ์เพลส",
        'en'        =>  "Flash Express",
        'code'      =>  "flash",
        "color"     =>  "#f3e42b",
        'tracking_url'  => "https://www.flashexpress.co.th/tracking/?se="
    ],
    1 => [
        'id'        =>  "jandt",
        'company'   =>  "J&T",
        'th'        =>  "J&T เจแอนด์ที แอกซ์เพลส",
        'en'        =>  "J&T Express",
        'code'      =>  "j&t",
        "color"     =>  "#c91416",
        'tracking_url'  => 'https://www.jtexpress.co.th/index/query/gzquery.html?bills=',
    ],
    2 => [
        'id'        =>  "kerry",
        'company'   =>  "Kerry Express",
        'th'        =>  "Kerry เคอรี่ เอกซ์เพลส",
        'en'        =>  "Kerry Express",
        'code'      =>  "kerry",
        "color"     =>  "#f26c23",
        'tracking_url'  => 'https://th.kerryexpress.com/th/track/?track=',
    ],
    3  => [
        'id'        =>  "ems",
        'company'   =>  "Thailand Post",
        'th'        =>  "EMS ไปรษณีย์ไทย พัสดุด่วน",
        'en'        =>  "Thailand Post Express Mail Service",
        'code'      =>  "ems",
        "color"     =>  "#ffffff",
        'tracking_url'  => "https://track.thailandpost.co.th/dashboard?trackNumber="
    ],
    4  => [
        'id'        =>  "rms",
        'company'   =>  "Thailand Post",
        'th'        =>  "ไปรษณีย์ไทย ลงทะเบียน",
        'en'        =>  "Thailand Post Registered Mail Service",
        'code'      =>  "rms",
        "color"     =>  "#ffffff",
        'tracking_url'  => "https://track.thailandpost.co.th/dashboard?trackNumber="
    ],
    5  => [
        'id'        =>  "lex",
        'company'   =>  "Lazada Express",
        'th'        =>  "LEX ลาซาด้า เอกซ์เพรส",
        'en'        =>  "Lazada Express",
        'code'      =>  "lex",
        "color"     =>  "#ffffff",
        'tracking_url'  => ""
    ],
    6  => [
        'id'        =>  "spx",
        'company'   =>  "Shopee Express",
        'th'        =>  "SPX ช้อปปี้ เอกซ็เพรส",
        'en'        =>  "Shopee Express",
        'code'      =>  "spx",
        "color"     =>  "#ffffff",
        'tracking_url'  => ""
    ],
    9 => [
        'id'        =>  'other',
        'company'   =>  "Seller Own Fleet",
        'th'        =>  "จัดส่งโดยผู้ขาย",
        'en'        =>  "Seller Own Fleet",
        'code'      =>  "cod",
        "color"     =>  "#ffffff",
        'tracking_url'  => '',
    ],

];
