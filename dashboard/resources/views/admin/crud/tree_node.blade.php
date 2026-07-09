@if(count($nodes) > 0)
    <div style="padding-left: {{ $level > 0 ? '20px' : '0' }}; display: flex; flex-direction: column; gap: 8px; margin-top: {{ $level > 0 ? '8px' : '0' }};">
        @foreach($nodes as $node)
            @php($isChecked = in_array((string) $node->id, array_map('strval', (array) $selected), true) ? 'checked' : '')
            <div style="display: flex; flex-direction: column;">
                <label class="checkbox-item" style="margin-bottom: 0;">
                    <input type="checkbox" name="{{ $name }}[]" value="{{ $node->id }}" {{ $isChecked }}>
                    <span>{{ $node->name }}</span>
                </label>
                @if($node->children && count($node->children) > 0)
                    @include('admin.crud.tree_node', ['nodes' => $node->children, 'level' => $level + 1, 'name' => $name, 'selected' => $selected])
                @endif
            </div>
        @endforeach
    </div>
@endif
