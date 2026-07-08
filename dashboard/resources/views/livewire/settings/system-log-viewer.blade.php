<div class="space-y-4">
    <div class="flex items-center space-x-4">
        <select wire:model.live="selectedFile" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700">
            <option value="">{{ __('admin.logs.select_file') }}</option>
            @foreach($files as $file)
                <option value="{{ $file }}">{{ $file }}</option>
            @endforeach
        </select>
        
        <button wire:click="mount" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            {{ __('admin.refresh') }}
        </button>
    </div>

    <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-4 font-mono text-xs overflow-auto max-h-[500px] border border-gray-200 dark:border-gray-700">
        @if($content)
            <pre class="whitespace-pre-wrap">{{ $content }}</pre>
        @else
            <div class="text-center py-10 text-gray-500 italic">
                {{ __('admin.logs.empty_state') }}
            </div>
        @endif
    </div>
</div>
