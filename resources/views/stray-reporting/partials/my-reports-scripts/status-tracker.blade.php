<script>
    // Generate Status Tracker HTML
    function generateStatusTracker(currentStatus) {
        const statuses = [
            {
                name: 'Pending',
                label: 'Report Submitted',
                icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
            },
            {
                name: 'Assigned',
                label: 'Caretaker Assigned',
                icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'
            },
            {
                name: 'In Progress',
                label: 'Rescue In Progress',
                icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
            },
            {
                name: 'Completed',
                label: 'Rescue Completed',
                icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            }
        ];

        const currentIndex = statuses.findIndex(s => s.name === currentStatus);

        if (currentStatus === 'Rejected') {
            return `
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 border-2 border-red-200">
                    <div class="flex items-center justify-center space-x-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-red-800">Report Rejected</h4>
                            <p class="text-sm text-red-600">This report has been marked as invalid or spam</p>
                        </div>
                    </div>
                </div>
            `;
        }

        let html = '<div class="bg-white rounded-xl p-6 border border-gray-200 overflow-hidden">';
        html += '<div class="flex items-center justify-between relative" style="isolation: isolate;">';

        statuses.forEach((status, index) => {
            const isCompleted = index < currentIndex;
            const isCurrent = index === currentIndex;

            html += `
                <div class="flex flex-col items-center relative" style="flex: 0 0 auto; z-index: 2;">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center mb-3 transition-all duration-300
                        ${isCompleted ? 'bg-gradient-to-br from-purple-500 to-purple-600 shadow-lg' :
                          isCurrent ? 'bg-gradient-to-br from-purple-500 to-purple-600 shadow-xl ring-4 ring-purple-200 animate-pulse' :
                          'bg-gray-200'}">
                        <div class="${isCompleted || isCurrent ? 'text-white' : 'text-gray-400'}">
                            ${isCompleted ?
                                '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>' :
                                status.icon}
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-semibold ${isCompleted || isCurrent ? 'text-purple-700' : 'text-gray-500'} whitespace-nowrap">
                            ${status.label}
                        </p>
                        ${isCurrent ? '<p class="text-xs text-purple-600 font-bold mt-1">Current Status</p>' : ''}
                    </div>
                </div>
            `;

            if (index < statuses.length - 1) {
                html += `
                    <div class="flex-1 h-1 mx-2 rounded-full transition-all duration-500"
                         style="margin-top: -35px; z-index: 1; ${index < currentIndex ? 'background: linear-gradient(to right, #9333ea, #7e22ce);' : 'background: #e5e7eb;'}">
                    </div>
                `;
            }
        });

        html += '</div></div>';
        return html;
    }
</script>
