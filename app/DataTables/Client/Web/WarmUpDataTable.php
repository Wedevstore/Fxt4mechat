<?php

namespace App\DataTables\Client\Web;

use App\Models\WhatsAppWarmup;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WarmUpDataTable extends DataTable
{
    /**
     * Build DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->orderColumn('id', fn($query, $order) => $query->orderBy('id', $order))
            ->orderColumn('name', fn($query, $order) => $query->orderBy('name', $order))
            
            // Display columns properly
            ->addColumn('device', function ($warmup) {
                return $warmup->device?->name ?? '<span class="text-muted">No Device</span>';
            })
            ->addColumn('day', fn($warmup) => $warmup->day)
            // ->addColumn('messages_sent_today', fn($warmup) => $warmup->messages_sent_today)
            ->addColumn('status', function ($warmup) {
                return view('backend.client.web.warm_up.column.status', compact('warmup'));
            })
            ->addColumn('action', function ($warmup) {
                return view('backend.client.web.warm_up.column.action', compact('warmup'));
            })
            ->rawColumns(['device', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     */
    public function query(WhatsAppWarmup $model): QueryBuilder
    {
        $client = auth()->user()->client;

        return $model->newQuery()
            ->where('client_id', $client->id)
            ->when(request('search')['value'] ?? false, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%")
                      ->orWhere('day', 'like', "%{$search}%");
                });
            })
            ->with(['device'])
            ->orderByDesc('id');
    }

    /**
     * Optional HTML builder setup.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('warmup-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->selectStyleSingle()
            ->setTableAttribute('style', 'width:99.8%')
            ->footerCallback('function ( row, data, start, end, display ) {
                $(".dataTables_length select").addClass("form-select form-select-lg without_search mb-3");
                selectionFields();
            }')
            ->parameters([
                'dom'        => 'Blfrtip',
                'buttons'    => [],
                'lengthMenu' => [[10, 25, 50, 100, 250], [10, 25, 50, 100, 250]],
                'language'   => [
                    'searchPlaceholder' => __("Search"),
                    'lengthMenu'        => '_MENU_ '.__('Records per page'),
                    'search'            => '',
                ],
            ]);
    }

    /**
     * Define table columns.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#')->width(10)->orderable(true),
            Column::make('name')->title(__('Name'))->orderable(true),
            Column::make('device')->title(__('Device')),
            Column::make('day')->title(__('Day')),
            // Column::make('messages_sent_today')->title(__('Messages Sent Today')),
            Column::make('status')->title(__('Status')),
            Column::computed('action')
                ->title(__('Action'))
                ->exportable(false)
                ->printable(false)
                ->searchable(false)
                ->addClass('text-center')
                ->width(10),
        ];
    }

    /**
     * Filename for export.
     */
    protected function filename(): string
    {
        return 'whatsapp_warmup_'.date('YmdHis');
    }
}
