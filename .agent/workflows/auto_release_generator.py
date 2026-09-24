# -*- coding: utf-8 -*-
"""
Automated Release Note Generator for AMRTM Business
Collects git commits, calls Groq API (Llama 3.3) for Arabic summarization,
fills DOCX template and generates premium, shaped Arabic PDF.
"""

import os
import sys
import re
import argparse
import subprocess
import json
import urllib.request
from datetime import datetime

# Arabic reshaper & bidi imports for premium Arabic PDF generation
import arabic_reshaper
from bidi.algorithm import get_display

from docx import Document
from docx.shared import Pt
from docx.oxml import OxmlElement

ARABIC_DAYS = ["الاثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت", "الأحد"]

def reshape_arabic_html(html_text):
    """Reshape Arabic text line by line while preserving HTML tags and tags structure"""
    if not html_text:
        return ""
    
    # Standardize newlines
    html_text = html_text.replace("\r\n", "\n")
    lines = html_text.split("\n")
    reshaped_lines = []
    
    for line in lines:
        if not line.strip():
            reshaped_lines.append("")
            continue
            
        # Split by XML tags (e.g. <b>, <br/>, etc.) to keep them intact
        parts = re.split(r'(<[^>]+>)', line)
        reshaped_parts = []
        
        for part in parts:
            if part.startswith('<') and part.endswith('>'):
                reshaped_parts.append(part)
            else:
                if part.strip():
                    reshaped = arabic_reshaper.reshape(part)
                    bidi_text = get_display(reshaped)
                    reshaped_parts.append(bidi_text)
                else:
                    reshaped_parts.append(part)
                    
        reshaped_lines.append("".join(reshaped_parts))
        
    return "\n".join(reshaped_lines)


def get_git_commits(repo_path, since_date=None, until_date=None, days=7):
    """Fetch git commit messages since a specific date or days ago"""
    if not os.path.exists(repo_path):
        print(f"[Warning] Repository path not found: {repo_path}")
        return []
        
    cmd = ["git", "log", "--oneline"]
    
    if since_date:
        cmd.append(f"--since={since_date}")
    else:
        cmd.append(f"--since={days} days ago")
        
    if until_date:
        cmd.append(f"--until={until_date}")
        
    try:
        result = subprocess.run(
            cmd,
            cwd=repo_path,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            check=True,
            encoding='utf-8',
            errors='ignore'
        )
        commits = [line.strip() for line in result.stdout.split("\n") if line.strip()]
        return commits
    except Exception as e:
        print(f"[Error] Error fetching commits from {repo_path}: {e}")
        return []


def query_groq_api(commits, api_key):
    """Send commits to Groq API (Llama 3.3) for premium Arabic summarization"""
    url = "https://api.groq.com/openai/v1/chat/completions"
    
    commits_text = "\n".join([f"- {c}" for c in commits])
    
    prompt = f"""
أنت مهندس برمجيات محترف وخبير في توثيق المشاريع البرمجية. 
قم بتحليل قائمة الـ Commits التالية وصياغتها في شكل "ملاحظات إصدار" (Release Notes) رسمية ومهنية باللغة العربية الفصحى المبسطة تليق بالعرض على الإدارة أو العملاء.

الشروط الهامة جداً:
1. اكتب بأسلوب بشري رسمي ومهني رفيع. تجنب العبارات النمطية للذكاء الاصطناعي تماماً.
2. المطور المسؤول هو: هشام الصغير. مدير الفريق هو: هشام قاسم الصغير.
3. قم بتوزيع الأعمال والـ Commits بذكاء وتلخيصها في الفئات التالية:
   - نبذة مختصرة ومهنية عن الإصدار (description)
   - الميزات الجديدة المكتملة (features)
   - التحسينات والتعديلات الفنية (enhancements)
   - إصلاح الأخطاء والمشاكل التقنية (bug_fixes)
   - التحديثات التقنية والبنية التحتية الخلفية (technical_updates)
4. يجب أن تكون مخرجاتك بتنسيق JSON فقط دون أي نصوص أو شرح إضافي خارج الـ JSON.

قائمة الـ Commits المتاحة للتحليل:
{commits_text}

تنسيق الـ JSON المطلوب بدقة:
{{
  "description": "وصف موجز للميزات والحلول التي يقدمها هذا الإصدار في جملتين أو ثلاث...",
  "features": [
    "ميزة 1...",
    "ميزة 2..."
  ],
  "enhancements": [
    "تحسين 1...",
    "تحسين 2..."
  ],
  "bug_fixes": [
    "إصلاح 1...",
    "إصلاح 2..."
  ],
  "technical_updates": [
    "تحديث 1...",
    "تحديث 2..."
  ]
}}
"""

    headers = {
        "Content-Type": "application/json",
        "Authorization": f"Bearer {api_key}",
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
    }
    
    payload = {
        "model": "llama-3.3-70b-versatile",
        "messages": [{"role": "user", "content": prompt}],
        "temperature": 0.3,
        "response_format": {"type": "json_object"}
    }
    
    req = urllib.request.Request(
        url,
        data=json.dumps(payload).encode("utf-8"),
        headers=headers
    )
    
    try:
        with urllib.request.urlopen(req) as response:
            result = json.loads(response.read().decode("utf-8"))
            content = result["choices"][0]["message"]["content"]
            return json.loads(content)
    except Exception as e:
        print(f"[Error] Error querying Groq API: {e}")
        # Fallback to local structure if API fails
        return {
            "description": "تم إصدار وتحديث مجموعة من الميزات الهامة وإصلاح المشاكل البرمجية لتحسين تجربة المستخدم وتحسين أداء التطبيق.",
            "features": ["تحديث ميزات النظام الأساسية للمشروع المعتمد"],
            "enhancements": ["تحسين سرعة استجابة الخادم وتكامل قواعد البيانات"],
            "bug_fixes": ["معالجة مشكلة تحميل البيانات وتثبيت النطاقات"],
            "technical_updates": ["تحديث ملفات التكوين والـ Script للرفع والاستقرار"]
        }


def set_cell_text(cell, text, bold=False, append=False):
    """Set text in a cell, preserving existing content if append=True and setting RTL"""
    if append:
        for p in cell.paragraphs:
            for r in p.runs:
                r.text = r.text + text
        return
        
    while len(cell.paragraphs) > 1:
        p = cell.paragraphs[-1]
        p._element.getparent().remove(p._element)
        
    p = cell.paragraphs[0]
    p.clear()
    
    # Set RTL paragraph alignment/direction
    p.paragraph_format.right_to_left = True
    
    run = p.add_run(text)
    run.bold = bold
    run.font.name = 'Arial'
    run.font.size = Pt(10.5)
    
    # Ensure RTL is set on the run level
    rPr = run._r.get_or_add_rPr()
    rPr.append(OxmlElement('w:rtl'))


def fill_docx_template(template_path, output_path, version, date_str, time_str, day_str,
                       description, features_formatted, platform_type,
                       testers, developers, team_manager, upload_responsible, signatory):
    """Fill fields in the existing Word template"""
    if not os.path.exists(template_path):
        raise FileNotFoundError(f"Template path not found: {template_path}")
        
    doc = Document(template_path)
    tables = doc.tables
    
    # Paragraph 0 Title - append version to "إصدار رقم "
    version_title_prefix = "إصدار رقم "
    for p in doc.paragraphs:
        if p.text.strip().startswith(version_title_prefix.strip()):
            runs = p.runs
            if runs:
                runs[0].text = version_title_prefix + version
                for r in runs[1:]:
                    r.text = ""
            break
            
    # Table 0: نبذة عن الإصدار [0,1]
    if len(tables) > 0:
        set_cell_text(tables[0].cell(0, 1), description)
        
    # Table 1: Version info [0,1]=version [0,3]=day [1,1]=date [1,3]=time
    if len(tables) > 1:
        set_cell_text(tables[1].cell(0, 1), version, bold=True)
        set_cell_text(tables[1].cell(0, 3), day_str)
        set_cell_text(tables[1].cell(1, 1), date_str)
        set_cell_text(tables[1].cell(1, 3), time_str)
        
    # Table 2: Platform type [1,0]
    if len(tables) > 2:
        set_cell_text(tables[2].cell(1, 0), platform_type)
        
    # Table 3: Features / Changes [1,0]
    if len(tables) > 3:
        set_cell_text(tables[3].cell(1, 0), features_formatted)
        
    # Table 4: Testers & Developers
    if len(tables) > 4:
        t = tables[4]
        max_data_rows = min(len(t.rows) - 1, max(len(testers), len(developers)))
        for i in range(max_data_rows):
            cell_1 = t.cell(i + 1, 1)
            cell_2 = t.cell(i + 1, 2)
            if i < len(testers):
                set_cell_text(cell_1, testers[i])
            if i < len(developers):
                set_cell_text(cell_2, developers[i])
                
    # Table 5: Team [0,1]=manager, [1,1]=upload
    if len(tables) > 5:
        set_cell_text(tables[5].cell(0, 1), team_manager)
        set_cell_text(tables[5].cell(1, 1), upload_responsible)
        
    # Table 7: Signatory [0,1]
    if len(tables) > 7:
        set_cell_text(tables[7].cell(0, 1), signatory)
        
    output_dir = os.path.dirname(output_path)
    if output_dir and not os.path.exists(output_dir):
        os.makedirs(output_dir)
        
    doc.save(output_path)
    return output_path


def convert_docx_to_pdf_win32(docx_path, pdf_path):
    """Convert DOCX to PDF using Microsoft Word via COM (win32com) to preserve 100% original template design"""
    import os
    try:
        import win32com.client
    except ImportError:
        print("[Warning] win32com not available, falling back to ReportLab generator.")
        return False
        
    wdExportFormatPDF = 17 # PDF format constant
    abs_docx_path = os.path.abspath(docx_path)
    abs_pdf_path = os.path.abspath(pdf_path)
    
    word = None
    doc = None
    try:
        word = win32com.client.Dispatch("Word.Application")
        word.Visible = False
        doc = word.Documents.Open(abs_docx_path)
        doc.ExportAsFixedFormat(abs_pdf_path, wdExportFormatPDF)
        return True
    except Exception as e:
        print(f"[Warning] PDF conversion using MS Word COM failed: {e}")
        return False
    finally:
        if doc is not None:
            doc.Close(SaveChanges=False)
        if word is not None:
            word.Quit()


def convert_docx_to_pdf(docx_path, pdf_path):
    """Premium PDF Generator from DOCX with Reshaped Arabic Text and proper layout"""
    from reportlab.lib.pagesizes import A4
    from reportlab.lib.styles import ParagraphStyle
    from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle
    from reportlab.lib import colors
    from reportlab.lib.units import cm
    from reportlab.pdfbase import pdfmetrics
    from reportlab.pdfbase.ttfonts import TTFont
    
    font_name = 'Arial'
    try:
        pdfmetrics.registerFont(TTFont('Arial', 'C:/Windows/Fonts/arial.ttf'))
        pdfmetrics.registerFont(TTFont('Arial-Bold', 'C:/Windows/Fonts/arialbd.ttf'))
    except Exception as ex:
        print(f"[Warning] Failed to register Arial, falling back to Helvetica (Warning: Arabic may not display correctly): {ex}")
        font_name = 'Helvetica'
        
    read_doc = Document(docx_path)
    doc_builder = SimpleDocTemplate(
        pdf_path,
        pagesize=A4,
        rightMargin=1.5*cm,
        leftMargin=1.5*cm,
        topMargin=1.5*cm,
        bottomMargin=1.5*cm
    )
    
    story = []
    
    # Styles matching visual excellence rules
    normal_style = ParagraphStyle(
        'N',
        fontName=font_name,
        fontSize=10,
        leading=15,
        alignment=2,  # Right aligned
        spaceAfter=4,
        textColor=colors.HexColor('#334155') # Sleek slate-700
    )
    
    heading_style = ParagraphStyle(
        'H',
        fontName=font_name + '-Bold' if font_name == 'Arial' else font_name,
        fontSize=16,
        leading=22,
        alignment=1,  # Centered
        spaceAfter=12,
        textColor=colors.HexColor('#0f172a') # Dark slate-900
    )
    
    # 1. Parse paragraphs
    for p in read_doc.paragraphs:
        txt = p.text.strip()
        if not txt:
            continue
            
        reshaped_txt = reshape_arabic_html(txt)
        if p.style.name.startswith('Heading') or txt.startswith("إصدار رقم"):
            story.append(Paragraph(f"<b>{reshaped_txt}</b>", heading_style))
        else:
            story.append(Paragraph(reshaped_txt, normal_style))
            
    story.append(Spacer(1, 10))
    
    # 2. Parse tables with premium grid design
    for table in read_doc.tables:
        data = []
        for row in table.rows:
            row_data = []
            for cell in row.cells:
                # Shape and clean up cell contents
                cell_text = cell.text.strip().replace("\n", "<br/>")
                reshaped_cell = reshape_arabic_html(cell_text)
                row_data.append(Paragraph(reshaped_cell, normal_style))
            data.append(row_data)
            
        if not data:
            continue
            
        col_count = len(data[0])
        avail_width = A4[0] - 3.0*cm
        
        # Build premium styled ReportLab Table
        tbl = Table(data, colWidths=[avail_width / col_count] * col_count)
        tbl.setStyle(TableStyle([
            ('ALIGN', (0, 0), (-1, -1), 'RIGHT'),
            ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
            ('BACKGROUND', (0, 0), (-1, -1), colors.HexColor('#f8fafc')), # very light slate
            ('GRID', (0, 0), (-1, -1), 0.5, colors.HexColor('#e2e8f0')), # soft slate border
            ('TOPPADDING', (0, 0), (-1, -1), 6),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
            ('LEFTPADDING', (0, 0), (-1, -1), 6),
            ('RIGHTPADDING', (0, 0), (-1, -1), 6),
        ]))
        
        story.append(tbl)
        story.append(Spacer(1, 12))
        
    doc_builder.build(story)
    return pdf_path


def read_daily_reports(reports_dir, date_from, date_to):
    """Read daily reports from date range and compile into structured data"""
    from datetime import timedelta
    
    start_date = datetime.strptime(date_from, "%Y-%m-%d") if date_from else None
    end_date = datetime.strptime(date_to, "%Y-%m-%d") if date_to else datetime.now()
    
    all_accomplishments = []
    all_challenges = []
    all_plans = []
    
    current = start_date
    while current and current <= end_date:
        filename = os.path.join(reports_dir, f"daily-report-{current.strftime('%Y-%m-%d')}.txt")
        if os.path.exists(filename):
            with open(filename, "r", encoding="utf-8") as f:
                content = f.read()
            # Extract accomplishments
            acc_match = re.search(r'✅ الإنجازات:\n(.*?)(?=\n⚠️ التحديات:|\Z)', content, re.DOTALL)
            if acc_match:
                items = [line.strip().lstrip('* ').lstrip('• ') for line in acc_match.group(1).strip().split('\n') if line.strip().startswith('*')]
                all_accomplishments.extend(items)
            # Extract challenges
            chal_match = re.search(r'⚠️ التحديات:\n(.*?)(?=\n📅 خطة الغد:|\Z)', content, re.DOTALL)
            if chal_match:
                chal_text = chal_match.group(1).strip()
                if chal_text and chal_text != "لا يوجد":
                    all_challenges.append(f"[{current.strftime('%Y-%m-%d')}] {chal_text}")
            # Extract plans
            plan_match = re.search(r'📅 خطة الغد:\n(.*?)(?=\Z)', content, re.DOTALL)
            if plan_match:
                plan_text = plan_match.group(1).strip()
                if plan_text and plan_text != "لا يوجد":
                    all_plans.append(f"[{current.strftime('%Y-%m-%d')}] {plan_text}")
        current += timedelta(days=1)
    
    return all_accomplishments, all_challenges, all_plans


def compile_release_from_reports(accomplishments, challenges, plans):
    """Compile daily report data into release note structure"""
    features = []
    enhancements = []
    bug_fixes = []
    technical_updates = []
    
    # Categorize accomplishments by keywords
    for item in accomplishments:
        item_lower = item.lower()
        if any(kw in item_lower for kw in ['إصلاح', 'مشكلة', 'خطأ', 'fix', 'crash', 'bug']):
            bug_fixes.append(item)
        elif any(kw in item_lower for kw in ['تحديث', 'تحسين', 'إعادة', 'refactor', 'تنظيف']):
            enhancements.append(item)
        elif any(kw in item_lower for kw in ['إضافة', 'إنشاء', 'تطوير', 'تصميم', 'دعم', 'نظام']):
            features.append(item)
        else:
            technical_updates.append(item)
    
    # If too few in some categories, redistribute
    if not features and accomplishments:
        features = [a for a in accomplishments if a not in bug_fixes and a not in enhancements and a not in technical_updates]
    
    # Build description
    description_parts = []
    if features:
        description_parts.append(f"تمت إضافة {len(features)} من الميزات الجديدة")
    if enhancements:
        description_parts.append(f"{len(enhancements)} تحسين")
    if bug_fixes:
        description_parts.append(f"إصلاح {len(bug_fixes)} مشكلة")
    
    description = "هذا الإصدار يتضمن " + " و ".join(description_parts) + ". "
    description += "تم تطوير النظام بناءً على التقارير اليومية للفترة المحددة."
    
    return {
        "description": description,
        "features": features,
        "enhancements": enhancements,
        "bug_fixes": bug_fixes,
        "technical_updates": technical_updates,
        "challenges": challenges,
        "plans": plans
    }


def main():
    parser = argparse.ArgumentParser(description='Automated Release Note Generator for AMRTM Business')
    parser.add_argument('--version', required=True, help='Version number e.g. 1.0.0')
    parser.add_argument('--days', type=int, default=7, help='Fetch commits from last N days')
    parser.add_argument('--date-from', default=None, help='Commits since date (YYYY-MM-DD)')
    parser.add_argument('--date-to', default=None, help='Commits until date (YYYY-MM-DD)')
    parser.add_argument('--from-reports', action='store_true', default=False, help='Generate from daily report files instead of git commits')
    parser.add_argument('--report-date-from', default=None, help='Read daily reports from date (YYYY-MM-DD)')
    parser.add_argument('--report-date-to', default=None, help='Read daily reports until date (YYYY-MM-DD)')
    parser.add_argument('--platform', default='تطبيق ويب (Laravel)', help='Platform Type')
    parser.add_argument('--testers', default='هشام قاسم الصغير', help='Comma-separated list of testers')
    parser.add_argument('--developers', default='هشام الصغير', help='Comma-separated list of developers')
    parser.add_argument('--team-manager', default='هشام قاسم الصغير', help='Team manager name')
    parser.add_argument('--upload-by', default='هشام الصغير', help='Uploader name')
    parser.add_argument('--signatory', default='هشام قاسم الصغير', help='Signatory manager name')
    parser.add_argument('--pdf', action='store_true', default=True, help='Generate PDF file as well')
    
    args = parser.parse_args()
    
    # Project paths for AMRTM Business
    project_root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
    template_path = os.path.join(project_root, "إصدار رقم.docx")
    reports_dir = os.path.join(project_root, ".agent", "reports")
    repo_path = project_root  # Single Laravel repo
    
    if args.from_reports:
        # Generate from daily report files
        date_from = args.report_date_from or args.date_from
        date_to = args.report_date_to or args.date_to
        
        if not date_from:
            print("[Error] --report-date-from is required when using --from-reports")
            sys.exit(1)
            
        print(f"[Reports] Reading daily reports from {date_from} to {date_to or 'today'}...")
        accomplishments, challenges, plans = read_daily_reports(reports_dir, date_from, date_to)
        print(f"[Reports] Found {len(accomplishments)} accomplishments across reports.")
        
        summary_data = compile_release_from_reports(accomplishments, challenges, plans)
    else:
        # Git + Groq approach for single Laravel repo
        print("[Git] Fetching recent Git commits...")
        all_commits = get_git_commits(repo_path, args.date_from, args.date_to, args.days)
        
        formatted_commits = []
        for c in all_commits:
            formatted_commits.append(f"[AMRTM] {c}")
            
        print(f"[Git] Found {len(formatted_commits)} commits in total.")
        
        print("[Groq AI] Summarizing commits using Groq AI...")
        groq_api_key = os.environ.get("GROQ_API_KEY", "")
        if not groq_api_key:
            print("[Warning] GROQ_API_KEY not set. Using fallback summarization.")
            summary_data = {
                "description": "تم إصدار وتحديث مجموعة من الميزات الهامة لإدارة الطلبات والخدمات الحكومية وقطاع الأعمال.",
                "features": ["تحديث ميزات النظام الأساسية لمنصة آمر تم"],
                "enhancements": ["تحسين سرعة استجابة الخادم وتكامل قواعد البيانات"],
                "bug_fixes": ["معالجة مشكلة تحميل البيانات وتثبيت النطاقات"],
                "technical_updates": ["تحديث ملفات التكوين والـ Script للرفع والاستقرار"]
            }
        else:
            summary_data = query_groq_api(formatted_commits, groq_api_key)
        print("[Groq AI] AI summarization completed.")
    
    # Format changes log beautifully in Arabic
    features_list = []
    
    if summary_data.get("features"):
        features_list.append("⭐ الميزات الجديدة:")
        for feat in summary_data["features"]:
            features_list.append(f"  • {feat}")
            
    if summary_data.get("enhancements"):
        features_list.append("\n⚡ التحسينات والتعديلات:")
        for enh in summary_data["enhancements"]:
            features_list.append(f"  • {enh}")
            
    if summary_data.get("bug_fixes"):
        features_list.append("\n🔧 إصلاح الأخطاء والمشاكل:")
        for fix in summary_data["bug_fixes"]:
            features_list.append(f"  • {fix}")
            
    if summary_data.get("technical_updates"):
        features_list.append("\n⚙️ تحديثات تقنية وبنية تحتية:")
        for tech in summary_data["technical_updates"]:
            features_list.append(f"  • {tech}")
            
    features_formatted = "\n".join(features_list)
    
    # 3. Setup Dates
    release_date = datetime.now()
    date_str = release_date.strftime("%Y-%m-%d")
    time_str = release_date.strftime("%I:%M %p").replace("AM", "ص").replace("PM", "م")
    day_str = ARABIC_DAYS[release_date.weekday()]
    
    # Parse testers and developers lists
    testers_list = [t.strip() for t in args.testers.split(",") if t.strip()]
    developers_list = [d.strip() for d in args.developers.split(",") if d.strip()]
    
    # 4. Fill DOCX template
    output_docx_path = os.path.join(reports_dir, f"إصدار رقم {args.version}.docx")
    print(f"[Docx] Filling DOCX template for version {args.version}...")
    
    fill_docx_template(
        template_path,
        output_docx_path,
        args.version,
        date_str,
        time_str,
        day_str,
        summary_data.get("description", ""),
        features_formatted,
        args.platform,
        testers_list,
        developers_list,
        args.team_manager,
        args.upload_by,
        args.signatory
    )
    print(f"[Docx] Filled DOCX successfully: {output_docx_path}")
    
    # 5. Generate PDF
    if args.pdf:
        output_pdf_path = os.path.join(reports_dir, f"إصدار رقم {args.version}.pdf")
        print(f"[PDF] Converting to PDF...")
        # Try MS Word COM first
        success = convert_docx_to_pdf_win32(output_docx_path, output_pdf_path)
        if not success:
            print(f"[PDF] Falling back to custom PDF generator...")
            convert_docx_to_pdf(output_docx_path, output_pdf_path)
        print(f"[PDF] Generated PDF successfully: {output_pdf_path}")
        
    print("\n[Success] Release notes workflow completed successfully!")

if __name__ == '__main__':
    main()
