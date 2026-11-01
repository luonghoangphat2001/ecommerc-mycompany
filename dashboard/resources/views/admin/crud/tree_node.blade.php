@if(count($nodes) > 0)
    <div style="padding-left: {{ $level > 0 ? '16px' : '0' }}; display: flex; flex-direction: column; gap: 6px; margin-top: {{ $level > 0 ? '6px' : '0' }}; {{ $level > 0 ? 'border-left: 1.5px solid #e2e8f0; margin-left: 4px;' : '' }}">
        @foreach($nodes as $node)
            <div style="display: flex; flex-direction: column;">
                <label class="checkbox-item" style="margin-bottom: 0; display: inline-flex; align-items: center; gap: 7px; font-size: 12.5px; color: #334155; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="{{ $name }}[]" value="{{ $node->id }}" @checked(in_array((string) $node->id, array_map('strval', (array) $selected), true))>
                    <span style="{{ $level === 0 ? 'font-weight: 600; color: #0f172a;' : 'font-weight: 400;' }}">{{ $node->name }}</span>
                </label>
                @if($node->children && count($node->children) > 0)
                    @include('admin.crud.tree_node', ['nodes' => $node->children, 'level' => $level + 1, 'name' => $name, 'selected' => $selected])
                @endif
            </div>
        @endforeach
    </div>
@endif
