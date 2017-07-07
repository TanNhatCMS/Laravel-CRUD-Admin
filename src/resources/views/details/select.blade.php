{{-- single relationships (1-1, 1-n) --}}

<!--
$this->addDetail([
    'label' => "Reported By",
    'type' => "select",
    'name' => 'user_id',
    'entity' => 'reporter',
    'attribute' => 'name',
    'model' => "App\User",
]);
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

{!! $value !!}
