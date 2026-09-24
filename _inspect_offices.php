<?php
foreach ([30,31,32] as $id) {
    $o = \App\Models\Business\Office::with("specialtiesRelation")->find($id);
    echo "== ID $id ==\n";
    echo "name_ar: " . ($o->name_ar ?? "NULL") . "\n";
    echo "specialties(json): " . json_encode($o->specialties, JSON_UNESCAPED_UNICODE) . "\n";
    echo "specialtiesRelation count: " . $o->specialtiesRelation->count() . "\n";
    foreach ($o->specialtiesRelation as $s) { echo "   spec: " . $s->name_ar . "\n"; }
    echo "display_specialty_ar: " . $o->display_specialty_ar . "\n";
    echo "\n";
}
