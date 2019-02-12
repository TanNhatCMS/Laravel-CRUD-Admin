{{-- single relationships (1-1, 1-n) --}}
<span>
    <?php
        $attributes = $crud->getModelAttributeFromRelation($entry, $column['entity'], $column['attribute']);
        if (count($attributes)) {
            if (isset($column['limit'])) {
                echo e(substr(implode(', ', $attributes), 0, $column['limit'])).'[&hellip;]';
            }
            else {
                echo e(implode(', ', $attributes));
            }
        } else {
            echo '-';
        }
    ?>
</span>
