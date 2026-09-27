# -*- coding: utf-8 -*-
"""استخراج كل قيود المفاتيح الأجنبية定义的 من مايجريشنز Laravel تلقائياً"""
import re, os, sys, glob
sys.stdout.reconfigure(encoding="utf-8")

ROOT = r"C:\react_projects\amrtm_business\amrtm_business_backend\database\migrations"
files = sorted(glob.glob(os.path.join(ROOT, "*.php")))

# $table->foreign('X')->references('id')->on('TABLE')->METHOD();
pat = re.compile(
    r"\$table\s*->\s*foreign\s*\(\s*['\"]([^'\"]+)['\"]\s*\)"
    r"\s*->\s*references\s*\(\s*['\"]([^'\"]+)['\"]\s*\)"
    r"\s*->\s*on\s*\(\s*['\"]([^'\"]+)['\"]\s*\)"
    r"\s*(?:->\s*([a-zA-Z]+)\s*\(\s*\))?\s*;",
    re.S,
)
#，还需要 table 名: 最近的 $schema->create('X', ...) / ->table('X', ...)
ctx_pat = re.compile(r"(?:->\s*create|->\s*table)\s*\(\s*['\"]([^'\"]+)['\"]")

found = []
for f in files:
    src = open(f, encoding="utf-8", errors="replace").read()
    for m in pat.finditer(src):
        col, refcol, reftbl, method = m.group(1), m.group(2), m.group(3), m.group(4)
        # ابحث عن اسم الجدول قبل هذا الموضع
        prefix = src[: m.start()]
        ctxs = ctx_pat.findall(prefix)
        table = ctxs[-1] if ctxs else "?"
        found.append((table, col, reftbl, refcol, method, os.path.basename(f)))

print("=" * 110)
print("قيود مستخرجة من المايجريشنز:", len(found))
print("=" * 110)
seen = set()
uniq = []
for t, c, rt, rc, meth, fn in found:
    key = (t, c, rt, rc)
    if key in seen:
        continue
    seen.add(key)
    uniq.append((t, c, rt, rc, meth, fn))

for t, c, rt, rc, meth, fn in uniq:
    action = {"cascadeOnDelete": "CASCADE", "nullOnDelete": "SET NULL", "restrictOnDelete": "RESTRICT",
              None: "(بدون)"}.get(meth, meth)
    print("  %-30s %-22s -> %-28s %-14s %s" % (t, c, rt, action, fn))

# احفظ كـ SQL plan
out = []
for t, c, rt, rc, meth, fn in uniq:
    action = {"cascadeOnDelete": "CASCADE", "nullOnDelete": "SET NULL", "restrictOnDelete": "RESTRICT"}.get(meth, "RESTRICT")
    out.append((t, c, rt, rc, action))
import json
with open(r"C:\Users\hisha\AppData\Local\Temp\opencode\fks_plan.json", "w", encoding="utf-8") as fh:
    json.dump(out, fh, ensure_ascii=False, indent=1)
print()
print("حُفظ الخطة في fks_plan.json  (", len(out), "قيد )")
