$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false
$dir = "C:\xampp\htdocs\foodlog\docs"
$origFile = Get-ChildItem -Path $dir -Filter "FoodLog_*.xlsx" | Select-Object -First 1
$wb = $excel.Workbooks.Open($origFile.FullName)
$ws = $wb.Sheets.Item(1)

$res = ""
for ($r = 5; $r -le 10; $r++) {
    $c1 = $ws.Cells.Item($r, 1).Value2
    $c2 = $ws.Cells.Item($r, 2).Value2
    $c3 = $ws.Cells.Item($r, 3).Value2
    $res += "Row $r : [$c1] [$c2] [$c3]`n"
}
$wb.Close($false)
$excel.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null
Write-Output $res
