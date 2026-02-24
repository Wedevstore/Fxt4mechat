<?php

namespace App\DataTables\Client\Web;

use App\Models\WebTemplate;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TemplateDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('name', fn($row) => $row->name)
            ->addColumn('message_type', fn($row) => ucfirst($row->message_type))
            ->addColumn('message', fn($row) => $row->message ?? '-')
            ->addColumn('media_url', function ($row) {
                if ($row->media_url) {
                    return '<a href="'.$row->media_url.'" target="_blank">View Media</a>';
                }
                return '-';
            })
            ->addColumn('status', function ($row) {
                return $row->status 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return view('backend.client.web.template.action', compact('row'));
            })
            ->rawColumns(['media_url', 'status', 'action'])
            ->setRowId('id');
    }

    public function query(): QueryBuilder
    {
        return WebTemplate::query()
            ->where('client_id', auth()->user()->client_id)
            ->latest('id')
            ->when(request('search')['value'] ?? false, function ($query, $search) {
                $query->where('name', 'like', "%$search%")
                      ->orWhere('message_type', 'like', "%$search%")
                      ->orWhere('message', 'like', "%$search%");
            });
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->setTableAttribute('style', 'width:99.8%')
            ->footerCallback('function ( row, data, start, end, display ) {
                $(".dataTables_length select").addClass("form-select form-select-lg without_search mb-3");
                selectionFields();
            }')
            ->parameters([
                'dom'        => 'Blfrtip',
                'buttons'    => [
                    [], // export buttons if needed
                ],
                'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
                'language'   => [
                    'searchPlaceholder' => __("Search templates"),
                    'lengthMenu'        => '_MENU_ '.__('per page'),
                    'search'            => '',
                ],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('id')->data('DT_RowIndex')->title('#')->width(10),
            Column::computed('name')->title(__('Name')),
            Column::computed('message_type')->title(__('Type'))->addClass('text-center'),
            Column::computed('message')->title(__('Message'))->width(200),
            Column::computed('media_url')->title(__('Media'))->addClass('text-center'),
            Column::computed('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))
                ->exportable(false)
                ->printable(false)
                ->searchable(false)
                ->addClass('action-card text-center')
                ->width(80),
        ];
    }

    protected function filename(): string
    {
        return 'message_templates_' . date('YmdHis');
    }
}
