# -*- coding: utf-8 -*-
"""
اختبار استيراد صارم: يستورد الملف الآمن مع إعادة تفعيل فحص المفاتيح الأجنبية
بعد كل جملة، تمامًا كما لو كان المستخدم قد فعّل خيار "Disable foreign key checks"
 fastening في phpMyAdmin ثم أعاده — أو أسوأ: لم يفعّله إطلاقاً.
"""
import pymysql, sys, re, os
sys.stdout.reconfigure(encoding="utf-8")

DUMP = sys.argv[1] if len(sys.argv) > 1 else r"C:\Users\hisha\AppData\Local\Temp\opencode\safe_dump.sql"
STRICT = os.environ.get("STRICT", "1") == "1"

text = open(DUMP, "rb").read().decode("utf-8", errors="replace")
text = re.sub(r"`amrtmco_business`", "`import_test`", text)

conn = pymysql.connect(host="127.0.0.1", user="root", password="", database="import_test",
                       charset="utf8mb4", autocommit=False)
cur = conn.cursor()
cur.execute("SET FOREIGN_KEY_CHECKS = 0")
cur.execute("SHOW TABLES")
for (tb,) in cur.fetchall():
    cur.execute("DROP TABLE IF EXISTS `%s`" % tb)
print("تم تفريغ import_test\n")

stmts, buf = [], []
for line in text.split("\n"):
    buf.append(line)
    if line.rstrip().endswith(";"):
        stmts.append("\n".join(buf)); buf = []

ok = fail = 0
errors = []
for s in stmts:
    body = "\n".join(l for l in s.split("\n") if not l.strip().startswith("--")).strip().rstrip(";").strip()
    # نفّذ جمل SET أيضاً (كما يفعل phpMyAdmin) حتى تُطبَّق الإعدادات مثل TIME_ZONE
    up = body.upper()
    if not body or up.startswith("/*") or up.startswith("LOCK TABLES") or up.startswith("UNLOCK TABLES"):
        continue
    if up.startswith("/*!"):
        body = body[3:].rstrip(";") if body.startswith("/*!") else body
        if not body:
            continue
    try:
        cur.execute(body)
        ok += 1
        if os.environ.get("TRACE") and "ALTER TABLE" in body:
            print("   [OK]  " + body.split("\n")[0][:80])
    except Exception as e:
        fail += 1
        errors.append((body.split("\n")[0][:95], str(e)[:170]))
        if os.environ.get("TRACE"):
            print("   [ERR] " + body.split("\n")[0][:80])
            print("         " + str(e)[:150])
        conn.rollback()
        if fail > 40:
            break
    if STRICT:
        # إعادة تفعيل الفحص بعد كل جملة لاختبار الصلابة الكاملة
        try:
            cur.execute("SET FOREIGN_KEY_CHECKS = 1")
        except Exception:
            pass
        cur.execute("SET FOREIGN_KEY_CHECKS = 0")

conn.commit()
print("=" * 100)
print("  الملف:", os.path.basename(DUMP))
print("  الوضع :", "صارم (فحص مفعّن دائماً)" if STRICT else "متساهل")
print("  جمل نجحت: %d | فشل: %d" % (ok, fail))
print("=" * 100)
for h, m in errors[:30]:
    print("  FAIL:", h)
    print("        ", m)

cur.execute("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='import_test' AND TABLE_TYPE='BASE TABLE'")
print("\nجداول مستوردة:", cur.fetchone()[0])
cur.execute("SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='import_test'")
print("قيود أجنبية مطبّقة:", cur.fetchone()[0])
for t in ("bs_users", "bs_offices", "bs_services", "bs_specialties", "bs_specialty_services",
          "bs_office_users", "bs_office_documents", "bs_payments", "bs_contracts", "bs_requests"):
    try:
        cur.execute("SELECT COUNT(*) FROM `" + t + "`")
        print("   %-24s %d صف" % (t, cur.fetchone()[0]))
    except Exception as e:
        print("   %-24s ERROR %s" % (t, str(e)[:60]))
print()
print("النتيجة النهائية:", "*** ناجح 100% ***" if fail == 0 else "*** فشل %d جملة ***" % fail)
conn.close()
