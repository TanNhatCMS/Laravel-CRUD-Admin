<!--
$this->addDetail([
    'label' => "View Thread Live",
    'type' => "link_select",
    'name' => 'thread_id',
    'entity' => 'thread',
    'attribute' => "title",
    'model' => "App\DiscussThread",

    'full_link' => $this->data['entry']->thread->getfullLink(), // define the entire link manually

    // 'route_name' => 'discuss.show' // laravel friendly name of the route
    // 'route_attribute' => 'slug' // automatically define a single param name and value to be the url
    // 'route_params' => ['var1' => 'foo', 'var2' => 'bar'], // definine each param individually
]);
-->

<!-- link option -->
  @php
    if(isset($detail['full_link'])) {
        $link = $detail['full_link'];
    } elseif(isset($detail['route_params'])) {
        $link = route($detail['route_name'], $detail['route_params']);
    } else {
        if(isset($detail['route_attribute'])) {
            $route_params = [$detail['route_attribute'] => $entry->{$detail['entity']}->{$detail['route_attribute']}];
        } else {
            $route_params = ['id' => $entry->{$detail['attribute']}];
        }
        $link = route($detail['route_name'], $route_params);
    }
  @endphp

<a href="{{ $link }}">
    @if ($entry->{$detail['entity']})
        {{ $entry->{$detail['entity']}->{$detail['attribute']} }}
    @else
        {{ str_limit(strip_tags($entry->{$detail['attribute']}), 80, "[...]") }}
    @endif
</a>
