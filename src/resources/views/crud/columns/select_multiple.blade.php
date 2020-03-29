{{-- relationships with pivot table (n-n) --}}
@php
    $results = data_get($entry, $column['name']);
@endphp

<span>
    <?php
    	if($column['count']) {
    		echo $results->count();
    	} elseif ($results && $results->count()) {
            $results_array = $results->pluck($column['attribute']);
            echo implode(', ', $results_array->toArray());
        } else {
            echo '-';
        }
    ?>
</span>