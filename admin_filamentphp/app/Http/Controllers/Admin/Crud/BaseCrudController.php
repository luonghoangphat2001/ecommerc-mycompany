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
        return $data;
    }

    protected function afterSave(Model $record, Request $request): void
    {
    }

    protected function formData(?Model $record = null): array
    {
        return [];
    }

    protected function canCreate(): bool
    {
        return true;
    }

    protected function canEdit(): bool
    {
        return true;
    }

    protected function canDelete(): bool
    {
        return true;
    }

    protected function headerActions(): array
    {
        return [];
    }

    public function index(Request $request): View
    {
        $modelClass = $this->modelClass();
        $query = $modelClass::query();
        $query = $this->indexQuery($query, $request);
        $this->applySearch($query, $request);

        return view('admin.crud.index', [
            'title' => $this->title(),
            'items' => $query->latest('id')->paginate(15)->withQueryString(),
            'fields' => $this->visibleFields('index'),
            'routePrefix' => $this->routePrefix(),
            'canCreate' => $this->canCreate(),
            'canEdit' => $this->canEdit(),
            'canDelete' => $this->canDelete(),
            'canImportExport' => true,
            'headerActions' => $this->headerActions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.crud.form', [
            'title' => 'Tạo mới - ' . $this->title(),
            'record' => null,
            'fields' => $this->visibleFields('form'),
            'routePrefix' => $this->routePrefix(),
            'formData' => $this->formData(),
        ]);
    }

    public function show(int $id): View
    {
        $modelClass = $this->modelClass();
        $record = $modelClass::findOrFail($id);

        return view('admin.crud.show', [
            'title' => 'Chi tiết - ' . $this->title(),
            'record' => $record,
            'fields' => $this->visibleFields('show'),
            'routePrefix' => $this->routePrefix(),
            'canEdit' => $this->canEdit(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data = $this->mutateData($data);
        $modelClass = $this->modelClass();
        $record = $modelClass::create($data);
        $this->afterSave($record, $request);

        return redirect()->route($this->routePrefix() . '.index')->with('status', 'Tạo mới thành công');
    }

    public function edit(int $id): View
    {
        $modelClass = $this->modelClass();
        $record = $modelClass::findOrFail($id);

        return view('admin.crud.form', [
            'title' => 'Chỉnh sửa - ' . $this->title(),
            'record' => $record,
            'fields' => $this->visibleFields('form'),
            'routePrefix' => $this->routePrefix(),
            'formData' => $this->formData($record),
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

        return redirect()->route($this->routePrefix() . '.index')->with('status', 'Cập nhật thành công');
    }

    public function destroy(int $id): RedirectResponse
    {
        $modelClass = $this->modelClass();
        $record = $modelClass::findOrFail($id);
        $record->delete();

        return redirect()->route($this->routePrefix() . '.index')->with('status', 'Xóa thành công');
    }

    public function export(Request $request): StreamedResponse
    {
        $modelClass = $this->modelClass();
        $query = $this->indexQuery($modelClass::query(), $request);
        $this->applySearch($query, $request);

        $fields = $this->visibleFields('export');
        $filename = Str::slug($this->title()) . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query, $fields): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_merge(['id'], array_keys($fields)));

            $query->latest('id')->chunk(200, function ($records) use ($handle, $fields): void {
                foreach ($records as $record) {
                    $row = [$record->id];
                    foreach ($fields as $name => $field) {
                        $row[] = AdminValue::format(data_get($record, $name), true);
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
            return back()->with('status', 'Không đọc được file import');
        }

        $headers = fgetcsv($handle);
        if (! is_array($headers) || $headers === []) {
            fclose($handle);
            return back()->with('status', 'File import không có header');
        }

        $headers = array_map(fn ($header) => trim((string) $header), $headers);
        $allowed = $this->importableColumns();
        $created = 0;
        $skipped = 0;
        $modelClass = $this->modelClass();

        while (($row = fgetcsv($handle)) !== false) {
            $payload = [];
            foreach ($headers as $index => $header) {
                if (! in_array($header, $allowed, true)) {
                    continue;
                }
                $payload[$header] = $row[$index] ?? null;
            }

            $payload = array_filter($payload, fn ($value) => $value !== '');
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
            ->with('status', "Import hoàn tất: {$created} dòng thành công, {$skipped} dòng bỏ qua");
    }

    protected function rules(?int $id = null): array
    {
        $rules = [];
        foreach ($this->visibleFields('form') as $name => $field) {
            $rules[$name] = $field['rules'] ?? ['nullable'];
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

        return array_values(array_filter(array_keys($this->visibleFields('import')), function (string $column) use ($table): bool {
            $field = $this->fields()[$column] ?? [];

            return Schema::hasColumn($table, $column) || ($field['virtual'] ?? false) === true;
        }));
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
