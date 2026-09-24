@echo off
chcp 65001 > nul
setlocal enabledelayedexpansion

echo =============================================================
echo   AMRTM Business - Owner Report Generator (DOCX)
echo   منصة آمر تم - تقرير مالك الشركة (غير تقني)
echo =============================================================
echo.

set /p DATE_FROM="[Input] From date (YYYY-MM-DD, e.g. 2026-09-13): "
if "!DATE_FROM!"=="" (
    echo.
    echo [Error] From date is required.
    echo.
    pause
    exit /b 1
)

set /p DATE_TO="[Input] To date (YYYY-MM-DD, default today): "
if "!DATE_TO!"=="" set DATE_TO=

echo.
echo [Running] Collecting Git commits and building DOCX + PDF report...
echo -------------------------------------------------------------
python "%~dp0owner_report_generator.py" --from "!DATE_FROM!" --to "!DATE_TO!"
echo -------------------------------------------------------------
echo.
echo [Done] Reports saved in:
echo   .agent\reports\owner-report-!DATE_TO!.docx
echo   .agent\reports\owner-report-!DATE_TO!.pdf
echo.
pause
