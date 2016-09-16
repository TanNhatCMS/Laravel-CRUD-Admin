{{-- custom return value via attribute --}}
<td>
	<?php
	    echo $entry->{$column['function_name']}()->first()->{$column['attribute']};
    ?>
</td>
