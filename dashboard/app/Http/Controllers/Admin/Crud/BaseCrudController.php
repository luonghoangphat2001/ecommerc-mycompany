<?php

namespace App\Http\Controllers\Admin\Crud;

use App\Http\Controllers\Controller;
use App\Support\AdminValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class BaseCrudController extends Controller
{
    abstract protected function modelClass(): string;
    abstract protected function title(): string;
    abstract protected function routePrefix(): string;
    abstract protected function fields(): array;

    protected function searchable(): array
    {
        return ['id'];
    }

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        return $query;
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        foreach ($this->visibleFields('form') as $name => $field) {
            if (($field['type'] ?? '') === 'image' && request()->hasFile($name)) {
                $data[$name] = request()->file($name)->store('uploads', 'public');
            }
        }

        $existingTranslations = $this->extractTranslations($record);
        foreach ($this->translatableFields() as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            // If incoming value is a JSON string e.g. {"vi":"...", "en":"..."}, decode it
            if (is_string($data[$field]) && str_starts_with(trim($data[$field]), '{')) {
                $decoded = json_decode(trim($data[$field]), true);
                if (is_array($decoded) && (isset($decoded['vi']) || isset($decoded['en']))) {
                    $data[$field] = $decoded;
                }
            }

            $existing = array_filter($existingTranslations[$field] ?? [], fn ($v) => $v !== null && $v !== '');

            if (is_array($data[$field])) {
                $vi = trim((string) ($data[$field]['vi'] ?? ''));
                $en = trim((string) ($data[$field]['en'] ?? ''));

                if ($vi === '' && isset($existing['vi'])) {
                    $vi = (string) $existing['vi'];
                }
                if ($en === '' && isset($existing['en'])) {
                    $en = (string) $existing['en'];
                }
                if ($vi === '' && $en !== '') {
                    $vi = $en;
                }
                if ($en === '' && $vi !== '') {
                    $en = $vi;
                }

                $clean = array_filter(['vi' => $vi, 'en' => $en], fn ($v) => $v !== '');
                $data[$field] = !empty($clean) ? $clean : null;
            } elseif (is_string($data[$field]) && trim($data[$field]) !== '') {
                $val = trim($data[$field]);
                $locale = app()->getLocale() ?: 'vi';
                $merged = $existing;
                $merged[$locale] = $val;
                if (empty($merged['vi'])) {
                    $merged['vi'] = $val;
                }
                if (empty($merged['en'])) {
                    $merged['en'] = $val;
                }
                $data[$field] = $merged;
            }
        }

        return $data;
    }

    protected function translatableFields(): array
    {
        $modelClass = $this->modelClass();
        $model = new $modelClass();
        $translatable = method_exists($model, 'getTranslatableAttributes')
            ? $model->getTranslatableAttributes()
            : [];

        foreach ($this->fields() as $name => $field) {
            if (!empty($field['translatable']) && !in_array($name, $translatable, true)) {
                $translatable[] = $name;
            }
        }

        return array_values(array_unique($translatable));
    }

    protected function extractTranslations(?Model $record = null): array
    {
        $result = [];
        foreach ($this->translatableFields() as $field) {
            $vi = '';
            $en = '';

            if ($record) {
                if (method_exists($record, 'getTranslations') && method_exists($record, 'isTranslatableAttribute') && $record->isTranslatableAttribute($field)) {
                    $translations = $record->getTranslations($field);
                    $vi = (string) ($translations['vi'] ?? '');
                    $en = (string) ($translations['en'] ?? '');
                } else {
                    $raw = $record->{$field} ?? null;
                    if (is_array($raw)) {
                        $vi = (string) ($raw['vi'] ?? '');
                        $en = (string) ($raw['en'] ?? '');
                    } elseif (is_string($raw)) {
                        $decoded = json_decode($raw, true);
                        if (is_array($decoded)) {
                            $vi = (string) ($decoded['vi'] ?? '');
                            $en = (string) ($decoded['en'] ?? '');
                        } else {
                            $vi = $raw;
                            $en = $raw;
                        }
                    }
                }
            }

            $result[$field] = [
                'vi' => $vi,
                'en' => $en,
            ];
        }

        return $result;
    }

    protected function flushStorefrontCaches(): void
    {
        foreach (['vi', 'en'] as $loc) {
            \Illuminate\Support\Facades\Cache::forget("storefront_settings_v1_{$loc}");
            \Illuminate\Support\Facades\Cache::forget("storefront_settings_v2_{$loc}");
        }
        \Illuminate\Support\Facades\Cache::forget('all_menus');
    }

    protected function afterSave(Model $record, Request $request): void
    {
    }

    protected function formData(?Model $record = null): array
    {
        return [];
    }

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check() && auth()->user()->hasRole('super_admin')) {
                return $next($request);
            }

            $module = str_replace('admin.', '', $this->routePrefix());
            $method = $request->route()->getActionMethod();
            
            $actionMap = [
                'index' => 'view',
                'show' => 'view',
                'export' => 'view',
                'create' => 'create',
                'store' => 'create',
                'import' => 'create',
                'edit' => 'update',
                'update' => 'update',
                'reorder' => 'update',
                'destroy' => 'delete',
                'process' => 'update',
                'updateStatus' => 'update',
                'storePayment' => 'update',
                'storeRefund' => 'update',
                'syncFromFiles' => 'update',
                'updateGroup' => 'update',
            ];
            
            $action = $actionMap[$method] ?? null;
            if ($action) {
                $permission = $action . '_' . $module;
                try {
                    if (!auth()->user()->hasPermissionTo($permission)) {
                        abort(403, 'Unauthorized action. Missing permission: ' . $permission);
                    }
                } catch (\Exception $e) {
                    abort(403, 'Unauthorized action. Missing permission: ' . $permission);
                }
            }

            return $next($request);
        });
    }

    protected function hasPermission(string $action): bool
    {
        if (auth()->check() && auth()->user()->hasRole('super_admin')) {
            return true;
        }
        $module = str_replace('admin.', '', $this->routePrefix());
        try {
            return auth()->check() && auth()->user()->hasPermissionTo($action . '_' . $module);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function canCreate(): bool
    {
        return $this->hasPermission('create');
    }

    protected function canEdit(): bool
    {
        return $this->hasPermission('update');
    }

    protected function canDelete(): bool
    {
        return $this->hasPermission('delete');
    }

    protected function headerActions(): array
    {
        return [];
    }

    protected function filters(): array
    {
        return [];
    }

    protected function canImportExport(): bool
    {
        return $this->hasPermission('create');
    }

    protected function tabs(): array
    {
        return [];
    }

    protected function activeTab(): ?string
    {
        return null;
    }

    public function index(Request $request): View
    {
        $modelClass = $this->modelClass();
        $query = $modelClass::query();
        $query = $this->indexQuery($query, $request);
        $this->applySearch($query, $request);

        return view('admin.crud.index', [
            'title' => __($this->title()),
            'items' => $query->latest('id')->paginate(15)->withQueryString(),
            'fields' => $this->visibleFields('index'),
            'routePrefix' => $this->routePrefix(),
            'canCreate' => $this->canCreate(),
            'canEdit' => $this->canEdit(),
            'canDelete' => $this->canDelete(),
            'canImportExport' => $this->canImportExport(),
            'headerActions' => $this->headerActions(),
            'filters' => $this->filters(),
            'tabs' => $this->tabs(),
            'activeTab' => $this->activeTab(),
        ]);
    }

    protected function formView(): string
    {
        return 'admin.crud.form';
    }

    public function create(): View
    {
        return view($this->formView(), [
            'title' => __('admin.actions.create') . ' - ' . __($this->title()),
            'record' => null,
            'fields' => $this->visibleFields('form'),
            'routePrefix' => $this->routePrefix(),
            'formData' => $this->formData(),
            'translatableFields' => $this->translatableFields(),
            'translations' => $this->extractTranslations(null),
            'tabs' => $this->tabs(),
            'activeTab' => $this->activeTab(),
        ]);
    }

    public function show(int $id): View
    {
        $modelClass = $this->modelClass();
        $record = $modelClass::findOrFail($id);
        
        $visibleFields = $this->visibleFields('show');
        $groups = method_exists($this, 'showGroups') ? $this->showGroups() : [];
        if (empty($groups)) {
            $groups = [
                'general' => [
                    'label' => __('admin.common.general_info'),
                    'fields' => array_keys($visibleFields),
                ]
            ];
        }

        return view('admin.crud.show', [
            'title' => __('admin.actions.view') . ' - ' . __($this->title()),
            'record' => $record,
            'fields' => $visibleFields,
            'groups' => $groups,
            'routePrefix' => $this->routePrefix(),
            'canEdit' => $this->canEdit(),
            'translatableFields' => $this->translatableFields(),
            'translations' => $this->extractTranslations($record),
            'tabs' => $this->tabs(),
            'activeTab' => $this->activeTab(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data = $this->mutateData($data);
        $modelClass = $this->modelClass();
        $record = $modelClass::create($data);
        $this->afterSave($record, $request);
        $this->flushStorefrontCaches();

        return redirect()->route($this->routePrefix() . '.index')->with('status', __('admin.messages.created'));
    }

    public function edit(int $id): View
    {
        $modelClass = $this->modelClass();
        $record = $modelClass::findOrFail($id);

        return view($this->formView(), [
            'title' => __('admin.actions.edit') . ' - ' . __($this->title()),
            'record' => $record,
            'fields' => $this->visibleFields('form'),
            'routePrefix' => $this->routePrefix(),
            'formData' => $this->formData($record),
            'translatableFields' => $this->translatableFields(),
            'translations' => $this->extractTranslations($record),
            'tabs' => $this->tabs(),
            'activeTab' => $this->activeTab(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $modelClass = $this->modelClass();
        $record = $modelClass::findOrFail($id);
        $data = $request->validate($this->rules($id));
        $data = $this->mutateData($data, $record);
        $record->update($data);
        $this->afterSave($record, $request);
        $this->flushStorefrontCaches();

        return redirect()->route($this->routePrefix() . '.index')->with('status', __('admin.messages.updated'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $modelClass = $this->modelClass();
        $record = $modelClass::findOrFail($id);
        $record->delete();
        $this->flushStorefrontCaches();

        return redirect()->route($this->routePrefix() . '.index')->with('status', __('admin.messages.deleted'));
    }

    public function reorder(Request $request)
    {
        $tree = $request->input('tree', []);
        if (empty($tree)) {
            return response()->json(['success' => true]);
        }

        $modelClass = $this->modelClass();
        $hasParentId = \Illuminate\Support\Facades\Schema::hasColumn((new $modelClass)->getTable(), 'parent_id');
        
        $this->updateTreeOrder($tree, null, $modelClass, $hasParentId);
        $this->flushStorefrontCaches();

        return response()->json(['success' => true]);
    }

    protected function updateTreeOrder(array $tree, ?int $parentId, string $modelClass, bool $hasParentId)
    {
        foreach ($tree as $index => $item) {
            $record = $modelClass::find($item['id']);
            if ($record) {
                if ($hasParentId) {
                    $record->parent_id = $parentId;
                }
                $record->order_column = $index;
                $record->save();
            }

            if (isset($item['children']) && is_array($item['children'])) {
                $this->updateTreeOrder($item['children'], $item['id'], $modelClass, $hasParentId);
            }
        }
    }

    public function export(Request $request): StreamedResponse
    {
        $modelClass = $this->modelClass();
        $query = $this->indexQuery($modelClass::query(), $request);
        $this->applySearch($query, $request);

        $fields = $this->visibleFields('export');
        $translatable = $this->translatableFields();
        $filename = Str::slug($this->title()) . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query, $fields, $translatable): void {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

            $headers = ['id'];
            foreach ($fields as $name => $field) {
                if (in_array($name, $translatable, true)) {
                    $headers[] = "{$name}_vi";
                    $headers[] = "{$name}_en";
                } else {
                    $headers[] = $name;
                }
            }
            fputcsv($handle, $headers);

            $query->latest('id')->chunk(200, function ($records) use ($handle, $fields, $translatable): void {
                foreach ($records as $record) {
                    $row = [$record->id];
                    foreach ($fields as $name => $field) {
                        if (in_array($name, $translatable, true)) {
                            if (method_exists($record, 'getTranslation')) {
                                $row[] = $record->getTranslation($name, 'vi', false) ?: '';
                                $row[] = $record->getTranslation($name, 'en', false) ?: '';
                            } elseif (is_array($record->{$name})) {
                                $row[] = $record->{$name}['vi'] ?? '';
                                $row[] = $record->{$name}['en'] ?? '';
                            } else {
                                $row[] = (string) $record->{$name};
                                $row[] = (string) $record->{$name};
                            }
                        } else {
                            $row[] = AdminValue::format(data_get($record, $name), true);
                        }
                    }
                    fputcsv($handle, $row);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return back()->with('status', __('admin.messages.import_failed'));
        }

        $headers = fgetcsv($handle);
        if (! is_array($headers) || $headers === []) {
            fclose($handle);
            return back()->with('status', __('admin.messages.import_missing_header'));
        }

        $headers = array_map(fn ($header) => trim((string) $header), $headers);
        $allowed = $this->importableColumns();
        $translatable = $this->translatableFields();
        $created = 0;
        $skipped = 0;
        $modelClass = $this->modelClass();

        while (($row = fgetcsv($handle)) !== false) {
            $payload = [];
            foreach ($headers as $index => $header) {
                if (! in_array($header, $allowed, true)) {
                    continue;
                }
                $val = $row[$index] ?? null;

                $isLangSuffix = false;
                foreach ($translatable as $transField) {
                    if ($header === "{$transField}_vi" || $header === "{$transField}[vi]") {
                        if (!isset($payload[$transField]) || !is_array($payload[$transField])) {
                            $payload[$transField] = is_array($payload[$transField] ?? null) ? $payload[$transField] : [];
                        }
                        $payload[$transField]['vi'] = $val;
                        $isLangSuffix = true;
                        break;
                    } elseif ($header === "{$transField}_en" || $header === "{$transField}[en]") {
                        if (!isset($payload[$transField]) || !is_array($payload[$transField])) {
                            $payload[$transField] = is_array($payload[$transField] ?? null) ? $payload[$transField] : [];
                        }
                        $payload[$transField]['en'] = $val;
                        $isLangSuffix = true;
                        break;
                    }
                }

                if (!$isLangSuffix) {
                    $payload[$header] = $val;
                }
            }

            $payload = array_filter($payload, fn ($value) => $value !== '' && $value !== null);
            if ($payload === []) {
                $skipped++;
                continue;
            }

            try {
                $modelClass::create($this->mutateData($payload));
                $created++;
            } catch (\Throwable) {
                $skipped++;
            }
        }

        fclose($handle);

        return redirect()->route($this->routePrefix() . '.index')
            ->with('status', __('admin.messages.import_completed', ['created' => $created, 'skipped' => $skipped]));
    }

    protected function rules(?int $id = null): array
    {
        $rules = [];
        $translatable = $this->translatableFields();

        foreach ($this->visibleFields('form') as $name => $field) {
            $fieldRules = $field['rules'] ?? ['nullable'];
            if (in_array($name, $translatable, true) && is_array(request()->input($name))) {
                $isRequired = in_array('required', (array) $fieldRules, true);
                $rules[$name] = array_values(array_filter([
                    $isRequired ? 'required' : 'nullable',
                    'array',
                    $isRequired ? function (string $attribute, mixed $value, \Closure $fail) {
                        $vi = trim((string) ($value['vi'] ?? ''));
                        $en = trim((string) ($value['en'] ?? ''));
                        if ($vi === '' && $en === '') {
                            $fail(__('validation.required', ['attribute' => $attribute]));
                        }
                    } : null,
                ]));
                $rules["{$name}.vi"] = ['nullable', 'string'];
                $rules["{$name}.en"] = ['nullable', 'string'];
            } else {
                $rules[$name] = $fieldRules;
            }
        }

        return $rules;
    }

    protected function visibleFields(string $context): array
    {
        return array_filter($this->fields(), function (array $field) use ($context): bool {
            $type = $field['type'] ?? 'text';

            if (($field['hidden'] ?? false) === true) {
                return false;
            }

            if ($type === 'password' && $context !== 'form') {
                return false;
            }

            return match ($context) {
                'index' => ! (($field['formOnly'] ?? false) || ($field['hideOnIndex'] ?? false)),
                'show' => ! (($field['formOnly'] ?? false) || ($field['hideOnShow'] ?? false)),
                'form' => ! (($field['tableOnly'] ?? false) || ($field['detailOnly'] ?? false) || ($field['hideOnForm'] ?? false)),
                'export' => ! (($field['formOnly'] ?? false) || ($field['hideOnExport'] ?? false) || in_array($type, ['password'], true)),
                'import' => ! (($field['tableOnly'] ?? false) || ($field['hideOnImport'] ?? false) || in_array($type, ['password', 'multiselect', 'checkboxgroup'], true)),
                default => true,
            };
        });
    }

    protected function importableColumns(): array
    {
        $modelClass = $this->modelClass();
        $table = (new $modelClass())->getTable();
        $translatable = $this->translatableFields();

        $columns = array_values(array_filter(array_keys($this->visibleFields('import')), function (string $column) use ($table): bool {
            $field = $this->fields()[$column] ?? [];

            return Schema::hasColumn($table, $column) || ($field['virtual'] ?? false) === true;
        }));

        foreach ($translatable as $field) {
            $columns[] = "{$field}_vi";
            $columns[] = "{$field}_en";
            $columns[] = "{$field}[vi]";
            $columns[] = "{$field}[en]";
        }

        return array_values(array_unique($columns));
    }

    protected function applySearch(Builder $query, Request $request): void
    {
        if (! $search = trim((string) $request->query('q', ''))) {
            return;
        }

        $modelClass = $this->modelClass();
        $table = (new $modelClass())->getTable();
        $columns = array_filter($this->searchable(), fn (string $column) => Schema::hasColumn($table, $column));

        if ($columns === []) {
            return;
        }

        $query->where(function (Builder $sub) use ($search, $columns): void {
            foreach ($columns as $column) {
                $sub->orWhere($column, 'like', '%' . $search . '%');
            }
        });
    }
}
