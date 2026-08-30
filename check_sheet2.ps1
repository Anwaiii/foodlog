$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false
$dir = "C:\xampp\htdocs\foodlog\docs"
$origFile = Get-ChildItem -Path $dir -Filter "FoodLog_*.xlsx" | Select-Object -First 1
$wb = $excel.Workbooks.Open($origFile.FullName)
$ws = $wb.Sheets.Item(2)

$c1 = $ws.Cells.Item(7, 1).Value2
$c2 = $ws.Cells.Item(7, 2).Value2
$c3 = $ws.Cells.Item(7, 3).Value2
$wb.Close($false)
$excel.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null
Write-Output "[$c1] [$c2] [$c3]"
