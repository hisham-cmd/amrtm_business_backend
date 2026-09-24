# -*- coding: utf-8 -*-
import sys, os
project_root = r'C:\react_projects\amrtm_business'
sys.path.insert(0, os.path.join(project_root, '.agent', 'workflows'))

from auto_release_generator import fill_docx_template, ARABIC_DAYS
from datetime import datetime

template_path = os.path.join(project_root, "إصدار رقم.docx")
reports_dir = os.path.join(project_root, ".agent", "reports")
version = "1.0.0"

release_date = datetime.now()
date_str = release_date.strftime("%Y-%m-%d")
time_str = release_date.strftime("%I:%M %p").replace("AM", "ص").replace("PM", "م")
day_str = ARABIC_DAYS[release_date.weekday()]

description = (
    "هذا الإصدار يمثل إطلاق منصة آمر تم لإدارة الطلبات والخدمات الحكومية وقطاع الأعمال. "
    "يشمل النظام واجهة مستخدم متكاملة مع نمط التصميم المؤسسي، ونظام تسجيل متعدد الأدوار، "
    "ودليل المستشارين، ونظام إدارة المنشآت والعقود."
)

features_formatted = """⭐ الميزات الجديدة:
  • إطلاق منصة آمر تم لإدارة الطلبات والخدمات الحكومية وقطاع الأعمال
  • تصميم نظام Corporate-UI متكامل مع بطاقات زجاجية وتأثيرات متدرجة
  • توحيد النافبار الاحترافي عبر جميع الصفحات مع دعم اللغتين
  • إنشاء دليل المستشارين مع البحث والفلترة والعدّادات
  • نظام تسجيل متعدد الأدوار (مقدم خدمة / عميل طالب خدمة)
  • نظام تسجيل المنشآت مع التخصصات والخدمات
  • نظام تسجيل العقود المستقل

⚡ التحسينات والتعديلات:
  • تحسين تجربة المستخدم على الأجهزة المحمولة
  • تحسين أداء تحميل الصور بأسماء UUID آمنة
  • توحيد القوالب عبر جميع الصفحات

🔧 إصلاح الأخطاء والمشاكل:
  • إصلاح صور الوزارات (404 على الإنتاج) بسبب الأسماء العربية
  • إصلاح مشكلة تسجيل الدخول (500 Internal Server Error)
  • إصلاح مشكلة رفع المرفقات (415 Unsupported Media Type)

⚙️ تحديثات تقنية وبنية تحتية:
  • بناء المشروع على Laravel 12 مع PHP 8.2
  • إضافة Tailwind CSS v4 مع Flowbite
  • إنشاء هجرات قاعدة البيانات للتوسع المستقبلي
  • إضافة اختبارات PHPUnit للميزات الجديدة"""

output_docx_path = os.path.join(reports_dir, f"إصدار رقم {version}.docx")

fill_docx_template(
    template_path,
    output_docx_path,
    version,
    date_str,
    time_str,
    day_str,
    description,
    features_formatted,
    "تطبيق ويب (Laravel)",
    ["هشام قاسم الصغير"],
    ["هشام الصغير"],
    "هشام قاسم الصغير",
    "هشام الصغير",
    "هشام قاسم الصغير"
)

print(f"[Success] DOCX generated: {output_docx_path}")
