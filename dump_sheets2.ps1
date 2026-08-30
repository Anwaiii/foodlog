$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false
$dir = "C:\xampp\htdocs\foodlog\docs"
$origFile = Get-ChildItem -Path $dir -Filter "FoodLog_*.xlsx" | Select-Object -First 1
$wb = $excel.Workbooks.Open($origFile.FullName)

$res = ""
for ($i = 1; $i -le $wb.Sheets.Count; $i++) {
    $name = $wb.Sheets.Item($i).Name
    $res += "Sheet ${i}: ${name}`n"
}
$wb.Close($false)
$excel.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null
Write-Output $res
