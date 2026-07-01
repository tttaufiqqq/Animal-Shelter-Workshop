<script>
    function displayCompatibilityResult(compatibility) {
        const container = document.getElementById('compatibilityStatus');
        const compatSection = document.getElementById('compatibilitySection');

        const statusConfig = {
            'excellent': {
                bg: 'from-green-50 to-emerald-50',
                border: 'border-green-300',
                icon: 'check-circle',
                iconColor: 'text-green-600',
                containerBg: 'bg-green-600'
            },
            'suitable': {
                bg: 'from-green-50 to-emerald-50',
                border: 'border-green-300',
                icon: 'check-circle',
                iconColor: 'text-green-600',
                containerBg: 'bg-green-600'
            },
            'warning': {
                bg: 'from-orange-50 to-amber-50',
                border: 'border-orange-300',
                icon: 'exclamation-triangle',
                iconColor: 'text-orange-600',
                containerBg: 'bg-orange-600'
            },
            'unsuitable': {
                bg: 'from-red-50 to-pink-50',
                border: 'border-red-300',
                icon: 'times-circle',
                iconColor: 'text-red-600',
                containerBg: 'bg-red-600'
            },
            'danger': {
                bg: 'from-red-50 to-pink-50',
                border: 'border-red-400',
                icon: 'skull-crossbones',
                iconColor: 'text-red-700',
                containerBg: 'bg-red-700'
            }
        };

        const config = statusConfig[compatibility.status];

        compatSection.className = `mx-6 mt-6 bg-gradient-to-br ${config.bg} rounded-xl p-5 border-2 ${config.border}`;

        const iconContainer = compatSection.querySelector('.bg-green-600, .bg-orange-600, .bg-red-600, .bg-red-700');
        if (iconContainer) {
            iconContainer.className = `${config.containerBg} text-white rounded-full p-3`;
            const icon = iconContainer.querySelector('i');
            if (icon) {
                icon.className = `fas fa-${config.icon} text-xl`;
            }
        }

        container.innerHTML = compatibility.messages.map(msg => {
            let bgColor = 'bg-white';
            let borderColor = 'border-gray-200';

            if (msg.includes('[✗]') || msg.includes('[⚠] DANGER')) {
                bgColor = 'bg-red-100';
                borderColor = 'border-red-300';
            } else if (msg.includes('[!]')) {
                bgColor = 'bg-orange-100';
                borderColor = 'border-orange-300';
            } else if (msg.includes('[✓]')) {
                bgColor = 'bg-green-100';
                borderColor = 'border-green-300';
            }

            return `
                <div class="${bgColor} rounded-lg p-3 border ${borderColor} mb-2">
                    <p class="text-sm font-semibold text-gray-800">${msg}</p>
                </div>
            `;
        }).join('');
    }
</script>
