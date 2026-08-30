$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false
$path = "C:\xampp\htdocs\foodlog\docs\FoodLog_機能設計書.xlsx"
$wb = $excel.Workbooks.Open($path)
$ws2 = $wb.Sheets("機能一覧")
$text = $ws2.Cells(7,3).Value2
$wb.Close($false)
$excel.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null
Write-Output "Text: $text"