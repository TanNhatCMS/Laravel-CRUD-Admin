<div @include('crud::inc.field_wrapper_attributes') >
<?php
if (in_array('label', $field)) {
    if ($field['label'] != '') {
        echo '<label>'.$field['label'].'</label>';
    }
}

$component = '<'.$field['name'];


foreach ($field as $key => $value) {
    if (! in_array($key, ['name', 'type', 'label'])) {
        $encoded = json_encode($value, true);
        $component .= ' :'.$key."='".$encoded."'";
    }
}


$component .= '></'.$field['name'].'>';
echo $component;
?>
</div>
