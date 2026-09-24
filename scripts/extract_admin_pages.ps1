<#
.SYNOPSIS
    Extracts the monolithic admin_dashboard.blade.php into separate files.
#>
$ErrorActionPreference = 'Stop'
$src = "C:\react_projects\amrtm_business\resources\views\update_service\admin_dashboard.blade.php"
$lines = Get-Content $src -Encoding UTF8
$total = $lines.Count
Write-Host "Read $total lines from admin_dashboard.blade.php"

function Get-Range($from, $to) {
    $lines[($from - 1)..($to - 1)] -join "`n"
}

# 1. CSS partial (lines 31-3325)
$cssContent = Get-Range 31 3325
$cssPath = "C:\react_projects\amrtm_business\resources\views\update_service\dashboard\_admin_css.blade.php"
Set-Content -Path $cssPath -Value $cssContent -Encoding UTF8
Write-Host "Wrote _admin_css.blade.php (lines 31-3325)"

# 2. JS partial (lines 4870-10385)
$jsContent = Get-Range 4870 10385
$jsPath = "C:\react_projects\amrtm_business\resources\views\update_service\dashboard\admin\_admin_js.blade.php"
Set-Content -Path $jsPath -Value $jsContent -Encoding UTF8
Write-Host "Wrote _admin_js.blade.php (lines 4870-10385)"

# 3. Extract 15 pages
$pagesDir = "C:\react_projects\amrtm_business\resources\views\update_service\dashboard\admin\pages"
$pages = [ordered]@{
    'overview'           = @(3345, 3497)
    'requests'           = @(3499, 3517)
    'pricing'            = @(3519, 3528)
    'contracts'          = @(3530, 3797)
    'finance'            = @(3799, 3900)
    'off-finance'        = @(3902, 4005)
    'catalog'            = @(4007, 4333)
    'users'              = @(4335, 4401)
    'analytics'          = @(4403, 4460)
    'logs'               = @(4462, 4487)
    'offices'            = @(4489, 4565)
    'office-specialties' = @(4567, 4643)
    'services-approvals' = @(4645, 4662)
    'permissions'        = @(4664, 4680)
    'settings'           = @(4682, 4705)
}

foreach ($name in $pages.Keys) {
    $start = $pages[$name][0]
    $end   = $pages[$name][1]
    $content = Get-Range $start $end
    $pagePath = Join-Path $pagesDir "$name.blade.php"
    Set-Content -Path $pagePath -Value $content -Encoding UTF8
    Write-Host "  pages/$name.blade.php (lines $start-$end)"
}

# 4. Extract modals
$modals = Get-Range 4711 4868
$modalsPath = "C:\react_projects\amrtm_business\resources\views\update_service\dashboard\admin\_modals.blade.php"
Set-Content -Path $modalsPath -Value $modals -Encoding UTF8
Write-Host "  _modals.blade.php (lines 4711-4868)"

Write-Host "`n=== EXTRACTION COMPLETE ==="
