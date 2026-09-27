# -*- coding: utf-8 -*-
"""
أداة تصدير آمنة لقاعدة amrtmco_business

المشكلة التي تحلها:
  mysqldump / phpMyAdmin يرتّبان الجداول أبجدياً، فينشأ الجدول الابن قبل
  الأب -> errno 150 "Foreign key constraint is incorrectly formed"
 、早期 user's dump فشل أيضاً بـ #1452 بسبب بيانات يتيمة.

الحل الذي يطبّقه هذا السكربت:
  1. ترتيب طوبولوجي: كل أب قبل كل أبنه
  2. إزالة قيود FK من داخل CREATE TABLE وإعادة إضافتها في نهاية الملف
     (سلوك phpMyAdmin Quick SQL = آمن دائماً)
  3. رأس يحوي SET FOREIGN_KEY_CHECKS=0 و SET NAMES utf8mb4
  4. تنظيف أي بيانات يتيمة قبل التصدير

النتيجة: ملف يستورد بنجاح حتى لو لم يُفعّل المستخدم خيار
"Disable foreign key checks" في phpMyAdmin.
"""
import pymysql, sys, re, os, subprocess, argparse
sys.stdout.reconfigure(encoding="utf-8")

MYSQLDUMP = r"C:\xampp\mysql\bin\mysqldump.exe"
DB = "amrtmco_business"


def get_schema():
    conn = pymysql.connect(host="127.0.0.1", user="root", password="", database=DB, charset="utf8mb4")
    cur = conn.cursor()
    cur.execute("""SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                   FROM information_schema.KEY_COLUMN_USAGE
                   WHERE TABLE_SCHEMA=%s AND REFERENCED_TABLE_NAME IS NOT NULL""", (DB,))
    edges = cur.fetchall()
    cur.execute("SHOW TABLES")
    tables = [r[0] for r in cur.fetchall()]
    conn.close()
    return tables, edges


def topological_order(tables, edges):
    """يعيد ترتيباً بحيث يأتي كل أب قبل أبنه"""
    parents = {t: set() for t in tables}
    for child, col, ptable, pcol in edges:
        if ptable in parents and child in parents and ptable != child:
            parents[child].add(ptable)
    order, visited = [], set()

    def visit(t, stack):
        if t in visited:
            return
        if t in stack:              # دورة -> كسرها
            return
        stack.add(t)
        for p in sorted(parents[t]):
            visit(p, stack)
        stack.discard(t)
        visited.add(t)
        order.append(t)

    for t in sorted(tables):
        visit(t, set())
    # أي جدول لم يُدرَج (دورات) يُضاف في النهاية
    for t in sorted(tables):
        if t not in visited:
            order.append(t)
            visited.add(t)
    return order


def split_blocks(dump_text):
    """يفصل ملف mysqldump إلى كتل لكل جدول (بنية + بيانات)"""
    lines = dump_text.split("\n")
    starts = []   # (index, table, kind)
    for i, l in enumerate(lines):
        m = re.match(r"^--\s*Table structure for table `([^`]+)`", l)
        if m:
            starts.append((i, m.group(1), "structure"))
        else:
            m2 = re.match(r"^--\s*Dumping data for table `([^`]+)`", l)
            if m2:
                starts.append((i, m2.group(1), "data"))
    blocks = {}
    for idx, (i, tbl, kind) in enumerate(starts):
        end = starts[idx + 1][0] if idx + 1 < len(starts) else len(lines)
        blocks.setdefault(tbl, []).extend(lines[i:end])
    return blocks


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("-o", "--out", default=r"C:\Users\hisha\Downloads\amrtmco_business_safe.sql")
    ap.add_argument("--no-fk", action="store_true", help="بدون قيود FK")
    args = ap.parse_args()

    tables, edges = get_schema()
    order = topological_order(tables, edges)
    print("عدد الجداول: %d | القيود: %d" % (len(tables), len(edges)))
    print("الترتيب الطوبولوجي (أول 20):")
    for t in order[:20]:
        print("   ", t)

    # 1) تفريغ الجداول في ملف مؤقت
    flags = "--no-create-info" if False else ""
    raw = subprocess.run(
        [MYSQLDUMP, "-u", "root", "--default-character-set=utf8mb4",
         "--single-transaction", "--complete-insert", "--add-drop-table",
         "--skip-extended-insert" if False else "--extended", DB],
        capture_output=True, check=True
    ).stdout.decode("utf-8", errors="replace")

    # 2) فصل الكتل
    blocks = split_blocks(raw)

    # 3) إزالة قيود FK من داخل CREATE TABLE (سنعيدها في النهاية)
    def strip_fk(block_lines):
        out = []
        for l in block_lines:
            if "FOREIGN KEY" in l and l.strip().startswith("CONSTRAINT"):
                # نحذف السطر ونزيل الفاصلة السابقة إن لزم
                continue
            if out and out[-1].rstrip().endswith(",") and "CONSTRAINT" in l:
                out[-1] = out[-1].rstrip().rstrip(",")
            out.append(l)
        # تنظيف الفاصلة قبل القوس الأخير
        txt = "\n".join(out)
        txt = re.sub(r",\s*\)\s*ENGINE", ") ENGINE", txt)
        return txt

    header = """-- =====================================================================
--  تصدير آمن لقاعدة `%s`
--  تمProduced by: Python topologically-ordered exporter
--  الجداول مرتبة بحيث يُنشأ كل أب قبل أبنه
--  قيود المفاتيح الأجنبية مضافة في نهاية الملف (سلوك phpMyAdmin)
--  التاريخ: %s
-- =====================================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET NAMES utf8mb4 */;
SET @OLD_TIME_ZONE=@@TIME_ZONE;
SET TIME_ZONE='+00:00';
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;

""" % (DB, __import__("datetime").datetime.now().strftime("%Y-%m-%d %H:%M:%S"))

    parts = [header]
    # نُزيل أسطر /*!...*/; الخاصة بـ mysqldump (حفظ المتغيرات) لأنها قد تعلق
    # مع الجملة التالية فيُفسدها أي مُقسِّم جمل بسيط (ومنها بعض إصدارات phpMyAdmin)
    versioned = re.compile(r"^\s*/\*!.*\*/;\s*$")
    for t in order:
        b = blocks.get(t)
        if not b:
            continue
        clean = [l for l in b if not versioned.match(l)]
        parts.append(strip_fk(clean).rstrip() + "\n\n")

    # 4) قيود FK في النهاية
    if not args.no_fk:
        # لا نضع أي تعليق قبل أول ALTER: ذلك يضمن أن كل جملة ALTER تبدأ
        # بسطر فارغ بعد الفاصلة المنقوطة السابقة، فلا \"تلتصق\" بتعليق
        # فيُسقطها أي مُقسِّم جمل بسيط.
        fkpart = []
        bychild = {}
        for child, col, ptable, pcol in edges:
            bychild.setdefault(child, []).append((col, ptable, pcol))
        for child in order:
            if child not in bychild:
                continue
            cons = []
            for col, ptable, pcol in bychild[child]:
                # نحدد سلوك الحذف من القاعدة الفعلية
                behavior = "CASCADE"
                p = ("SELECT rc.DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS rc "
                     "WHERE rc.CONSTRAINT_SCHEMA=%s AND rc.TABLE_NAME=%s AND rc.CONSTRAINT_NAME=%s")
                try:
                    cn = pymysql.connect(host="127.0.0.1", user="root", password="", charset="utf8mb4")
                    cc = cn.cursor()
                    cc.execute(p, (DB, child, "%s_%s_foreign" % (child, col)))
                    r = cc.fetchone()
                    cn.close()
                    if r and r[0] in ("CASCADE", "SET NULL", "RESTRICT", "NO ACTION", "SET DEFAULT"):
                        behavior = r[0]
                except Exception:
                    pass
                cons.append("  ADD CONSTRAINT `%s_%s_foreign` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`) ON DELETE %s" % (
                    child, col, col, ptable, pcol, behavior))
            if cons:
                fkpart.append("ALTER TABLE `%s`\n  %s;\n\n" % (child, ",\n  ".join(cons)))
        parts.append("".join(fkpart))

    parts.append("""SET TIME_ZONE=@OLD_TIME_ZONE;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

-- Dump completed
""")

    out = "".join(parts)
    with open(args.out, "w", encoding="utf-8", newline="\n") as f:
        f.write(out)
    print()
    print("تم إنشاء الملف:", args.out)
    print("الحجم: %d بايت" % os.path.getsize(args.out))


if __name__ == "__main__":
    main()
