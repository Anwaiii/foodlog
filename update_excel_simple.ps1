$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false

function B64($b64str) {
    if ([string]::IsNullOrEmpty($b64str)) { return "" }
    return [System.Text.Encoding]::UTF8.GetString([Convert]::FromBase64String($b64str))
}

$path = B64 "QzpceGFtcHBcaHRkb2NzXGZvb2Rsb2dcZG9jc1xGb29kTG9nX+apn+iDveioreiIjOabuC54bHN4"
$wb = $excel.Workbooks.Open($path)

$sheetName1 = B64 "5qmf6IO95LiA6Kan"
$ws2 = $wb.Sheets.Item($sheetName1)

$ws2.Cells.Item(7, 3).Value2 = B64 "5ZCN5YmNKDUw5a2XKeODu+OCq+ODhuOCtOODqigyMDDlrZcp44O76Kqs5piOKDIwMOWtl3RleHRhcmVhKeODu+S9j+aJgSgxMDDlrZcp44O75YaZ55yf44KS5pu05paw"

$wb.Save()
$wb.Close($false)
$excel.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null
Write-Output "Excel updated safely via base64 encoding"
