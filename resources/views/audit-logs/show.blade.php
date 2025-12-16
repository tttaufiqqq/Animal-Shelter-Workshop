<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Log Details #' . $log->id) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Log Metadata -->
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Timestamp</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $log->created_at->format('Y-m-d H:i:s T') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">User</dt>
                            <dd class="mt-1">
                                <div class="text-sm font-medium text-gray-900">{{ $log->user_name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $log->user_email }}</div>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Action</dt>
                            <dd class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($log->action_type === 'created') bg-green-100 text-green-800
                                    @elseif($log->action_type === 'updated') bg-yellow-100 text-yellow-800
                                    @elseif($log->action_type === 'deleted') bg-red-100 text-red-800
                                    @elseif($log->action_type === 'login') bg-blue-100 text-blue-800
                                    @elseif($log->action_type === 'logout') bg-gray-100 text-gray-800
                                    @elseif($log->action_type === 'failed_login') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $log->action_label }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Model</dt>
                            <dd class="mt-1">
                                @if($log->auditable_type)
                                    <div class="text-sm text-gray-900">{{ $log->model_name }} #{{ $log->auditable_id }}</div>
                                    <div class="text-xs text-gray-500">Connection: {{ $log->auditable_connection }}</div>
                                @else
                                    <span class="text-sm text-gray-400">N/A</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $log->ip_address }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Request</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ $log->method }}</span>
                                <span class="ml-2">{{ $log->url }}</span>
                            </dd>
                        </div>
                    </dl>

                    <!-- Changed Fields -->
                    @if($log->changed_fields)
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Changed Fields</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Field
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Old Value
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                New Value
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($log->changed_fields as $field => $values)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $field }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                                    {{ $values['old'] ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                                    {{ $values['new'] ?? 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Metadata -->
                    @if($log->metadata)
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Metadata</h3>
                            <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif

                    <!-- User Agent -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">User Agent</h3>
                        <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg break-all">{{ $log->user_agent }}</p>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-8">
                        <a href="{{ route('audit-logs.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            &larr; Back to Audit Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
