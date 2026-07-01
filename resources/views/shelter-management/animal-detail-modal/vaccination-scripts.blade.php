<script>
    function displayVaccinationRecords(vaccinations) {
        const container = document.getElementById('detailVaccinationsContainer');
        document.getElementById('detailVaccinationCount').textContent = vaccinations.length;

        let totalCost = 0;

        if (vaccinations.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-syringe text-4xl mb-2 opacity-50"></i>
                    <p>No vaccination records found</p>
                </div>
            `;
            return totalCost;
        }

        const vaccinationsHtml = vaccinations.map((record, index) => {
            const cost = parseFloat(record.cost) || 0;
            totalCost += cost;

            return `
                <div class="bg-gradient-to-br from-orange-50 to-yellow-50 rounded-lg p-4 border border-orange-200 hover:shadow-md mb-3">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="bg-orange-600 text-white rounded-full w-10 h-10 flex items-center justify-center">
                                <i class="fas fa-syringe"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">${record.vaccine_name || 'Vaccination'}</h4>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-calendar text-orange-600 mr-1"></i>
                                    ${record.date ? new Date(record.date).toLocaleDateString() : 'N/A'}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Cost</p>
                            <p class="font-bold text-orange-600 text-lg">RM ${cost.toFixed(2)}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        ${record.next_due_date ? `
                            <div class="bg-white rounded-lg p-2">
                                <p class="text-xs text-gray-600">Next Due</p>
                                <p class="font-semibold text-gray-800 text-sm">${new Date(record.next_due_date).toLocaleDateString()}</p>
                            </div>
                        ` : ''}
                        ${record.batch_number ? `
                            <div class="bg-white rounded-lg p-2">
                                <p class="text-xs text-gray-600">Batch Number</p>
                                <p class="font-semibold text-gray-800 text-sm">${record.batch_number}</p>
                            </div>
                        ` : ''}
                        ${record.administered_by ? `
                            <div class="bg-white rounded-lg p-2">
                                <p class="text-xs text-gray-600">Administered By</p>
                                <p class="font-semibold text-gray-800 text-sm">${record.administered_by}</p>
                            </div>
                        ` : ''}
                    </div>
                    ${record.notes ? `
                        <div class="bg-white rounded-lg p-3 mt-2">
                            <p class="text-xs text-gray-600 mb-1">Notes</p>
                            <p class="text-sm text-gray-800">${record.notes}</p>
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('');

        container.innerHTML = vaccinationsHtml;
        return totalCost;
    }

    function closeAnimalDetailModal() {
        document.getElementById('animalDetailModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentAnimalId = null;
        currentImages = [];
        currentImageIndex = 0;
    }
</script>
