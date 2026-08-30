$OutputEncoding = [System.Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false
$path = "C:\xampp\htdocs\foodlog\docs\FoodLog_機能設計書.xlsx"
$wb = $excel.Workbooks.Open($path)
$ws2 = $wb.Sheets.Item("機能一覧")

$ws2.Cells.Item(6, 3).Value2 = "名前(50字)・カテゴリ(200字)・説明(200字textarea)・住所(100字)・写真"
$ws2.Cells.Item(7, 3).Value2 = "名前(50字)・カテゴリ(200字)・説明(200字textarea)・住所(100字)・写真を更新"
$ws2.Cells.Item(11, 3).Value2 = "日付(未来日不可)・注文内容(200字)・感想(500字)・評価(必須)・写真(1枚)"
$ws2.Cells.Item(13, 3).Value2 = "編集モーダルで更新（画像変更対応・同じ文字制限適用）"

$ws3 = $wb.Sheets.Item("DB設計")
$ws3.Cells.Clear()

function S($ws,$r,$c,$v,$bold=$false,$bg=$null,$fg=$null,$sz=$null) {
    $cell = $ws.Cells.Item($r,$c)
    $cell.Value2 = $v
    if ($bold) { $cell.Font.Bold = $true }
    if ($sz) { $cell.Font.Size = $sz }
    if ($bg) { $cell.Interior.Color = $bg }
    if ($fg) { $cell.Font.Color = $fg }
}

S $ws3 1 1 "DB設計（最新版 v1.4）" $true $null 0xC45911 15
S $ws3 2 1 "最終更新: 2026-08-30" $false $null 0x595959

$tables = @(
  @{ name="restaurants (レストラン情報)"; rows=@(
    @("id","INT","PK, AUTO_INCREMENT","レストランID"),
    @("name","VARCHAR(255)","NOT NULL","店名"),
    @("category","VARCHAR(100)","NULL","カテゴリ（Japanese, Italian等）"),
    @("description","TEXT","NULL","説明・紹介文"),
    @("address","VARCHAR(100)","NULL","住所"),
    @("image","VARCHAR(255)","NULL","画像ファイルのパス"),
    @("emoji","VARCHAR(10)","NULL","デフォルト絵文字（シードデータ用）"),
    @("created_at","TIMESTAMP","DEFAULT CURRENT_TIMESTAMP","登録日時")
  )},
  @{ name="reviews (レビュー情報)"; rows=@(
    @("id","INT","PK, AUTO_INCREMENT","レビューID"),
    @("restaurant_id","INT","FK (restaurants.id) ON DELETE CASCADE","対象レストランID"),
    @("date","DATE","NOT NULL","注文日"),
    @("order_details","TEXT","NOT NULL","注文内容"),
    @("impression","TEXT","NOT NULL","感想"),
    @("rating","TINYINT","NULL","評価（1〜5）"),
    @("image","VARCHAR(255)","NULL","画像ファイルのパス"),
    @("created_at","TIMESTAMP","DEFAULT CURRENT_TIMESTAMP","登録日時")
  )}
)

$row = 4
foreach ($t in $tables) {
    S $ws3 $row 1 $t.name $true $null 0x1F5C9E 12
    $row++
    $hdr = @("カラム名","データ型","制約","論理名")
    for ($i=0; $i -lt 4; $i++) { S $ws3 $row ($i+1) $hdr[$i] $true 0x4472C4 0xFFFFFF }
    $row++
    foreach ($r in $t.rows) {
        $bg = if (($row%2)-eq0) { 0xDAE8FC } else { 0xFFFFFF }
        for ($c=0; $c -lt 4; $c++) { S $ws3 $row ($c+1) $r[$c] $false $bg }
        $row++
    }
    $row++
}
$ws3.Columns.AutoFit() | Out-Null
$ws3.Columns.Item(3).ColumnWidth = 35
$ws3.Columns.Item(4).ColumnWidth = 30

$wb.Save()
$wb.Close($false)
$excel.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null
Write-Output "Excel updated via file correctly"
