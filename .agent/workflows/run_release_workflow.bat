@echo off
chcp 65001 > nul
setlocal enabledelayedexpansion

echo =============================================================
echo   AMRTM Business - Release Notes Generator
echo   منصة آمر تم - قطاع الأعمال
echo =============================================================
echo.

set /p VERSION="[Input] Version number (e.g. 1.0.0): "
if "%VERSION%"=="" (
    echo.
    echo [Error] Version number is required.
    echo.
    pause
    exit /b 1
)

echo.
echo Choose source:
echo [1] Git commits + AI (default)
echo [2] Daily report files
echo.
set /p SOURCE="[Input] Source (1 or 2): "
if "%SOURCE%"=="" set SOURCE=1

if "%SOURCE%"=="2" (
    set /p DATE_FROM="[Input] From date (YYYY-MM-DD, e.g. 2026-09-01): "
    if "!DATE_FROM!"=="" (
        echo.
        echo [Error] From date is required when using reports.
        echo.
        pause
        exit /b 1
    )
    set /p DATE_TO="[Input] To date (YYYY-MM-DD, default today): "
    if "!DATE_TO!"=="" set DATE_TO=
    
    echo.
    echo [Running] Compiling release from daily reports...
    echo -------------------------------------------------------------
    python "%~dp0auto_release_generator.py" --version "%VERSION%" --from-reports --report-date-from "!DATE_FROM!" --report-date-to "!DATE_TO!" --pdf
) else (
    set /p DAYS="[Input] Days to look back for commits (default 7): "
    if "%DAYS%"=="" set DAYS=7
    
    echo.
    echo [Running] Collecting Git commits and calling AI API...
    echo -------------------------------------------------------------
    python "%~dp0auto_release_generator.py" --version "%VERSION%" --days %DAYS% --pdf
)
echo -------------------------------------------------------------
echo.
echo [Done] Files generated in reports directory:
echo   .agent\reports\
echo.
pause
