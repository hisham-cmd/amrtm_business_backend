<?php
$c = App\Models\Category::where("key","ministries")->with("entities")->first();
echo "key=".$c->key." name=".$c->name_ar." entities=".$c->entities->count().PHP_EOL;
foreach($c->entities->take(8) as $e){
    echo " - ".$e->name_ar." (icon=".$e->icon." color=".$e->color." img=".$e->images." tag=".$e->tag_ar.") svcs=".$e->govServices()->count().PHP_EOL;
}
