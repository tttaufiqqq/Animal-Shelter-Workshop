<script>
    function displayCompatibilityAnalysis(animal, inventoryData) {
        document.getElementById('compatibilitySection').classList.remove('hidden');
        document.getElementById('noAnimalCompatSection').classList.add('hidden');

        document.getElementById('compatAnimalName').textContent = `${animal.name} (${animal.species})`;

        const healthEl = document.getElementById('compatHealthStatus');
        const healthColors = {
            'Healthy': 'text-green-600',
            'Sick': 'text-red-600',
            'Recovering': 'text-orange-600',
            'Critical': 'text-red-700'
        };
        healthEl.textContent = animal.health_status || 'Unknown';
        healthEl.className = `font-bold ${healthColors[animal.health_status] || 'text-gray-600'}`;

        const compatibility = analyzeCompatibility(animal, inventoryData);
        displayCompatibilityResult(compatibility);
    }

    function analyzeCompatibility(animal, inventory) {
        const itemName = inventory.item_name.toLowerCase();
        const brand = (inventory.brand || '').toLowerCase();
        const category = (inventory.category_main || '').toLowerCase();
        const species = animal.species || 'Unknown';
        const health = animal.health_status || 'Healthy';
        const age = animal.age_category || 'Adult';

        let status = 'suitable';
        let messages = [];

        const rules = {
            'Cat': {
                unsuitable: ['dog food', 'dog treat', 'dog toy', 'canine', 'puppy'],
                warnings: ['xylitol', 'chocolate', 'onion', 'garlic']
            },
            'Dog': {
                unsuitable: ['cat food', 'cat litter', 'feline', 'kitten'],
                warnings: ['xylitol', 'chocolate', 'grapes', 'raisins', 'onion', 'garlic']
            }
        };

        if (health === 'Sick' || health === 'Critical') {
            if (!itemName.includes('veterinary') && !itemName.includes('recovery') && !itemName.includes('medical') && category === 'food') {
                status = 'warning';
                messages.push('[!] Consider using veterinary-prescribed recovery diet for sick animals');
            }
            if (itemName.includes('recovery') || itemName.includes('veterinary') || brand.includes('veterinary')) {
                status = 'excellent';
                messages.push('[✓] Excellent choice! This is a specialized recovery product suitable for sick animals');
            }
        }

        if (age === 'Kitten' || age === 'Puppy') {
            if (itemName.includes('adult') && !itemName.includes('kitten') && !itemName.includes('puppy')) {
                status = 'warning';
                messages.push('[!] This product is for adults. Consider using age-appropriate formula for young animals');
            }
            if (itemName.includes(age.toLowerCase())) {
                messages.push('[✓] Perfect! This product is specifically formulated for young animals');
            }
        }

        if (age === 'Senior') {
            if (itemName.includes('senior') || itemName.includes('7+')) {
                messages.push('[✓] Great! This product supports senior animal health needs');
            }
        }

        if (rules[species]) {
            for (const unsuitable of rules[species].unsuitable) {
                if (itemName.includes(unsuitable) || brand.includes(unsuitable)) {
                    status = 'unsuitable';
                    messages.push(`[✗] UNSUITABLE: This product is not appropriate for ${species.toLowerCase()}s`);
                    break;
                }
            }

            for (const warning of rules[species].warnings) {
                if (itemName.includes(warning) || brand.includes(warning)) {
                    status = 'danger';
                    messages.push(`[⚠] DANGER: Contains ${warning} which is toxic to ${species.toLowerCase()}s`);
                    break;
                }
            }
        }

        if (messages.length === 0) {
            messages.push(`[✓] This item appears suitable for ${animal.name} (${species} - ${health})`);
        }

        return { status, messages };
    }
</script>
