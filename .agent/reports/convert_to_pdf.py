import sys, os
project_root = r'C:\react_projects\amrtm_business'
sys.path.insert(0, os.path.join(project_root, '.agent', 'workflows'))
from auto_release_generator import convert_docx_to_pdf_win32, convert_docx_to_pdf

reports_dir = os.path.join(project_root, '.agent', 'reports')
docx_path = os.path.join(reports_dir, "إصدار رقم 1.0.0.docx")
pdf_path = os.path.join(reports_dir, "إصدار رقم 1.0.0.pdf")

print("[PDF] Converting to PDF...")
success = convert_docx_to_pdf_win32(docx_path, pdf_path)
if success:
    print(f"[PDF] PDF generated: {pdf_path}")
else:
    print("[PDF] Falling back to ReportLab...")
    convert_docx_to_pdf(docx_path, pdf_path)
    print(f"[PDF] PDF generated: {pdf_path}")
