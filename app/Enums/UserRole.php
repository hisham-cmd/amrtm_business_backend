<?php

namespace App\Enums;

enum UserRole: string
{
    case Supervisor = 'supervisor';  // مشرف النظام — صلاحيات كاملة
    case Manager    = 'manager';     // مدير المشروع
    case Agent      = 'agent';       // مندوب
    case Owner      = 'owner';       // مالك القاعة
    case Partner    = 'partner';     // شريك خدمات
    case Officiant  = 'officiant';   // مأذون شرعي
    case User       = 'user';        // مستخدم عادي

    public function label(): string
    {
        return match($this) {
            self::Supervisor => 'مشرف النظام',
            self::Manager    => 'مدير المشروع',
            self::Agent      => 'مندوب',
            self::Owner      => 'مالك القاعة',
            self::Partner    => 'شريك خدمات',
            self::Officiant  => 'مأذون شرعي',
            self::User       => 'مستخدم',
        };
    }

    /** Roles that have access to privileged dashboards */
    public function isStaff(): bool
    {
        return in_array($this, [self::Supervisor, self::Manager, self::Agent, self::Owner, self::Partner, self::Officiant]);
    }
}
