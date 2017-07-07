<!--
$this->addDetail([
    'label' => "Thread Responses",
    'type' => "info_box_select",
    'name' => 'response_count',
    'entity' => 'thread',
    'model' => "App\DiscussThread",
    'attribute' => 'replies',
    'count' => true,
    'info_box_options' => [
        'color' => 'purple',
        'icon' => 'fa fa-comments',
    ]
], 'statistic');
-->

@php
    $count = false;

    if(isset($detail['count'])) {
        $count = true;
    }

    if(isset($detail['value'])) {
        $value = $detail['value'];
    } else {
        if($count) {
            $value = count($entry->{$detail['entity']}->{$detail['attribute']});
        } else {
            $value = $entry->{$detail['entity']}->{$detail['attribute']};
        }
    }
@endphp

<div @include('crud::inc.detail_wrapper_attributes.blade') >
    <div class="info-box bg-{!! $detail['info_box_options']['color'] !!}">
        <span class="info-box-icon"><i class="{!! $detail['info_box_options']['icon'] !!}"></i></span>
        <div class="info-box-content">
            <span class="info-box-text">{!! $detail['label'] !!}</span>
            <span class="info-box-number">{!! $value !!}</span>
        </div>
    </div>
</div>
