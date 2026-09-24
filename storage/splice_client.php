<?php
$src      = 'resources/views/update_service/provider-account.blade.php';
$tmp      = 'C:/Users/hisha/AppData/Local/Temp/opencode/client_section_new.blade';
$newBlock = file_get_contents($tmp);
$lines    = file($src, FILE_IGNORE_NEW_LINES);

if (count($lines) < 340) { fwrite(STDERR, "Unexpected line count\n"); exit(1); }

$out = [];
foreach (range(1, 157) as $i)      $out[] = $lines[$i - 1];   // keep 1..157
foreach (explode("\n", $newBlock) as $ln) $out[] = $ln;        // new block (158..340)
foreach (range(341, count($lines)) as $i) $out[] = $lines[$i - 1]; // keep 341..end

file_put_contents($src, implode("\n", $out) . "\n");
echo "OK total lines: " . count($out) . PHP_EOL;