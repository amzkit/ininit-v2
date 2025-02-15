
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
    0  => [
        'id'            =>  0,
        'code'          =>  "created",
        'current_text'  =>  "สร้างคำสั่งซื้อแล้ว",
        'next_text'     =>  "รอที่อยู่ลูกค้า",
    ],
    1  => [
        'id'            =>  1,
        'code'          =>  "addressed",
        'current_text'  =>  "ได้รับที่อยู่ลูกค้าแล้ว",
        'next_text'     =>  "รอสลิป",
    ],
    2  => [
        'id'            =>  2,
        'code'          =>  "confirmed",
        'current_text'  =>  "ได้รับสลิปแล้ว",
        'next_text'     =>  "รอตรวจสอบสลิป",
    ],
    3  => [
        'id'            =>  3,
        'code'          =>  "verified",
        'current_text'  =>  "ยืนยันคำสั่งซื้อแล้ว",
        'next_text'     =>  "รอพิมพ์ใบปะหน้า",
    ],
    4  => [
        'id'            =>  4,
        'code'          =>  "denied",
        'current_text'  =>  "สลิปมีปัญหา",
        'next_text'     =>  "รอสลิป เนื่องจากสลิปมีปัญหา",
    ],
    5  => [
        'id'            =>  5,
        'code'          =>  "printed",
        'current_text'  =>  "พิมพ์ใบปะหน้าแล้ว",
        'next_text'     =>  "รอบรรจุสินค้า",
    ],
    6  => [
        'id'            =>  6,
        'code'          =>  "packed",
        'current_text'  =>  "บรรจุสินค้าแล้ว",
        'next_text'     =>  "รอจัดส่งสินค้า",
    ],
    7  => [
        'id'            =>  7,
        'code'          =>  "shipped",
        'current_text'  =>  "จัดส่งสินค้าแล้ว",
        'next_text'     =>  "รอลูกค้าได้รับสินค้า",
    ],
    8  => [
        'id'            =>  8,
        'code'          =>  "delivered",
        'current_text'  =>  "ลูกค้าได้รับสินค้าแล้ว",
        'next_text'     =>  "เสร็จสิ้น",
    ],

];
