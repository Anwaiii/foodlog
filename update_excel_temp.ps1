$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false

$dir = "C:\xampp\htdocs\foodlog\docs"
$origFile = Get-ChildItem -Path $dir -Filter "FoodLog_*.xlsx" | Select-Object -First 1
$origPath = $origFile.FullName
$origName = $origFile.Name
$tempPath = "$dir\temp.xlsx"

if (Test-Path $tempPath) { Remove-Item $tempPath -Force }
Rename-Item -Path $origPath -NewName "temp.xlsx" -Force

$wb = $excel.Workbooks.Open($tempPath)

function B64($b64str) {
    if ([string]::IsNullOrEmpty($b64str)) { return "" }
    return [System.Text.Encoding]::UTF8.GetString([Convert]::FromBase64String($b64str))
}

$sheetName1 = B64 "5qmf6IO95LiA6Kan"
$ws2 = $wb.Sheets.Item($sheetName1)

$ws2.Cells.Item(7, 3).Value2 = B64 "5ZCN5YmNKDUw5a2XKeODu+OCq+ODhuOCtOODqigyMDDlrZcp44O76Kqs5piOKDIwMOWtl3RleHRhcmVhKeODu+S9j+aJgSgxMDDlrZcp44O75YaZ55yf44KS5pu05paw"

$wb.Save()
$wb.Close($false)
$excel.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null

Rename-Item -Path $tempPath -NewName $origName -Force

Write-Output "Done"
