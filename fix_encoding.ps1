# Fix double-UTF8 encoded characters in all twig and php files
# The broken chars are UTF-8 sequences read as latin1 then re-encoded as UTF-8

# Map of broken byte sequences (as byte arrays) to correct UTF-8 bytes
# Each broken sequence represents a wrongly double-encoded accented char
# Pattern: UTF-8 char X was stored as latin1 bytes, then those latin1 bytes were re-encoded as UTF-8
# é = C3 A9 -> stored as latin1 "Ã©" -> re-UTF8 encoded as E2 94 9C C2 AE ... wait

# Let's use a simpler approach: read each file as UTF-8, then do byte-level substitution
# Broken: E2 94 9C C2 AE = ├® -> should be C3 A9 = é
# Broken: E2 94 9C C3 A1 = ├á -> should be C3 A0 = à  
# Broken: E2 94 9C C2 BF = ├¿ -> should be C3 A8 = è
# Broken: E2 94 9C C2 AC = ├¬ -> should be C3 AA = ê
# Broken: E2 94 9C C2 AB = ├« -> should be C3 AE = î
# Broken: E2 94 9C C3 B4 = ├┤ -> should be C3 B4 = ô (same!)
# Broken: E2 94 9C C2 A7 = ├§ -> should be C3 A7 = ç
# Broken: C3 A2 E2 80 9A = â€š?

# Actually let me map the VISIBLE broken strings to correct ones using string replacement
# The terminal shows ├® which in UTF-8 is the bytes of those box-drawing chars

$fixMap = @(
    @{ from = [byte[]](0xE2, 0x94, 0x9C, 0xC2, 0xAE); to = [byte[]](0xC3, 0xA9) }  # ├® -> é
    @{ from = [byte[]](0xE2, 0x94, 0x9C, 0xC2, 0xA1); to = [byte[]](0xC3, 0xA0) }  # ├¡ -> à
    @{ from = [byte[]](0xE2, 0x94, 0x9C, 0xC3, 0xA0); to = [byte[]](0xC3, 0xA0) }  # ├à -> à
    @{ from = [byte[]](0xE2, 0x94, 0x9C, 0xC2, 0xBF); to = [byte[]](0xC3, 0xA8) }  # ├¿ -> è
    @{ from = [byte[]](0xE2, 0x94, 0x9C, 0xC2, 0xAC); to = [byte[]](0xC3, 0xAA) }  # ├¬ -> ê
    @{ from = [byte[]](0xE2, 0x94, 0x9C, 0xC2, 0xAB); to = [byte[]](0xC3, 0xAE) }  # ├« -> î
    @{ from = [byte[]](0xE2, 0x94, 0x9C, 0xC2, 0xB4); to = [byte[]](0xC3, 0xB4) }  # ├´ -> ô
    @{ from = [byte[]](0xE2, 0x94, 0x9C, 0xC2, 0xA7); to = [byte[]](0xC3, 0xA7) }  # ├§ -> ç
    @{ from = [byte[]](0xE2, 0x94, 0x9C, 0xC2, 0xB9); to = [byte[]](0xC3, 0xB9) }  # ├¹ -> ù
    @{ from = [byte[]](0xE2, 0x94, 0x9C, 0xC2, 0xBB); to = [byte[]](0xC3, 0xBB) }  # ├» -> û
    @{ from = [byte[]](0xE2, 0x94, 0x9C, 0xE2, 0x94, 0x82); to = [byte[]](0xC3, 0xA9) }  # ├│ -> é alternate
    @{ from = [byte[]](0xE2, 0x94, 0x9C, 0xE2, 0x94, 0x80); to = [byte[]](0xC3, 0xA0) }  # ├─ -> à alternate
)

function Replace-Bytes {
    param([byte[]]$data, [byte[]]$from, [byte[]]$to)
    $result = [System.Collections.Generic.List[byte]]::new()
    $i = 0
    while ($i -lt $data.Length) {
        $found = $false
        if ($i -le ($data.Length - $from.Length)) {
            $match = $true
            for ($j = 0; $j -lt $from.Length; $j++) {
                if ($data[$i + $j] -ne $from[$j]) { $match = $false; break }
            }
            if ($match) {
                $result.AddRange([byte[]]$to)
                $i += $from.Length
                $found = $true
            }
        }
        if (-not $found) {
            $result.Add($data[$i])
            $i++
        }
    }
    return $result.ToArray()
}

$targetDirs = @("c:\piDev\Projet-PI-2026\PI_dev\templates", "c:\piDev\Projet-PI-2026\PI_dev\src")
$allFiles = Get-ChildItem -Recurse -Path $targetDirs -Include "*.twig","*.php"
$totalFixed = 0

foreach ($f in $allFiles) {
    $bytes = [System.IO.File]::ReadAllBytes($f.FullName)
    $original = $bytes.Clone()
    $changed = $false
    
    foreach ($map in $fixMap) {
        $newBytes = Replace-Bytes -data $bytes -from $map.from -to $map.to
        if ($newBytes.Length -ne $bytes.Length -or (Compare-Object $bytes $newBytes)) {
            $bytes = $newBytes
            $changed = $true
        }
    }
    
    if ($changed) {
        [System.IO.File]::WriteAllBytes($f.FullName, $bytes)
        $totalFixed++
        Write-Host "Fixed: $($f.Name)"
    }
}

Write-Host "`nDone. Total files fixed: $totalFixed"
