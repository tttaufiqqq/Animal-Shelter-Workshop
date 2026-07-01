<script>
    function displayMedicalRecords(medicals) {
        const container = document.getElementById('detailMedicalsContainer');
        document.getElementById('detailMedicalCount').textContent = medicals.length;

        let totalCost = 0;

        if (medicals.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-stethoscope text-4xl mb-2 opacity-50"></i>
                    <p>No medical records found</p>
                </div>
            `;
            return totalCost;
        }

        const medicalsHtml = medicals.map((record, index) => {
            const cost = parseFloat(record.cost) || 0;
            totalCost += cost;

            return `
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200 hover:shadow-md mb-3">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="bg-purple-600 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold">
                                ${index + 1}
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">${record.treatment || 'Medical Record'}</h4>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-calendar text-purple-600 mr-1"></i>
                                    ${record.date ? new Date(record.date).toLocaleDateString() : 'N/A'}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Cost</p>
                            <p class="font-bold text-purple-600 text-lg">RM ${cost.toFixed(2)}</p>
                        </div>
                    </div>
                    ${record.diagnosis ? `
                        <div class="bg-white rounded-lg p-3 mb-2">
                            <p class="text-xs text-gray-600 mb-1">Diagnosis</p>
                            <p class="text-sm text-gray-800">${record.diagnosis}</p>
                        </div>
                    ` : ''}
                    ${record.notes ? `
                        <div class="bg-white rounded-lg p-3 mb-2">
                            <p class="text-xs text-gray-600 mb-1">Notes</p>
                            <p class="text-sm text-gray-800">${record.notes}</p>
                        </div>
                    ` : ''}
                    ${record.vet_name ? `
                        <div class="mt-2 text-sm text-gray-600">
                            <i class="fas fa-user-md text-purple-600 mr-1"></i>
                            Dr. ${record.vet_name}
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('');

        container.innerHTML = medicalsHtml;
        return totalCost;
    }
</script>
