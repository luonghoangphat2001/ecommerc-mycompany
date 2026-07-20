<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Ecommerce\Workspace\Services\WorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepartmentWorkspaceController extends Controller
{
    protected WorkspaceService $workspaceService;

    public function __construct(WorkspaceService $workspaceService)
    {
        $this->workspaceService = $workspaceService;
    }

    public function export(Request $request, string $code): StreamedResponse
    {
        $department = \App\Models\Department::where('code', $code)->first();
        $datasets = $this->workspaceDatasets($code);
        $filename = 'workspace-' . Str::slug($code) . '-' . now()->format('Ymd-His') . '.xls';

        return response()->streamDownload(function () use ($datasets, $department): void {
            echo '<html><head><meta charset="UTF-8"></head><body>';

            foreach ($datasets as $key => $dataset) {
                $modelClass = $dataset['model'];
                $columns = $dataset['columns'];
                $query = $modelClass::query();

                if ($department && in_array('department_id', $columns, true)) {
                    $query->where('department_id', $department->id);
                }

                echo '<table border="1">';
                echo '<tr><th colspan="' . count($columns) . '">' . e($dataset['label']) . '</th></tr>';
                echo '<tr>';
                foreach ($columns as $column) {
                    echo '<th>' . e($column) . '</th>';
                }
                echo '</tr>';

                $query->latest('id')->chunk(200, function ($records) use ($columns): void {
                    foreach ($records as $record) {
                        echo '<tr>';
                        foreach ($columns as $column) {
                            $value = data_get($record, $column);
                            if ($value instanceof \DateTimeInterface) {
                                $value = $value->format('Y-m-d H:i:s');
                            } elseif ($value instanceof \BackedEnum) {
                                $value = $value->value;
                            }
                            echo '<td>' . e((string) $value) . '</td>';
                        }
                        echo '</tr>';
                    }
                });

                echo '</table><br>';
            }

            echo '</body></html>';
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    public function import(Request $request, string $code): RedirectResponse
    {
        $request->validate([
            'dataset' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:csv,txt,xls,xlsx', 'max:5120'],
        ]);

        $datasets = $this->workspaceDatasets($code);
        $datasetKey = $request->input('dataset');
        if (! isset($datasets[$datasetKey])) {
            return back()->with('status', 'Dataset không hợp lệ.');
        }

        $department = \App\Models\Department::where('code', $code)->first();
        $dataset = $datasets[$datasetKey];
        $rows = $this->readSpreadsheetRows($request->file('file')->getRealPath(), $request->file('file')->getClientOriginalExtension());
        $headers = array_shift($rows);
        if (! is_array($headers) || $headers === []) {
            return back()->with('status', __('admin.messages.import_missing_header'));
        }

        $headers = array_map(fn ($header) => trim((string) $header), $headers);
        $columns = $dataset['columns'];
        $modelClass = $dataset['model'];
        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $payload = [];
            foreach ($headers as $index => $header) {
                if (! in_array($header, $columns, true) || $header === 'id') {
                    continue;
                }
                $payload[$header] = $row[$index] ?? null;
            }

            if ($department && in_array('department_id', $columns, true)) {
                $payload['department_id'] = $department->id;
            }

            $payload = array_filter($payload, fn ($value) => $value !== '');
            if ($payload === []) {
                $skipped++;
                continue;
            }

            try {
                $modelClass::create($payload);
                $created++;
            } catch (\Throwable) {
                $skipped++;
            }
        }

        return redirect()->route('admin.workspace.show', $code)
            ->with('status', __('admin.messages.import_completed', ['created' => $created, 'skipped' => $skipped]));
    }

    public function show(Request $request, string $code)
    {
        // Route explicit workspaces to their respective methods to maintain backwards compatibility 
        // with specific layouts and specific metric collections if any custom logic remains
        if (in_array($code, ['cfo', 'logistics', 'rnd', 'ops', 'cskh', 'hr'])) {
            return $this->{$code}($request);
        }

        $period = $request->query('period', 'all');
        $data = $this->workspaceService->getDefaultData($code);

        $view = "admin.workspaces.{$code}";
        if (!view()->exists($view)) {
            $view = 'admin.workspaces.default';
        }

        return view($view, array_merge($data, [
            'period' => $period,
            'title' => __("workspace.sidebar.{$code}") === "workspace.sidebar.{$code}" ? $data['department']->name : __("workspace.sidebar.{$code}"),
            'description' => $data['department']->description ?? 'Quản lý ' . $data['department']->name,
            'workspaceCode' => $code,
            'importDatasets' => $this->workspaceDatasetOptions($code),
        ]));
    }

    public function cfo(Request $request)
    {
        $period = $request->query('period', 'all');
        $cfoService = app(\App\Ecommerce\Workspace\Services\CfoService::class);
        $data = $cfoService->getWorkspaceData($period);
        $metrics = $data['metrics'];
        
        return view('admin.workspaces.cfo', [
            'metrics' => $metrics,
            'proposals' => $data['proposals'],
            'payrolls' => $data['payrolls'],
            'prices' => $data['prices'],
            'period' => $period,
            'title' => __('workspace.cfo.title'),
            'description' => __('workspace.cfo.description'),
            'tabs' => [__('workspace.cfo.tabs.approvals'), __('workspace.cfo.tabs.cashflow'), __('workspace.cfo.tabs.pricing')],
            'workspaceCode' => 'cfo',
            'importDatasets' => $this->workspaceDatasetOptions('cfo'),
        ]);
    }

    public function logistics(Request $request)
    {
        $period = $request->query('period', 'all');
        $logisticsService = app(\App\Ecommerce\Workspace\Services\LogisticsService::class);
        $data = $logisticsService->getWorkspaceData($period);
        
        return view('admin.workspaces.logistics', [
            'metrics' => $data['metrics'],
            'lowStockAlerts' => $data['lowStockAlerts'],
            'inventoryStocks' => $data['inventoryStocks'],
            'purchaseOrders' => $data['purchaseOrders'],
            'period' => $period,
            'title' => __('workspace.logistics.title'),
            'description' => __('workspace.logistics.description'),
            'workspaceCode' => 'logistics',
            'importDatasets' => $this->workspaceDatasetOptions('logistics'),
        ]);
    }

    public function rnd(): View
    {
        return view('admin.workspaces.rnd', array_merge($this->workspaceService->getRndData(), [
            'title' => __('workspace.rnd.title'),
            'description' => __('workspace.rnd.description'),
            'workspaceCode' => 'rnd',
            'importDatasets' => $this->workspaceDatasetOptions('rnd'),
        ]));
    }

    public function ops(Request $request)
    {
        $period = $request->query('period', 'all');
        $opsService = app(\App\Ecommerce\Workspace\Services\OpsService::class);
        $data = $opsService->getWorkspaceData($period);

        return view('admin.workspaces.ops', [
            'metrics' => $data['metrics'],
            'liveOrders' => $data['liveOrders'],
            'incidents' => $data['incidents'],
            'period' => $period,
            'title' => __('workspace.ops.title'),
            'description' => __('workspace.ops.description'),
            'workspaceCode' => 'ops',
            'importDatasets' => $this->workspaceDatasetOptions('ops'),
        ]);
    }

    public function cskh(Request $request)
    {
        $period = $request->query('period', 'all');
        $cskhService = app(\App\Ecommerce\Workspace\Services\CskhService::class);
        $data = $cskhService->getWorkspaceData($period);

        return view('admin.workspaces.cskh', [
            'metrics' => $data['metrics'],
            'reviews' => $data['reviews'],
            'coupons' => $data['coupons'],
            'period' => $period,
            'tabs' => [__('workspace.cskh.content.reviews_title'), __('workspace.cskh.content.sentiment_title'), __('workspace.cskh.content.coupons_title')],
            'title' => __('workspace.cskh.title'),
            'description' => __('workspace.cskh.description'),
            'workspaceCode' => 'cskh',
            'importDatasets' => $this->workspaceDatasetOptions('cskh'),
        ]);
    }

    public function hr(Request $request)
    {
        $period = $request->query('period', 'all');
        $hrService = app(\App\Ecommerce\Workspace\Services\HrService::class);
        $data = $hrService->getWorkspaceData($period);

        return view('admin.workspaces.hr', [
            'metrics' => $data['metrics'],
            'contracts' => $data['contracts'],
            'period' => $period,
            'tabs' => [__('workspace.hr.content.contracts_title'), __('workspace.hr.content.health_title'), __('workspace.hr.content.risk_title')],
            'title' => __('workspace.hr.title'),
            'description' => __('workspace.hr.description'),
            'workspaceCode' => 'hr',
            'importDatasets' => $this->workspaceDatasetOptions('hr'),
        ]);
    }

    private function workspaceDatasetOptions(string $code): array
    {
        return collect($this->workspaceDatasets($code))
            ->mapWithKeys(fn (array $dataset, string $key) => [$key => $dataset['label']])
            ->toArray();
    }

    private function workspaceDatasets(string $code): array
    {
        $map = [
            'cfo' => [
                'financial_proposals' => ['label' => 'Đề xuất tài chính', 'model' => \App\Models\DepartmentFinancialProposal::class],
                'payrolls' => ['label' => 'Bảng lương', 'model' => \App\Models\DepartmentPayroll::class],
                'material_prices' => ['label' => 'Giá nguyên vật liệu', 'model' => \App\Models\DepartmentMaterialPrice::class],
            ],
            'logistics' => [
                'purchase_orders' => ['label' => 'Đơn mua hàng', 'model' => \App\Models\DepartmentPurchaseOrder::class],
            ],
            'ops' => [
                'incidents' => ['label' => 'Sự cố vận hành', 'model' => \App\Models\DepartmentIncident::class],
            ],
            'cskh' => [
                'customer_reviews' => ['label' => 'Đánh giá khách hàng', 'model' => \App\Models\DepartmentCustomerReview::class],
            ],
            'hr' => [
                'employee_contracts' => ['label' => 'Hợp đồng nhân sự', 'model' => \App\Models\DepartmentEmployeeContract::class],
            ],
            'rnd' => [
                'department_agents' => ['label' => 'Nhân sự phòng ban', 'model' => \App\Models\DepartmentAgent::class],
            ],
        ];

        $datasets = $map[$code] ?? [
            'department_agents' => ['label' => 'Nhân sự phòng ban', 'model' => \App\Models\DepartmentAgent::class],
        ];

        foreach ($datasets as $key => $dataset) {
            $model = new $dataset['model'];
            $columns = array_values(array_filter(
                array_merge(['id'], $model->getFillable(), ['created_at', 'updated_at']),
                fn (string $column) => Schema::hasColumn($model->getTable(), $column)
            ));
            $datasets[$key]['columns'] = $columns;
        }

        return $datasets;
    }

    private function readSpreadsheetRows(string $path, string $extension): array
    {
        $extension = strtolower($extension);

        if ($extension === 'xlsx' && class_exists(\ZipArchive::class)) {
            return $this->readXlsxRows($path);
        }

        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function readXlsxRows(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $shared = simplexml_load_string($sharedXml);
            foreach ($shared->si ?? [] as $si) {
                $text = '';
                if (isset($si->t)) {
                    $text = (string) $si->t;
                } elseif (isset($si->r)) {
                    foreach ($si->r as $run) {
                        $text .= (string) $run->t;
                    }
                }
                $sharedStrings[] = $text;
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row ?? [] as $rowNode) {
            $row = [];
            foreach ($rowNode->c as $cell) {
                $ref = (string) $cell['r'];
                $index = $this->excelColumnIndex(preg_replace('/\d+/', '', $ref));
                $type = (string) $cell['t'];
                $value = (string) ($cell->v ?? '');

                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                }

                $row[$index] = $value;
            }

            if ($row !== []) {
                ksort($row);
                $rows[] = array_replace(array_fill(0, max(array_keys($row)) + 1, ''), $row);
            }
        }

        return $rows;
    }

    private function excelColumnIndex(string $column): int
    {
        $index = 0;
        foreach (str_split($column) as $char) {
            $index = $index * 26 + ord(strtoupper($char)) - 64;
        }

        return max(0, $index - 1);
    }
}
