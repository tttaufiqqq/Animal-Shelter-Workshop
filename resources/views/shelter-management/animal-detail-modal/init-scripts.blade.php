<script>
    let currentAnimalId = null;
    let currentImages = [];
    let currentImageIndex = 0;

    function openAnimalDetailModal(animalId) {
        console.log('Opening animal modal for ID:', animalId);

        currentAnimalId = animalId;
        document.getElementById('animalDetailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        document.getElementById('animalDetailLoading').classList.remove('hidden');
        document.getElementById('animalDetailContent').classList.add('hidden');

        const url = `/shelter-management/animals/${animalId}/details`;
        console.log('Fetching from URL:', url);

        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                displayAnimalDetails(data);
            })
            .catch(error => {
                console.error('Error fetching animal details:', error);
                console.error('Error details:', error.message);
                alert(`Failed to load animal details: ${error.message}`);
                closeAnimalDetailModal();
            });
    }

    function displayAnimalDetails(data) {
        document.getElementById('animalDetailLoading').classList.add('hidden');
        document.getElementById('animalDetailContent').classList.remove('hidden');

        displayImages(data.images || []);

        document.getElementById('animalDetailName').textContent = data.name;
        document.getElementById('animalDetailSubtitle').textContent = `${data.species || 'Unknown'}`;
        document.getElementById('detailAnimalName').textContent = data.name;

        const adoptionStatusEl = document.getElementById('detailAdoptionStatus');
        const statusColors = {
            'available': 'text-green-600',
            'adopted': 'text-blue-600',
            'pending': 'text-orange-600',
            'not_available': 'text-red-600'
        };
        const statusText = data.adoption_status
            ? data.adoption_status.replace('_', ' ').charAt(0).toUpperCase() + data.adoption_status.slice(1).replace('_', ' ')
            : 'Unknown';
        adoptionStatusEl.textContent = statusText;
        adoptionStatusEl.className = `font-bold text-lg ${statusColors[data.adoption_status] || 'text-gray-600'}`;

        document.getElementById('detailHealthDetails').textContent = data.health_details || 'No health details available.';

        const medicalTotal = displayMedicalRecords(data.medicals || []);
        const vaccinationTotal = displayVaccinationRecords(data.vaccinations || []);

        document.getElementById('totalMedicalCost').textContent = `RM ${medicalTotal.toFixed(2)}`;
        document.getElementById('totalVaccinationCost').textContent = `RM ${vaccinationTotal.toFixed(2)}`;
        document.getElementById('totalAllCosts').textContent = `RM ${(medicalTotal + vaccinationTotal).toFixed(2)}`;
    }
</script>
