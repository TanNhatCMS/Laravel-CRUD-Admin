<!-- link option -->

<!--
$this->addDetail([
    'label' => "Test",
    'type' => "link",
    'name' => 'thread_id',
    'entity' => 'thread',
    'attribute' => "id",

    'full_link' => $this->data['entry']->thread->getfullLink(), // define the entire link manually

    // 'route_attribute' => 'slug' // automatically define a single param name and value to be the url
    // 'route_params' => ['var1' => 'foo', 'var2' => 'bar'], // definine each param individually
]);
-->

@php
    if(isset($detail['full_link'])) {
        $link = $detail['full_link'];
    } elseif(isset($detail['route_params'])) {
        $link = route($detail['route_name'], $detail['route_params']);
    } else {
        if(isset($detail['route_attribute'])) {
            $route_params = [$detail['route_attribute'] => $entry->{$detail['route_attribute']}];
        } else {
            $route_params = ['id' => $entry->{$detail['attribute']}];
        }
        $link = route($detail['route_name'], $route_params);
    }
@endphp

<a href="{{ $link }}">
    {{ str_limit(strip_tags($entry->{$detail['attribute']}), 80, "[...]") }}
</a>
