<?php

namespace App\DataTables\Client\Web;

use App\Enums\TypeEnum;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VerifyContactDataTable extends DataTable
{
    protected $verifyNumber;

    /**
     * Accept verifyNumber from Controller
     */
    public function with(array|string $key, array|string|null $value = null): static
    {
        if (is_string($key) && $key === 'verifyNumber') {
            $this->verifyNumber = $value;
        } elseif (is_array($key) && isset($key['verifyNumber'])) {
            $this->verifyNumber = $key['verifyNumber'];
        }

        return parent::with($key, $value);
    }

    /**
     * Build DataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('created_at', fn($contact) => $contact->created_at?->format('d-m-Y'))
            ->addColumn('phone', fn($contact) => view('backend.client.web.verify-number.contacts.partials.phone', ['q' => $contact]))
            ->addColumn('status', fn($contact) => view('backend.client.web.verify-number.contacts.partials.status', ['q' => $contact]))
            ->addColumn('checkbox', fn($contact) => view('backend.client.web.verify-number.contacts.partials.checkbox', ['q' => $contact]))
            ->addColumn('contacts_list', function ($contact) {
                $contactLists = '';
                if (!is_null($contact->contactList)) {
                    foreach ($contact->contactList as $relation) {
                        $contactLists .= optional($relation->list)->name . ', ';
                    }
                    $contactLists = rtrim($contactLists, ', ');
                }
                return $contactLists;
            })
            ->addColumn('segments', function ($contact) {
                $segments = '';
                if (!is_null($contact->segmentList)) {
                    foreach ($contact->segmentList as $relation) {
                        $segments .= optional($relation->segment)->title . ', ';
                    }
                    $segments = rtrim($segments, ', ');
                }
                return $segments;
            })
            ->addColumn('action', fn($contact) => view('backend.client.web.verify-number.contacts.partials.action', ['q' => $contact]))
            ->addColumn('verify_whatsapp', fn($contact) => view('backend.client.web.verify-number.contacts.partials.verify_whatsapp', ['q' => $contact]))
            ->addColumn('name', fn($contact) => view('backend.client.web.verify-number.contacts.partials.name', ['q' => $contact]))
            ->rawColumns(['created_at'])
            ->setRowId('id');
    }

    /**
     * Query for DataTable
     */
    public function query(Contact $model)
    {
        $verifyNumber = $this->verifyNumber ?? null;

        $query = $model
            ->where('contacts.client_id', auth()->user()->client->id)
            ->where('contacts.status', 1)
            ->where('contacts.is_blacklist', 0)
            ->where('type', TypeEnum::WHATSAPP)
            ->whereNotNull('phone')
            ->latest()
            ->with(['contactList.list', 'segmentList.segment']);
        

        // Apply filters from verifyNumber
        if ($verifyNumber) {
            // Fix: decode only if string
            $contactListIds = $verifyNumber->contact_list_ids;
            if (is_string($contactListIds)) $contactListIds = json_decode($contactListIds, true);
            $contactListIds = is_array($contactListIds) ? array_map('intval', $contactListIds) : [];

            $segmentIds = $verifyNumber->segment_ids;
            if (is_string($segmentIds)) $segmentIds = json_decode($segmentIds, true);
            $segmentIds = is_array($segmentIds) ? array_map('intval', $segmentIds) : [];

            if (!empty($contactListIds)) {
                $query->whereHas('contactList', function ($q) use ($contactListIds) {
                    $q->whereIn('contact_relation_lists.contact_list_id', $contactListIds);
                });
            }

            if (!empty($segmentIds)) {
                $query->whereHas('segmentList', function ($q) use ($segmentIds) {
                    $q->whereIn('contact_relation_segments.segment_id', $segmentIds);
                });
            }
        }


        // Apply search filters
        $query
            ->when($this->request->name, fn($q, $name) => $q->where('name', 'like', "%$name%"))
            ->when($this->request->phone, fn($q, $phone) => $q->where('phone', $phone))
            ->when($this->request->country_id, fn($q, $country_id) => $q->where('country_id', $country_id))
            ->when($this->request->status !== null, function ($q) {
                if ($this->request->status == '1') $q->where('status', true);
                elseif ($this->request->status == '0') $q->where('status', false);
            })
            ->when($this->request->is_blacklist !== null, function ($q) {
                if ($this->request->is_blacklist == '1') $q->where('is_blacklist', true);
                elseif ($this->request->is_blacklist == '0') $q->where('is_blacklist', false);
            })
            ->when(request('search')['value'] ?? false, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%$search%")
                        ->orWhere('username', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
                });
            });

        return $query;
    }

    /**
     * Build HTML
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->setTableAttribute('style', 'width:99.8%')
            ->footerCallback('function ( row, data, start, end, display ) {
                $(".dataTables_length select").addClass("form-select form-select-lg without_search mb-3");
                selectionFields();
            }')
            ->parameters([
                'dom'        => 'Blfrtip',
                'buttons'    => [[]],
                'lengthMenu' => [[10, 25, 50, 100, 250], [10, 25, 50, 100, 250]],
                'language'   => [
                    'searchPlaceholder' => __('search'),
                    'lengthMenu'        => '_MENU_ ' . __('contacts_per_page'),
                    'search'            => '',
                ],
            ]);
    }

    /**
     * Table Columns
     */
    public function getColumns(): array
    {
        return [
            // Column::computed('checkbox')
            //     ->title('<div class="custom-control custom-checkbox"><label class="custom-control-label" for="checkAll"><input id="checkAll" class="custom-control-input custom-checkbox" value="checkAll" type="checkbox"><span></span></label></div>')
            //     ->width(10),
            Column::computed('id')->data('DT_RowIndex')->title('#')->width(10),
            Column::computed('name')->title(__('name')),
            Column::computed('phone')->title(__('phone')),
            Column::computed('contacts_list')->title(__('contacts_list')),
            Column::computed('segments')->title(__('segments')),
            Column::computed('status')->title(__('status')),
            Column::computed('verify_whatsapp')->title(__('verify_whatsapp')),
            Column::computed('action')
                ->title(__('action'))
                ->exportable(false)
                ->printable(false)
                ->searchable(false)
                ->addClass('action-card')
                ->width(10),
        ];
    }

    protected function filename(): string
    {
        return 'contacts_' . date('YmdHis');
    }
}
