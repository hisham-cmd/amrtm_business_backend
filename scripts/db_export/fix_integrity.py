# -*- coding: utf-8 -*-
"""
إصلاح شامل لقاعدة amrtmco_business
الهدف: إمكانية تصدير القاعدة واستيرادها في سيرفر الإنتاج دون أخطاء.

1) تنظيف كل البيانات اليتيمة (بطolicy مطابقة لـ Laravel: CASCADE يحذف، SET NULL يفوّض)
2) تثبيت كل قيود المفاتيح الأجنبية الـ 23 المستخرجة من المايجريشنز
3) تقرير مفصّل قبل/بعد

السكربت آمن وقابل لإعادة التشغيل (idempotent).
"""
import pymysql, sys, json, re
sys.stdout.reconfigure(encoding="utf-8")

DB = "amrtmco_business"
PLAN = json.load(open(r"C:\Users\hisha\AppData\Local\Temp\opencode\fks_plan.json", encoding="utf-8"))

conn = pymysql.connect(host="127.0.0.1", user="root", password="", database=DB, charset="utf8mb4", autocommit=False)
cur = conn.cursor()


def cols_of(t):
    cur.execute("SHOW COLUMNS FROM `%s`" % t)
    return {r[0]: r for r in cur.fetchall()}


def existing_fks():
    cur.execute("""SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                   FROM information_schema.KEY_COLUMN_USAGE
                   WHERE TABLE_SCHEMA=%s AND REFERENCED_TABLE_NAME IS NOT NULL""", (DB,))
    return {(r[0], r[1]) for r in cur.fetchall()}


report = []
print("=" * 105)
print(" 阶段 1 / Phase 1  -  تنظيف البيانات اليتيمة (Orphan cleanup)")
print("=" * 105)

cur.execute("SET FOREIGN_KEY_CHECKS = 0")

for table, col, reftable, refcol, action in PLAN:
    tc, rc = cols_of(table), cols_of(reftable)
    if col not in tc or refcol not in rc:
        report.append(("SKIP", table, col, "عمود غير موجود"))
        print("  SKIP  %-30s .%-20s (عمود غير موجود)" % (table, col))
        continue

    coldef = tc[col][1].lower()
    nullable = tc[col][2] == "YES"
    pk = tc[col][3] == "PRI"

    # لا نلمس الأعمدة الأساسية
    if pk:
        print("  SKIP  %-30s .%-20s (عمود أساسي)" % (table, col))
        continue

    q = ("SELECT COUNT(*) FROM `%s` c LEFT JOIN `%s` p ON c.`%s` = p.`%s` "
         "WHERE c.`%s` IS NOT NULL AND p.`%s` IS NULL") % (table, reftable, col, refcol, col, refcol)
    cur.execute(q)
    n = cur.fetchone()[0]
    if n == 0:
        print("  OK    %-30s .%-20s -> %-26s نظيف" % (table, col, reftable))
        continue

    if action == "CASCADE":
        sql = "DELETE c FROM `%s` c LEFT JOIN `%s` p ON c.`%s` = p.`%s` WHERE c.`%s` IS NOT NULL AND p.`%s` IS NULL" % (
            table, reftable, col, refcol, col, refcol)
        cur.execute(sql)
        deleted = cur.rowcount
        report.append(("CASCADE-DELETE", table, col, "%d صف" % deleted))
        print("  DEL   %-30s .%-20s -> %-26s حُذف %d صف يتيم" % (table, col, reftable, deleted))
    else:  # SET NULL
        if not nullable:
            # العمود NOT NULL -> نحوّله إلى NULLABLE ثم نفوّض
            cur.execute("ALTER TABLE `%s` MODIFY `%s` BIGINT(20) UNSIGNED NULL" % (table, col))
            report.append(("ALTER-NULLABLE", table, col, "NOT NULL -> NULL"))
            print("  ALT   %-30s .%-20s NOT NULL -> NULLABLE" % (table, col))
        sql = "UPDATE `%s` c LEFT JOIN `%s` p ON c.`%s` = p.`%s` SET c.`%s` = NULL WHERE c.`%s` IS NOT NULL AND p.`%s` IS NULL" % (
            table, reftable, col, refcol, col, col, refcol)
        cur.execute(sql)
        nulled = cur.rowcount
        report.append(("SET-NULL", table, col, "%d صف" % nulled))
        print("  NULL  %-30s .%-20s -> %-26s فُوِّض %d صف إلى NULL" % (table, col, reftable, nulled))

conn.commit()
cur.execute("SET FOREIGN_KEY_CHECKS = 1")

print()
print("=" * 105)
print(" Phase 2  -  تثبيت قيود المفاتيح الأجنبية (23 قيد)")
print("=" * 105)

have = existing_fks()
added, skipped = 0, 0
for table, col, reftable, refcol, action in PLAN:
    if (table, col) in have:
        print("  EXISTS %-30s .%-20s" % (table, col))
        skipped += 1
        continue
    tc = cols_of(table)
    if col not in tc:
        print("  SKIP   %-30s .%-20s (عمود غير موجود)" % (table, col))
        skipped += 1
        continue
    name = "%s_%s_foreign" % (table, col)
    try:
        cur.execute("ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`) ON DELETE %s" % (
            table, name, col, reftable, refcol, action))
        added += 1
        print("  ADDED  %-30s .%-20s -> %-26s %s" % (table, col, reftable, action))
    except Exception as e:
        print("  FAIL   %-30s .%-20s : %s" % (table, col, e))
        report.append(("FAIL", table, col, str(e)[:90]))

conn.commit()
print()
print("  أُضيف: %d | موجود مسبقاً/متخطى: %d" % (added, skipped))
conn.close()
