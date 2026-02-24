<?php

namespace App\DataTables\Client;

use App\Models\Segment;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SegmentDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->orderColumn('id', function ($query, $order) {
                $query->orderBy('id', $order);
            })
            ->orderColumn('title', function ($query, $order) {
                $query->orderBy('title', $order);
            })
            ->addColumn('status', function ($segments) {
                return view('backend.client.whatsapp.segments.status', compact('segments'));
            })
            ->addColumn('action', function ($segments) {
                return view('backend.client.whatsapp.segments.action', compact('segments'));
            })
            ->setRowId('id');
    }



    public function query(Segment $model): QueryBuilder
    {
        return $model->newQuery()
            ->when(request()->has('order'), function ($query) {
                $columnIndex = request('order')[0]['column']; // Get column index
                $direction = request('order')[0]['dir']; // Get sort direction

                $columns = ['id', 'title']; // Ensure these match table columns

                if (isset($columns[$columnIndex])) {
                    $query->orderBy($columns[$columnIndex], $direction);
                }
            }, function ($query) {
                $query->orderBy('id', 'desc'); // Default sort
            })
            ->when(request('search')['value'] ?? false, function ($query, $search) {
                $query->where('title', 'like', "%$search%");
            })
            ->withPermission();
    }



    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc') // Default sort by ID descending
            ->selectStyleSingle()
            ->setTableAttribute('style', 'width:99.8%')
            ->parameters([
                'dom'        => 'Blfrtip',
                'buttons'    => [],
                'lengthMenu' => [[10, 25, 50, 100, 250], [10, 25, 50, 100, 250]],
                'order'      => [[0, 'desc']], // Default sorting
                'language'   => [
                    'searchPlaceholder' => __('search'),
                    'lengthMenu'        => '_MENU_ '.__('segments_per_page'),
                    'search'            => '',
                ],
            ]);
    }



    public function getColumns(): array
    {
        return [
            Column::make('id')
                ->title('#')
                ->orderable(true) // Enable sorting
                ->searchable(false)
                ->width(10),

            Column::make('title')
                ->title(__('Segments'))
                ->orderable(true) // Enable sorting
                ->searchable(true),

            Column::make('status')
                ->title(__('Status'))
                ->orderable(false) // Disable sorting for non-sortable columns
                ->searchable(false)
                ->width(10),

            Column::make('action')
                ->title(__('Action'))
                ->orderable(false) // Actions should not be sortable
                ->searchable(false)
                ->width(10),
        ];
    }


    protected function filename(): string
    {
        return 'brand_'.date('YmdHis');
    }
}
