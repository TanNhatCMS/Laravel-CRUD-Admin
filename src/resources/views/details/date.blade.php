<!--
$this->addDetail([
    'label' => "Report Date",
    'type' => "date",
    'name' => 'created_at',
]);
-->
@if (!empty($entry->{$detail['name']}))
    {{ Date::parse($entry->{$detail['name']})->format(config('backpack.base.default_date_format')) }}
@endif
