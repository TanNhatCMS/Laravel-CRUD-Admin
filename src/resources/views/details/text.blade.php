<!-- text option -->
<!--
$this->addDetail([
    'label' => "Text Detail",
    'type' => "text",
    'name' => 'Test',
]);
-->
{{ str_limit(strip_tags($detail['name']), 80, "[...]") }}
