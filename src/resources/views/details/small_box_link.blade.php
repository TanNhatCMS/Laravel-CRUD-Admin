<!--
$this->addDetail([
    'name' => 'view_link',
    'label' => 'View Thread',
    'type' => 'small_box_link',

    'route_name' => 'article.view',
    'attribute' => "user_id",

    'small_box_options' => [
        'color' => 'teal',
        'icon' => 'fa fa-eye',
    ],

    'full_link' => $this->data['entry']->thread->getfullLink(), // define the entire link manually

    // 'route_attribute' => 'slug' // automatically define a single param name and value to be the url
    // 'route_params' => ['var1' => 'foo', 'var2' => 'bar'], // definine each param individually
], 'statistic');
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

<div @include('crud::inc.detail_wrapper_attributes.blade') >
  <a href="{{ $link }}">
  <div class="small-box bg-{!! $detail['small_box_options']['color'] !!}">
    <div class="inner">
      @if(isset($detail['value']))
      <h3>{!! $detail['value'] !!}</h3>
      @endif
      <p>{!! $detail['label'] !!}</p>
    </div>
    <div class="icon" style="font-size: 30px; float:right;margin: 0;margin-top:13px;">
      <i class="{!! $detail['small_box_options']['icon'] !!}"></i>
    </div>
  </div>
  </a>
</div>
