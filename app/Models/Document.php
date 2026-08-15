<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    const TYPE_CV           = 'cv';           // السيرة الذاتية (مرحلة التقديم)
    const TYPE_ID_CARD      = 'id_card';      // صورة الهوية
    const TYPE_PHOTO        = 'photo';        // صورة شخصية
    const TYPE_BANK_INFO    = 'bank_info';    // بيانات بنكية
    const TYPE_PROFESSIONAL = 'professional'; // شهادات مهنية
    const TYPE_RESIGNATION_SUPPORT = 'resignation_support'; // مستندات داعمة للاستقالة الفورية


    const REQUIRED_FOR_ONBOARDING = [
        self::TYPE_ID_CARD,
        self::TYPE_PHOTO,
        self::TYPE_BANK_INFO,
    ];
    const ALL_TYPES = [
        self::TYPE_CV,
        self::TYPE_ID_CARD,
        self::TYPE_PHOTO,
        self::TYPE_BANK_INFO,
        self::TYPE_PROFESSIONAL,
        self::TYPE_RESIGNATION_SUPPORT,

    ];

    protected $fillable = [
        'owner_type',
        'owner_id',
        'type',
        'file_name',
        'file_path',
        'original_name',
    ];


    public function owner()
    {
        return $this->morphTo();
    }
}
