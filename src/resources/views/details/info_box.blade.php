<!--
$this->addDetail([
    'label' => "Total Favorites",
    'type' => "info_box",
    'name' => 'test',
    'value' => 2,

    'info_box_options' => [
        'color' => 'green',
        'icon' => 'fa fa-star',
    ]
], 'statistic');
-->
<div @include('crud::inc.detail_wrapper_attributes.blade') >
    <div class="info-box bg-{!! $detail['info_box_options']['color'] !!}">
        <span class="info-box-icon"><i class="{!! $detail['info_box_options']['icon'] !!}"></i></span>
        <div class="info-box-content">
            <span class="info-box-text">{!! $detail['label'] !!}</span>
            <span class="info-box-number">{!! $detail['value'] !!}</span>
        </div>
    </div>
</div>
