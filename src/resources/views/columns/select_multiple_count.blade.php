{{-- print items count of relationship --}}
<td>
    <?php
        $results = $entry->{$column['entity']};

        if ($results && $results->count()) {
            $results_array = $results->pluck($column['attribute']);
            echo count($results_array->toArray());
        } else {
            echo '0';
        }
    ?>
</td>
