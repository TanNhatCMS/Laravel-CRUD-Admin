<!--
$this->addDetail([
    'name' => 'view_link',
    'label' => 'View Original Item',
    'type' => 'small_box_link',

    'route_name' => 'article.view',
    'attribute' => "user_id",

    'small_box_options' => [
        'color' => 'teal',
        'icon' => 'fa fa-eye',
    ],
], 'statistic');
-->
<div @include('crud::inc.detail_wrapper_attributes.blade') >
    <div class="info-box bg-{!! $detail['info_box_options']['color'] !!}">
        <span class="info-box-icon"><i class="{!! $detail['info_box_options']['icon'] !!}"></i></span>
        <div class="info-box-content">
            <span class="info-box-text">{!! $detail['label'] !!}</span>
            @if(isset($detail['value']))
            <span class="info-box-number">{!! $detail['value'] !!}</span>
            @endif
        </div>
    </div>
</div>
