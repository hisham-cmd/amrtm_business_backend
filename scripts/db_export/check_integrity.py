# -*- coding: utf-8 -*-
"""تشخيص شامل لسلامة البيانات المرجعية في قاعدة amrtmco_business"""
import pymysql, sys, re
sys.stdout.reconfigure(encoding="utf-8")

DB = "amrtmco_business"
conn = pymysql.connect(host="127.0.0.1", user="root", password="", database=DB, charset="utf8mb4")
cur = conn.cursor()

cur.execute("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=%s AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME", (DB,))
tables = [r[0] for r in cur.fetchall()]
print("=" * 95)
print("عدد الجداول:", len(tables))

cur.execute("SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=%s AND REFERENCED_TABLE_NAME IS NOT NULL ORDER BY TABLE_NAME", (DB,))
declared = cur.fetchall()
print("القيود الأجنبية المعلنة:", len(declared))
for d in declared:
    print("   ", d)

ph = ",".join(["%s"] * len(tables))
cur.execute("SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME IN (" + ph + ") ORDER BY TABLE_NAME, ORDINAL_POSITION", [DB] + tables)
cols = {}
for t, c, dt in cur.fetchall():
    cols.setdefault(t, []).append((c, dt))

cur.execute("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=%s AND CONSTRAINT_NAME='PRIMARY'", (DB,))
pks = {}
for t, c in cur.fetchall():
    pks.setdefault(t, c)

print()
print("=" * 95)
print("فحص العلاقات (أعمدة int/_id تشير إلى جداول أخرى) - orphans > 0 = بيانات ييمة")
print("=" * 95)

problems = []
for t in tables:
    if t not in pks:
        continue
    for (c, dt) in cols.get(t, []):
        if dt not in ("bigint", "int", "smallint", "mediumint", "tinyint"):
            continue
        if c == pks[t]:
            continue
        m = re.match(r"^(?:(\w+?)_)?id$", c)
        if not m:
            continue
        prefix = m.group(1)
        if not prefix:
            continue
        parent = None
        for cd in (prefix, prefix + "s", "bs_" + prefix, "bs_" + prefix + "s"):
            if cd in tables and cd != t:
                parent = cd
                break
        if not parent or parent not in pks:
            continue
        q = ("SELECT COUNT(*) FROM `" + t + "` c "
             "LEFT JOIN `" + parent + "` p ON c.`" + c + "` = p.`" + pks[parent] + "` "
             "WHERE c.`" + c + "` IS NOT NULL AND p.`" + pks[parent] + "` IS NULL")
        try:
            cur.execute(q)
            n = cur.fetchone()[0]
        except Exception as e:
            print("  !! خطأ", t, c, parent, e)
            continue
        print("  %-4s %-32s .%-24s -> %-28s orphans=%d" % ("YES" if n else "-", t, c, parent + "." + pks[parent], n))
        if n:
            problems.append((t, c, parent, pks[parent], n))

print()
print("=" * 95)
print("عدد العلاقات التي فيها بيانات ييمة:", len(problems))
for p in problems:
    print("   ", p)
conn.close()
