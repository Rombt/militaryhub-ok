<#
.SYNOPSIS
Рекурсивно строит файловую структуру папки в виде дерева и копирует результат в буфер обмена.

.DESCRIPTION
Проходит по всем папкам и файлам внутри BasePath.
Исключает заданные директории.
Формирует текстовое дерево структуры вида:

project
+-- src
|   +-- index.php
+-- utils

Результат:
- копируется в буфер обмена
- выводится в консоль

.PARAMETER BasePath
Папка, структуру которой нужно построить.

.PARAMETER ExcludeDirs
Список директорий, которые нужно исключить. Относительный путь.

.EXAMPLE
.\copy-tree-structure.ps1 -BasePath .

.EXAMPLE
.\copy-tree-structure.ps1 -BasePath "D:\web\multisite\wp-content\themes\tc-1"
#>

param(
    [Parameter(Mandatory = $false)]
    [string]$BasePath = ".",

    [string[]]$ExcludeDirs = @()
)

# дефолтные исключения
$defaultExcludeDirs = @(
    "node_modules",
    ".git",
    "vendor",
    "dist",
    "build",
    ".vscode",
    ".cache"
)

# объединяем дефолтные + пользовательские
$ExcludeDirs = $defaultExcludeDirs + $ExcludeDirs

# Нормализация пути
$BasePath = (Resolve-Path $BasePath).Path

$output = New-Object System.Collections.Generic.List[string]

function Build-Tree {
    param(
        [string]$Folder,
        [string]$Prefix = ""
    )

    $items = Get-ChildItem -LiteralPath $Folder |
    Where-Object {
        foreach ($dir in $ExcludeDirs) {
            if ($_.FullName -match "\\$dir(\\|$)") {
                return $false
            }
        }
        return $true
    } |
    Sort-Object @{ Expression = { - $_.PSIsContainer } }, Name

    for ($i = 0; $i -lt $items.Count; $i++) {

        $item = $items[$i]
        $isLast = ($i -eq ($items.Count - 1))

        if ($isLast) {
            $branch = "+-- "
            $nextPrefix = "$Prefix    "
        }
        else {
            $branch = "|-- "
            $nextPrefix = "$Prefix|   "
        }

        $output.Add("$Prefix$branch$($item.Name)")

        if ($item.PSIsContainer) {
            Build-Tree -Folder $item.FullName -Prefix $nextPrefix
        }
    }
}

$output.Add((Split-Path $BasePath -Leaf))

Build-Tree -Folder $BasePath

$result = $output -join [Environment]::NewLine

Set-Clipboard -Value $result

Write-Host ""
Write-Host "===== TREE STRUCTURE ====="
Write-Host ""
Write-Host $result
Write-Host ""
Write-Host "Copied to clipboard"
