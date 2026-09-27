# -*- coding: utf-8 -*-
"""تحقق نهائي شامل من قاعدة amrtmco_business"""
import pymysql, sys, re
sys.stdout.reconfigure(encoding="utf-8")
conn = pymysql.connect(host="127.0.0.1", user="root", password="", database="amrtmco_business", charset="utf8mb4")
cur = conn.cursor()

cur.execute("""SELECT rc.TABLE_NAME, kcu.COLUMN_NAME, rc.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME, rc.DELETE_RULE
               FROM information_schema.REFERENTIAL_CONSTRAINTS rc
               JOIN information_schema.KEY_COLUMN_USAGE kcu
                 ON kcu.CONSTRAINT_NAME=rc.CONSTRAINT_NAME AND kcu.CONSTRAINT_SCHEMA=rc.CONSTRAINT_SCHEMA
               WHERE rc.CONSTRAINT_SCHEMA='amrtmco_business'
               ORDER BY rc.TABLE_NAME, kcu.COLUMN_NAME""")
rows = cur.fetchall()
print("=" * 100)
print("  قيود المفاتيح الأجنبية في القاعدة: %d" % len(rows))
print("=" * 100)
print("%-30s %-20s %-26s %-10s" % ("جدول الابن", "العمود", "جدول الأب", "الحذف"))
print("-" * 100)
bad = 0
for t, c, pt, pc, dr in rows:
    n = int(cur.execute("SELECT COUNT(*) FROM `%s` a LEFT JOIN `%s` b ON a.`%s`=b.`%s` WHERE a.`%s` IS NOT NULL AND b.`%s` IS NULL" % (t, pt, c, pc, c, pc)))
    cur.execute("SELECT COUNT(*) FROM `%s` a LEFT JOIN `%s` b ON a.`%s`=b.`%s` WHERE a.`%s` IS NOT NULL AND b.`%s` IS NULL" % (t, pt, c, pc, c, pc))
    orp = cur.fetchone()[0]
    flag = "" if orp == 0 else "   *** %d يتيم ***" % orp
    bad += orp
    print("%-30s %-20s %-26s %-10s%s" % (t, c, pt, dr, flag))
print("-" * 100)
print("إجمالي الصفوف اليتيمة:", bad)
print()
print("النتيجة:", "*** القاعدة سليمة 100% - جاهزة للتصدير ***" if bad == 0 else "*** فيه مشاكل ***")
conn.close()
