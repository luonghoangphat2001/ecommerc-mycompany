<?php

namespace App\Http\Controllers\Admin\Crud;

use App\Ecommerce\Media\Services\MediaUrlService;
use App\Http\Controllers\Admin\Traits\HasRecursiveTreeReorder;
use App\Http\Controllers\Controller;
use App\Support\Admin;
use App\Support\Infrastructure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class BaseCrudController extends Controller
{
    use HasRecursiveTreeReorder;

    protected function isEnglishLocale(): bool
    {
        return app()->getLocale() === 'en';
    }

    abstract protected function modelClass(): string;

    abstract protected function title(): string;

    abstract protected function routePrefix(): string;

    abstract protected function fields(): array;

    protected function searchable(): array
    {
        return ['id'];
    }

    protected function findRecord(int $id): Model
    {
        $modelClass = $this->modelClass();
        $query = $modelClass::query();
        if (method_exists($modelClass, 'brand')) {
            $query->with('brand');
        }

        return $query->findOrFail($id);
    }

    protected function brandLabel(Model $record, string $name): ?string
    {
        return in_array($name, ['brand', 'shop_brand_id'], true) && method_exists($record, 'brand')
            ? $record->brand?->name
            : null;
    }

    protected function brandLabels(Model $record, array $fields): array
    {
        $labels = [];
        foreach ($fields as $name => $field) {
            $labels[$name] = $this->brandLabel($record, $name);
        }

        return $labels;
    }

    protected function brandLabelsForItems(iterable $items, array $fields): array
    {
        $labels = [];
        foreach ($items as $item) {
            $labels[$item->getKey()] = $this->brandLabels($item, $fields);
        }

        return $labels;
    }

    protected function redirectToIndex(string $message): RedirectResponse
    {
        return redirect()
            ->route($this->routePrefix() . '.index')
            ->with('status', __('admin.messages.' . $message));
    }

    protected function imageUrls(Model $record, array $fields): array
    {
        $service = app(MediaUrlService::class);
        $urls = [];

        foreach ($fields as $name => $field) {
            if (($field['type'] ?? null) === 'image') {
                $urls[$name] = $service->url(data_get($record, $name));
            }
        }

        return $urls;
    }

    protected function imageUrlsForItems(iterable $items, array $fields): array
    {
        $urls = [];

        foreach ($items as $item) {
            $urls[$item->getKey()] = $this->imageUrls($item, $fields);
        }

        return $urls;
    }

    protected function crudFieldGroups(array $fields, bool $show = false, ?Model $record = null, array $formData = []): array
    {
        $groups = $this->groupFieldsByPurpose($fields, $show);
        $groups['mainFields'] = $this->prioritizeFields($groups['mainFields'], ['title', 'name', 'slug', 'sku']);
        $groups['pricingFields'] = $this->prioritizeFields($groups['pricingFields'], ['price', 'old_price', 'cost', 'sale_price', 'sale_start_date', 'sale_end_date', 'qty', 'security_stock', 'backorder', 'requires_shipping']);
        $groups = $this->addFieldLayoutHints($groups);
        if (! $show) {
            $groups = $this->prepareFormFieldValues($groups, $record, $formData);
        }
        $groups['hasSidebar'] = $this->hasSidebarFields($groups);

        return $groups;
    }

    protected function prepareFormFieldValues(array $groups, ?Model $record, array $formData): array
    {
        $oldInput = session()->getOldInput();

        foreach ($groups as &$fields) {
            foreach ($fields as $name => &$field) {
                $value = $oldInput[$name] ?? $formData[$name] ?? data_get($record, $name);
                $type = $field['type'] ?? 'text';
                $field['displayValue'] = $this->formatFormValue($value, $type);
                $field['selected'] = $this->selectedFormValues($name, $type, $value, $record, $formData);
                $field['blocksData'] = $type === 'blocks' ? $this->decodeBlocks($value) : [];
                $field['fileName'] = is_string($value) ? basename($value) : null;
            }
            unset($field);
        }
        unset($fields);

        return $groups;
    }

    protected function formatFormValue(mixed $value, string $type): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $type === 'datetime-local' ? $value->format('Y-m-d\\TH:i') : $value->format('Y-m-d');
        }
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return $value;
    }

    protected function selectedFormValues(string $name, string $type, mixed $value, ?Model $record, array $formData): array
    {
        if ($type === 'multiselect' && ! isset(session()->getOldInput()[$name]) && ! isset($formData[$name]) && is_iterable(data_get($record, $name))) {
            return collect(data_get($record, $name))->pluck('name')->toArray();
        }

        return (array) $value;
    }

    protected function decodeBlocks(mixed $value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return is_array($value) ? $value : [];
    }

    protected function groupFieldsByPurpose(array $fields, bool $show): array
    {
        $metaKeys = $show
            ? ['is_visible', 'featured', 'type', 'status', 'is_approved', 'role', 'published_at', 'author_id', 'shop_brand_id', 'department_id', 'apply_tax', 'backorder', 'requires_shipping', 'is_active', 'is_urgent']
            : ['is_visible', 'status', 'post_type', 'type', 'featured', 'is_featured', 'published_at', 'author_id', 'shop_brand_id', 'department_id', 'apply_tax', 'backorder', 'requires_shipping', 'is_active', 'is_urgent'];
        $pricingKeys = ['price', 'old_price', 'sale_price', 'sale_start_date', 'sale_end_date', 'cost', 'qty', 'security_stock', 'stock_quantity', 'low_stock_threshold', 'amount', 'base_salary', 'allowance', 'tax', 'insurance', 'net_salary'];
        $groups = ['mainFields' => [], 'pricingFields' => [], 'seoFields' => [], 'sidebarMetaFields' => [], 'sidebarMediaFields' => [], 'sidebarTaxonomyFields' => []];

        foreach ($fields as $name => $field) {
            $groups[$this->fieldGroup($name, $field, $metaKeys, $pricingKeys)][$name] = $field;
        }

        return $groups;
    }

    protected function fieldGroup(string $name, array $field, array $metaKeys, array $pricingKeys): string
    {
        $type = $field['type'] ?? 'text';

        return match (true) {
            $type === 'image' => 'sidebarMediaFields',
            in_array($name, ['categories', 'tags'], true) || in_array($type, ['tree-select', 'tags-checkboxes'], true) => 'sidebarTaxonomyFields',
            in_array($name, ['seo_title', 'seo_description'], true) || Str::startsWith($name, 'seo_') => 'seoFields',
            in_array($name, $metaKeys, true) => 'sidebarMetaFields',
            in_array($name, $pricingKeys, true) => 'pricingFields',
            default => 'mainFields',
        };
    }

    protected function prioritizeFields(array $fields, array $priority): array
    {
        $ordered = [];
        foreach ($priority as $name) {
            if (isset($fields[$name])) {
                $ordered[$name] = $fields[$name];
                unset($fields[$name]);
            }
        }

        return $ordered + $fields;
    }

    protected function addFieldLayoutHints(array $groups): array
    {
        foreach ($groups as &$fields) {
            foreach ($fields as $name => &$field) {
                $type = $field['type'] ?? 'text';
                $field['type'] = $type;
                $field['isWide'] = in_array($type, ['textarea', 'editor', 'blocks'], true)
                    || in_array($name, ['content', 'description', 'blocks'], true)
                    || ($field['colspan'] ?? null) === 'full';
            }
            unset($field);
        }
        unset($fields);

        return $groups;
    }

    protected function hasSidebarFields(array $groups): bool
    {
        return (bool) ($groups['sidebarMetaFields'] || $groups['sidebarMediaFields'] || $groups['sidebarTaxonomyFields']);
    }

    protected function relatedViewData(?Model $record): array
    {
        $isProduct = str_contains($this->routePrefix(), 'products');
        $isPost = str_contains($this->routePrefix(), 'posts');
        $hasComments = $record && ($isProduct || $isPost) && method_exists($record, 'comments');

        return [
            'isProduct' => $isProduct,
            'hasCommentSection' => (bool) $hasComments,
            'comments' => $hasComments ? $record->comments()->with('author')->get() : collect(),
            'categories' => $record && method_exists($record, 'categories') ? $record->categories : collect(),
            'tags' => $record && method_exists($record, 'tags') ? $record->tags : collect(),
        ];
    }

    protected function redirectWithSuccess(string $message): RedirectResponse
    {
        return redirect()
            ->route($this->routePrefix() . '.index')
            ->with('success', $message);
    }

    protected function redirectBackWithError(\Throwable $exception): RedirectResponse
    {
        report($exception);

        return back()->withInput()->withErrors(['error' => $exception->getMessage()]);
    }

    protected function persistRecord(Request $request, ?Model $record = null): Model
    {
        $data = $request->validate($this->rules($record?->getKey()));
        $data = $this->mutateData($data, $record);

        if ($record) {
            $record->update($data);
        } else {
            $modelClass = $this->modelClass();
            $record = $modelClass::query()->create($data);
        }

        $this->afterSave($record, $request);
        $this->flushStorefrontCaches();

        return $record;
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
            if (! array_key_exists($field, $data)) {
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
                $data[$field] = ! empty($clean) ? $clean : null;
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
        $model = new $modelClass;
        $translatable = method_exists($model, 'getTranslatableAttributes')
            ? $model->getTranslatableAttributes()
            : [];

        foreach ($this->fields() as $name => $field) {
            if (! empty($field['translatable']) && ! in_array($name, $translatable, true)) {
                $translatable[] = $name;
            }
        }

        return array_values(array_unique($translatable));
    }

    protected function extractTranslations(?Model $record = null): array
    {
        $result = [];
        foreach ($this->translatableFields() as $field) {
            $result[$field] = $record ? $this->translationPair($record, $field) : ['vi' => '', 'en' => ''];
        }

        return $result;
    }

    protected function translationPair(Model $record, string $field): array
    {
        if (method_exists($record, 'getTranslations')
            && method_exists($record, 'isTranslatableAttribute')
            && $record->isTranslatableAttribute($field)) {
            $translations = $record->getTranslations($field);

            return [
                'vi' => (string) ($translations['vi'] ?? ''),
                'en' => (string) ($translations['en'] ?? ''),
            ];
        }

        $raw = $record->{$field} ?? null;
        if (is_array($raw)) {
            return [
                'vi' => (string) ($raw['vi'] ?? ''),
                'en' => (string) ($raw['en'] ?? ''),
            ];
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return [
                    'vi' => (string) ($decoded['vi'] ?? ''),
                    'en' => (string) ($decoded['en'] ?? ''),
                ];
            }

            return ['vi' => $raw, 'en' => $raw];
        }

        return ['vi' => '', 'en' => ''];
    }

    protected function flushStorefrontCaches(): void
    {
        Infrastructure::flushStorefrontCache();
    }

    protected function afterSave(Model $record, Request $request): void {}

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
                    if (! auth()->user()->hasPermissionTo($permission)) {
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

    protected function paginateIndex(Builder $query, int $perPage = 15): LengthAwarePaginator
    {
        return $query->latest('id')->paginate($perPage)->withQueryString();
    }

    public function index(Request $request): View | RedirectResponse
    {
        $modelClass = $this->modelClass();
        $query = $modelClass::query();
        if (method_exists($modelClass, 'brand')) {
            $query->with('brand');
        }
        $query = $this->indexQuery($query, $request);
        $this->applySearch($query, $request);
        $fields = $this->visibleFields('index');
        $items = $this->paginateIndex($query);

        return view('admin.crud.index', [
            'title' => __($this->title()),
            'items' => $items,
            'fields' => $fields,
            'imageUrls' => $this->imageUrlsForItems($items, $fields),
            'brandLabels' => $this->brandLabelsForItems($items, $fields),
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
        $fields = $this->visibleFields('form');
        $formData = $this->formData();

        return view($this->formView(), array_merge([
            'title' => __('admin.actions.create') . ' - ' . __($this->title()),
            'record' => null,
            'fields' => $fields,
            'imageUrls' => [],
            'routePrefix' => $this->routePrefix(),
            'formData' => $formData,
            'translatableFields' => $this->translatableFields(),
            'translations' => $this->extractTranslations(null),
            'tabs' => $this->tabs(),
            'activeTab' => $this->activeTab(),
        ], $this->crudFieldGroups($fields, false, null, $formData), $this->relatedViewData(null)));
    }

    public function show(int $id): View
    {
        $modelClass = $this->modelClass();
        $record = $this->findRecord($id);

        $visibleFields = $this->visibleFields('show');
        $groups = method_exists($this, 'showGroups') ? $this->showGroups() : [];
        if (empty($groups)) {
            $groups = [
                'general' => [
                    'label' => __('admin.common.general_info'),
                    'fields' => array_keys($visibleFields),
                ],
            ];
        }

        return view('admin.crud.show', array_merge([
            'title' => __('admin.actions.view') . ' - ' . __($this->title()),
            'record' => $record,
            'fields' => $visibleFields,
            'imageUrls' => $this->imageUrls($record, $visibleFields),
            'brandLabels' => $this->brandLabels($record, $visibleFields),
            'groups' => $groups,
            'routePrefix' => $this->routePrefix(),
            'canEdit' => $this->canEdit(),
            'translatableFields' => $this->translatableFields(),
            'translations' => $this->extractTranslations($record),
            'tabs' => $this->tabs(),
            'activeTab' => $this->activeTab(),
        ], $this->crudFieldGroups($visibleFields, true), $this->relatedViewData($record)));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->persistRecord($request);

        return $this->redirectToIndex('created');
    }

    public function edit(int $id): View
    {
        $modelClass = $this->modelClass();
        $record = $this->findRecord($id);

        $fields = $this->visibleFields('form');
        $formData = $this->formData($record);

        return view($this->formView(), array_merge([
            'title' => __('admin.actions.edit') . ' - ' . __($this->title()),
            'record' => $record,
            'fields' => $fields,
            'imageUrls' => $this->imageUrls($record, $fields),
            'routePrefix' => $this->routePrefix(),
            'formData' => $formData,
            'translatableFields' => $this->translatableFields(),
            'translations' => $this->extractTranslations($record),
            'tabs' => $this->tabs(),
            'activeTab' => $this->activeTab(),
        ], $this->crudFieldGroups($fields, false, $record, $formData), $this->relatedViewData($record)));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $record = $this->findRecord($id);
        $this->persistRecord($request, $record);

        return $this->redirectToIndex('updated');
    }

    public function destroy(int $id): RedirectResponse
    {
        $modelClass = $this->modelClass();
        $record = $this->findRecord($id);
        $record->delete();
        $this->flushStorefrontCaches();

        return $this->redirectToIndex('deleted');
    }

    public function reorder(Request $request)
    {
        $tree = $request->input('tree', []);
        if (empty($tree)) {
            return response()->json(['success' => true]);
        }

        $modelClass = $this->modelClass();
        $hasParentId = Schema::hasColumn((new $modelClass)->getTable(), 'parent_id');

        $this->walkTree($tree, null, function (array $item, int $index, ?int $parentId) use ($modelClass, $hasParentId): void {
            $record = $modelClass::query()->find($item['id'] ?? null);
            if (! $record) {
                return;
            }

            if ($hasParentId) {
                $record->parent_id = $parentId;
            }
            $record->order_column = $index;
            $record->save();
        });
        $this->flushStorefrontCaches();

        return response()->json(['success' => true]);
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
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel

            fputcsv($handle, $this->exportHeaders($fields, $translatable));

            $query->latest('id')->chunk(200, function ($records) use ($handle, $fields, $translatable): void {
                foreach ($records as $record) {
                    fputcsv($handle, $this->exportRow($record, $fields, $translatable));
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function exportHeaders(array $fields, array $translatable): array
    {
        $headers = ['id'];
        foreach ($fields as $name => $field) {
            if (in_array($name, $translatable, true)) {
                $headers[] = "{$name}_vi";
                $headers[] = "{$name}_en";
            } else {
                $headers[] = $name;
            }
        }

        return $headers;
    }

    protected function exportRow(Model $record, array $fields, array $translatable): array
    {
        $row = [$record->getKey()];
        foreach ($fields as $name => $field) {
            if (in_array($name, $translatable, true)) {
                $translation = $this->translationPair($record, $name);
                $row[] = $translation['vi'];
                $row[] = $translation['en'];
            } else {
                $row[] = Admin::format(data_get($record, $name), true);
            }
        }

        return $row;
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
            $payload = $this->buildImportPayload($headers, $row, $allowed, $translatable);
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

    protected function buildImportPayload(array $headers, array $row, array $allowed, array $translatable): array
    {
        $payload = [];

        foreach ($headers as $index => $header) {
            if (! in_array($header, $allowed, true)) {
                continue;
            }

            $translation = $this->importTranslationColumn($header, $translatable);
            if ($translation) {
                [$field, $locale] = $translation;
                $payload[$field] = is_array($payload[$field] ?? null) ? $payload[$field] : [];
                $payload[$field][$locale] = $row[$index] ?? null;

                continue;
            }

            $payload[$header] = $row[$index] ?? null;
        }

        return array_filter($payload, fn ($value) => $value !== '' && $value !== null);
    }

    protected function importTranslationColumn(string $header, array $translatable): ?array
    {
        foreach ($translatable as $field) {
            foreach (['vi', 'en'] as $locale) {
                if ($header === "{$field}_{$locale}" || $header === "{$field}[{$locale}]") {
                    return [$field, $locale];
                }
            }
        }

        return null;
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
        $table = (new $modelClass)->getTable();
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
        $table = (new $modelClass)->getTable();
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
