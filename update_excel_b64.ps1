$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false
$path = "C:\xampp\htdocs\foodlog\docs\FoodLog_?????.xlsx"
$wb = $excel.Workbooks.Open($path)

function B64($b64str) {
    if ([string]::IsNullOrEmpty($b64str)) { return "" }
    return [System.Text.Encoding]::UTF8.GetString([Convert]::FromBase64String($b64str))
}

$sheetName1 = B64 "5qmf6IO95LiA6Kan"
$ws2 = $wb.Sheets.Item($sheetName1)

function S($ws,$r,$c,$b64,$bold=$false,$bg=$null,$fg=$null,$sz=$null) {
    $v = B64 $b64
    $cell = $ws.Cells.Item($r,$c)
    $cell.Value2 = $v
    if ($bold) { $cell.Font.Bold = $true }
    if ($sz) { $cell.Font.Size = $sz }
    if ($bg) { $cell.Interior.Color = $bg }
    if ($fg) { $cell.Font.Color = $fg }
}

# 6 3: ??(50?)?????(200?)???(200?textarea)???(100?)???
S $ws2 6 3 "5ZCN5YmNKDUw5a2XKeODu+OCq+ODhuOCtOODqigyMDDlrZcp44O76Kqs5piOKDIwMOWtl3RleHRhcmVhKeODu+S9j+aJgSgxMDDlrZcp44O75YaZ55yf" $false
# 7 3: ??(50?)?????(200?)???(200?textarea)???(100?)??????
S $ws2 7 3 "5ZCN5YmNKDUw5a2XKeODu+OCq+ODhuOCtOODqigyMDDlrZcp44O76Kqs5piOKDIwMOWtl3RleHRhcmVhKeODu+S9j+aJgSgxMDDlrZcp44O75YaZ55yf44KS5pu05paw" $false
# 11 3: ??(?????)?????(200?)???(500?)???(??)???(1?)
S $ws2 11 3 "5pel5LuYKOacqueUseaXpeS4jeWPrinjg7vms6jmlofGheWuuigyMDDlrZcp44O75oSf5oOzKDUwMOWtlynjg7voqZTkuqEo5b+F6aCIKeODu+WGmOecnygx5p6aKQ==" $false
# 13 3: ??????????????????????????
S $ws2 13 3 "57eo6ZuG44Oi44O844OA44Or44Gn5pu05paw77yI55S75YOP5aSJ5pu05a++5b+c44O75ZCM44GY5paH5a2X5Yi26ZmQ6YGp55So77yJ" $false

$sheetName2 = B64 "RELog6KiI"
$ws3 = $wb.Sheets.Item($sheetName2)
$ws3.Cells.Clear()

# DB?????? v1.4?
S $ws3 1 1 "RELog6KiI77yI5pyA5paw54mIIHYxLjTvvIk=" $true $null 0xC45911 15
# ????: 2026-08-30
S $ws3 2 1 "5pyA57WC5pu05pawOiAyMDI2LTA4LTMw" $false $null 0x595959

# restaurants (???????)
S $ws3 4 1 "cmVzdGF1cmFudHMgKOS6r+afmeW8leeZv+aDheWgqSk=" $true $null 0x1F5C9E 12
# ???? ???? ?? ???
S $ws3 5 1 "44Kr44Op44Og5ZCN" $true 0x4472C4 0xFFFFFF
S $ws3 5 2 "44OH44O844K/5Z6L" $true 0x4472C4 0xFFFFFF
S $ws3 5 3 "5Yi257SE" $true 0x4472C4 0xFFFFFF
S $ws3 5 4 "6KuW55CG5ZCN" $true 0x4472C4 0xFFFFFF

$global:r = 6
function Row($c1,$c2,$c3,$c4) {
    S $ws3 $global:r 1 $c1 $false 0xFFFFFF; S $ws3 $global:r 2 $c2 $false 0xFFFFFF
    S $ws3 $global:r 3 $c3 $false 0xFFFFFF; S $ws3 $global:r 4 $c4 $false 0xFFFFFF
    $global:r++
}

Row "aWQ=" "SU5U" "UEssIEFVVE9fSU5DUkVNRU5U" "44Os44K544OI44Op44OzSUQ="
Row "bmFtZQ==" "VkFSQ0hBUigyNTUp" "Tk9UIE5VTEw=" "5bqX5ZCN"
Row "Y2F0ZWdvcnk=" "VkFSQ0hBUigxMDAp" "TlVMTA==" "44Kr44OG44K044OJKEphcGFuZXNlLCBJdGFsaWFu562JKQ=="
Row "ZGVzY3JpcHRpb24=" "VEVYVA==" "TlVMTA==" "6Kqs5piO44O757S55LuL5paH"
Row "YWRkcmVzcw==" "VkFSQ0hBUigxMDAp" "TlVMTA==" "5L2P5omB"
Row "aW1hZ2U=" "VkFSQ0hBUigyNTUp" "TlVMTA==" "55S75YOP44OV44Kh44Kk44Or44Gu44OR44K5"
Row "ZW1vamk=" "VkFSQ0hBUigxMCk=" "TlVMTA==" "44OH44OV44Kp44Or44OI57W15paH5a2X77yI44K344O844OJ44OH44O844K/55So77yJ"
Row "Y3JlYXRlZF9hdA==" "VElNRVNUQU1Q" "REVGQVVMVCBDVVJSRU5UX1RJTUVTVEFNUA==" "55m76Yyy5pel5pmC"

$global:r++
# reviews (??????)
S $ws3 $global:r 1 "cmV2aWV3cyAo44Os44OT44Ol44O85oO15aCxKQ==" $true $null 0x1F5C9E 12
$global:r++
S $ws3 $global:r 1 "44Kr44Op44Og5ZCN" $true 0x4472C4 0xFFFFFF
S $ws3 $global:r 2 "44OH44O844K/5Z6L" $true 0x4472C4 0xFFFFFF
S $ws3 $global:r 3 "5Yi257SE" $true 0x4472C4 0xFFFFFF
S $ws3 $global:r 4 "6KuW55CG5ZCN" $true 0x4472C4 0xFFFFFF
$global:r++

Row "aWQ=" "SU5U" "UEssIEFVVE9fSU5DUkVNRU5U" "44Os44OT44Ol44O8SUQ="
Row "cmVzdGF1cmFudF9pZA==" "SU5U" "RksgKHJlc3RhdXJhbnRzLmlkKSBPTiBERUxFVEUgQ0FTQ0FERSA=" "5a++6LGh44Os44K544OI44Op44OzSUQ="
Row "ZGF0ZQ==" "REFURQ==" "Tk9UIE5VTEw=" "5rOo5paH5pel"
Row "b3JkZXJfZGV0YWlscw==" "VEVYVA==" "Tk9UIE5VTEw=" "5rOo5paH5YaF5a65"
Row "aW1wcmVzc2lvbg==" "VEVYVA==" "Tk9UIE5VTEw=" "5oSf5oOz"
Row "cmF0aW5n" "VElOWUlOVA==" "TlVMTA==" "6KmV5L6h77yIMeOAnjXvvIk="
Row "aW1hZ2U=" "VkFSQ0hBUigyNTUp" "TlVMTA==" "55S75YOP44OV44Kh44Kk44Or44Gu44OR44K5"
Row "Y3JlYXRlZF9hdA==" "VElNRVNUQU1Q" "REVGQVVMVCBDVVJSRU5UX1RJTUVTVEFNUA==" "55m76Yyy5pel5pmC"

$ws3.Columns.AutoFit() | Out-Null
$ws3.Columns.Item(3).ColumnWidth = 35
$ws3.Columns.Item(4).ColumnWidth = 30

$wb.Save()
$wb.Close($false)
$excel.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null
Write-Output "Excel updated safely via base64 encoding"
