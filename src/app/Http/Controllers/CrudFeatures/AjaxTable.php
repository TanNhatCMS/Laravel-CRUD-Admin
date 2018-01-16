<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

trait AjaxTable
{
    /**
     * The search function that is called by the data table.
     *
     * @return  JSON Array of cells in HTML form.
     */
    public function search()
    {
        $requestType = Request()->request_type;

        if ($requestType == 'excel') {
//            $table_name = $this->crud->model->getTable();
            $filename = ucfirst($this->crud->entity_name_plural . '-' . Carbon::now()->toDateString());
            $result = $this->crud->query->get();

            $data = array();
            foreach ($result as $item) {

                if (!method_exists($item, 'toExport')) {
                    $data[] = $item->toArray();
                } else {
                    $data[] = $item->toExport();
                }
            }

            Excel::create($filename, function ($excel) use ($data) {
                $excel->sheet('Sheet', function ($sheet) use ($data) {
                    $sheet->with($data);
                });
            })->store('xls');

            return response()->json([
                'error' => "",
                'download' => url('/exports') . '/' . $filename . '.xls',
            ]);

        }

        $this->crud->hasAccessOrFail('list');

        $totalRows = $filteredRows = $this->crud->count();

        // if a search term was present
        if ($this->request->input('search') && $this->request->input('search')['value']) {
            // filter the results accordingly
            $this->crud->applySearchTerm($this->request->input('search')['value']);
            // recalculate the number of filtered rows
            $filteredRows = $this->crud->count();
        }

// start the results according to the datatables pagination
if ($this->request->input('start')) {
    $this->crud->skip($this->request->input('start'));
}

// limit the number of results according to the datatables pagination
if ($this->request->input('length')) {
    $this->crud->take($this->request->input('length'));
}

// overwrite any order set in the setup() method with the datatables order
if ($this->request->input('order')) {
    $column_number = $this->request->input('order')[0]['column'];
    if ($this->crud->details_row) {
        $column_number = $column_number - 1;
    }
    $column_direction = $this->request->input('order')[0]['dir'];
    $column = $this->crud->findColumnById($column_number);

    if ($column['tableColumn']) {
        $this->crud->orderBy($column['name'], $column_direction);
    }
}

$entries = $this->crud->getEntries();

return $this->crud->getEntriesAsJsonForDatatables($entries, $totalRows, $filteredRows);
}
}
