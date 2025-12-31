<!-- Inventory Create Modal -->
<div id="inventoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-500 to-cyan-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">
                        <i class="fas fa-box mr-2"></i>
                        Add Inventory Item
                    </h2>
                    <p class="text-blue-100 mt-1" id="inventorySlotInfo"></p>
                </div>
                <button onclick="closeInventoryModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <form method="POST" action="{{ route($routePrefix . '.store-inventory') }}" class="p-6 space-y-4" id="inventoryForm">
            @csrf
            <input type="hidden" name="slotID" id="inventorySlotID">

            <!-- Animal Information & Smart Suggestions -->
            <div id="animalInfoSection" class="hidden bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-5 border-2 border-purple-200">
                <div class="flex items-start gap-4">
                    <div class="bg-purple-600 text-white rounded-full p-3">
                        <i class="fas fa-paw text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                            <i class="fas fa-brain text-purple-600"></i>
                            Smart Inventory Suggestions
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                            <div class="bg-white rounded-lg p-3 border border-purple-100">
                                <p class="text-xs text-gray-600 mb-1">Animal</p>
                                <p class="font-bold text-gray-800" id="animalName"></p>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-purple-100">
                                <p class="text-xs text-gray-600 mb-1">Species</p>
                                <p class="font-bold text-gray-800" id="animalSpecies"></p>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-purple-100">
                                <p class="text-xs text-gray-600 mb-1">Health Status</p>
                                <p class="font-bold" id="animalHealth"></p>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-purple-100">
                                <p class="text-xs text-gray-600 mb-1">Age</p>
                                <p class="font-bold text-gray-800" id="animalAge"></p>
                            </div>
                        </div>

                        <!-- Recommended Items -->
                        <div class="bg-white rounded-lg p-4 border-2 border-purple-300">
                            <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <i class="fas fa-lightbulb text-amber-500"></i>
                                Recommended for this Animal
                            </h4>
                            <div id="recommendedItems" class="space-y-2"></div>
                        </div>

                        <!-- Warning for Unsuitable Items -->
                        <div id="restrictedWarning" class="hidden mt-3 bg-red-50 border-l-4 border-red-500 p-3 rounded">
                            <p class="text-sm text-red-800 font-semibold flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span id="restrictedMessage"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Animal Warning -->
            <div id="noAnimalSection" class="hidden bg-amber-50 rounded-xl p-4 border border-amber-200">
                <p class="text-amber-800 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    <span>This slot has no animal assigned. Adding general inventory items.</span>
                </p>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Item Name <span class="text-red-600">*</span>
                </label>
                <input type="text" name="item_name" id="inventoryItemName" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="e.g., Dog Food, Cat Litter" required>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Category <span class="text-red-600">*</span>
                </label>
                <select name="categoryID" id="inventoryCategoryID" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->main }}{{ $category->sub ? ' - ' . $category->sub : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Quantity <span class="text-red-600">*</span>
                    </label>
                    <input type="number" name="quantity" id="inventoryQuantity" min="0" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="0" required>
                </div>

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Weight (kg) <span class="text-gray-500 text-sm">(Optional)</span>
                    </label>
                    <input type="number" name="weight" id="inventoryWeight" min="0" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="0.00">
                </div>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Brand <span class="text-gray-500 text-sm">(Optional)</span>
                </label>
                <input type="text" name="brand" id="inventoryBrand" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="e.g., Royal Canin, Pedigree">
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Status <span class="text-red-600">*</span>
                </label>
                <select name="status" id="inventoryStatus" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                    <option value="available">Available</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeInventoryModal()" id="inventoryCancelBtn" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" id="inventorySubmitBtn" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-cyan-700 flex items-center gap-2">
                    <i class="fas fa-save" id="inventorySubmitIcon"></i>
                    <span id="inventorySubmitText">Add Inventory</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Intelligent Inventory Recommendation System
    const inventoryRecommendations = {
        // Cat-specific recommendations
        'Cat': {
            'Healthy': {
                'Adult': [
                    { category: 'Food', type: 'Adult Cat Food', brands: ['Royal Canin Adult Cat', 'Hill\'s Science Diet Adult', 'Purina Pro Plan Adult'], icon: 'utensils' },
                    { category: 'Litter', type: 'Clumping Cat Litter', brands: ['Tidy Cats', 'Fresh Step', 'World\'s Best Cat Litter'], icon: 'box' },
                    { category: 'Toys', type: 'Interactive Toys', brands: ['Kong Cat', 'Petstages', 'Catit'], icon: 'gamepad' }
                ],
                'Kitten': [
                    { category: 'Food', type: 'Kitten Growth Formula', brands: ['Royal Canin Kitten', 'Hill\'s Science Diet Kitten', 'Blue Buffalo Kitten'], icon: 'utensils' },
                    { category: 'Food', type: 'Kitten Milk Replacer', brands: ['KMR', 'PetAg', 'Hartz'], icon: 'baby-carriage' },
                    { category: 'Litter', type: 'Non-clumping (Kitten-safe)', brands: ['Yesterday\'s News', 'Ökocat', 'Paper Pellets'], icon: 'box' }
                ],
                'Senior': [
                    { category: 'Food', type: 'Senior Cat Food (7+ years)', brands: ['Royal Canin Senior', 'Hill\'s Science Diet Senior', 'Purina Pro Plan Senior'], icon: 'utensils' },
                    { category: 'Supplements', type: 'Joint Support', brands: ['Cosequin', 'Dasuquin', 'NaturVet'], icon: 'pills' }
                ]
            },
            'Sick': {
                'Adult': [
                    { category: 'Food', type: 'Veterinary Recovery Diet', brands: ['Royal Canin Veterinary Recovery', 'Hill\'s a/d Critical Care', 'Purina Pro Plan Veterinary Diets'], icon: 'notes-medical', priority: 'high' },
                    { category: 'Medicine', type: 'Electrolyte Solution', brands: ['Veterinary Prescribed', 'Pet-A-Lyte'], icon: 'syringe', priority: 'high' },
                    { category: 'Supplies', type: 'Heating Pad', brands: ['K&H Pet Products', 'Snuggle Safe'], icon: 'thermometer-half' }
                ],
                'Kitten': [
                    { category: 'Food', type: 'Recovery Formula (Kitten)', brands: ['Royal Canin Babycat', 'KMR Emergency', 'Hill\'s a/d'], icon: 'notes-medical', priority: 'high' },
                    { category: 'Supplies', type: 'Pediatric Heating Pad', brands: ['K&H Thermo-Kitty', 'SnuggleSafe'], icon: 'thermometer-half', priority: 'high' }
                ],
                'Senior': [
                    { category: 'Food', type: 'Senior Recovery Diet', brands: ['Royal Canin Senior Consult', 'Hill\'s i/d Digestive Care'], icon: 'notes-medical', priority: 'high' },
                    { category: 'Supplements', type: 'Immune Support', brands: ['VetriScience', 'Nutramax'], icon: 'shield-alt', priority: 'high' }
                ]
            },
            'Recovering': {
                'Adult': [
                    { category: 'Food', type: 'Convalescent Formula', brands: ['Royal Canin Recovery', 'Hill\'s Prescription Diet'], icon: 'heart', priority: 'medium' },
                    { category: 'Supplements', type: 'Probiotic', brands: ['Purina FortiFlora', 'Proviable-DC'], icon: 'capsules', priority: 'medium' }
                ]
            }
        },
        // Dog-specific recommendations
        'Dog': {
            'Healthy': {
                'Adult': [
                    { category: 'Food', type: 'Adult Dog Food', brands: ['Royal Canin Adult', 'Hill\'s Science Diet Adult', 'Purina Pro Plan Adult'], icon: 'utensils' },
                    { category: 'Toys', type: 'Chew Toys', brands: ['Kong Classic', 'Nylabone', 'Benebone'], icon: 'bone' },
                    { category: 'Supplies', type: 'Leash & Collar', brands: ['Ruffwear', 'PetSafe', 'Blueberry Pet'], icon: 'link' }
                ],
                'Puppy': [
                    { category: 'Food', type: 'Puppy Growth Formula', brands: ['Royal Canin Puppy', 'Hill\'s Science Diet Puppy', 'Blue Buffalo Puppy'], icon: 'utensils' },
                    { category: 'Food', type: 'Puppy Milk Replacer', brands: ['Esbilac', 'PetAg', 'Nutri-Vet'], icon: 'baby-carriage' },
                    { category: 'Toys', type: 'Puppy Teething Toys', brands: ['Kong Puppy', 'Nylabone Puppy', 'Petstages'], icon: 'bone' }
                ],
                'Senior': [
                    { category: 'Food', type: 'Senior Dog Food (7+ years)', brands: ['Royal Canin Senior', 'Hill\'s Science Diet Senior', 'Purina Pro Plan Senior'], icon: 'utensils' },
                    { category: 'Supplements', type: 'Joint & Mobility Support', brands: ['Cosequin DS', 'Dasuquin', 'Glucosamine'], icon: 'pills' }
                ]
            },
            'Sick': {
                'Adult': [
                    { category: 'Food', type: 'Veterinary Recovery Diet', brands: ['Royal Canin Veterinary Recovery', 'Hill\'s a/d Critical Care'], icon: 'notes-medical', priority: 'high' },
                    { category: 'Medicine', type: 'Rehydration Solution', brands: ['Veterinary Prescribed', 'Oralade'], icon: 'syringe', priority: 'high' },
                    { category: 'Supplies', type: 'Heating Pad', brands: ['K&H Pet Products', 'Snuggle Safe'], icon: 'thermometer-half' }
                ],
                'Puppy': [
                    { category: 'Food', type: 'Puppy Recovery Formula', brands: ['Royal Canin Starter', 'Hill\'s a/d'], icon: 'notes-medical', priority: 'high' },
                    { category: 'Supplies', type: 'Pediatric Incubator Pad', brands: ['K&H Thermo-Pet', 'SnuggleSafe'], icon: 'thermometer-half', priority: 'high' }
                ]
            },
            'Recovering': {
                'Adult': [
                    { category: 'Food', type: 'Convalescent Formula', brands: ['Royal Canin Recovery', 'Hill\'s Prescription Diet'], icon: 'heart', priority: 'medium' },
                    { category: 'Supplements', type: 'Digestive Support', brands: ['Purina FortiFlora', 'Proviable'], icon: 'capsules', priority: 'medium' }
                ]
            }
        },
        // Bird recommendations
        'Bird': {
            'Healthy': {
                'Adult': [
                    { category: 'Food', type: 'Premium Bird Seed Mix', brands: ['Kaytee', 'Higgins', 'ZuPreem'], icon: 'seedling' },
                    { category: 'Supplies', type: 'Cage & Perches', brands: ['Prevue Pet', 'Vision', 'You & Me'], icon: 'home' }
                ]
            },
            'Sick': {
                'Adult': [
                    { category: 'Food', type: 'Hand-feeding Formula', brands: ['Kaytee Exact', 'Harrison\'s Recovery'], icon: 'notes-medical', priority: 'high' },
                    { category: 'Supplies', type: 'Brooder/Heating', brands: ['K&H Snuggle Up', 'Infrared Lamp'], icon: 'thermometer-half', priority: 'high' }
                ]
            }
        },
        // Default/Other animals
        'Other': {
            'Healthy': {
                'Adult': [
                    { category: 'Food', type: 'Species-appropriate Diet', brands: ['Consult Veterinarian'], icon: 'utensils' },
                    { category: 'Supplies', type: 'Habitat Supplies', brands: ['Pet Store Recommended'], icon: 'home' }
                ]
            },
            'Sick': {
                'Adult': [
                    { category: 'Food', type: 'Veterinary Diet', brands: ['Veterinary Prescribed'], icon: 'notes-medical', priority: 'high' }
                ]
            }
        }
    };

    function openInventoryModal(slotId, slotName, animalData = null) {
        document.getElementById('inventoryModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Set slot ID
        document.getElementById('inventorySlotID').value = slotId;

        // Display slot info
        document.getElementById('inventorySlotInfo').textContent = `Adding to: ${slotName}`;

        // Reset form
        document.getElementById('inventoryForm').reset();
        document.getElementById('inventorySlotID').value = slotId;

        // Show intelligent suggestions if animal exists
        if (animalData && animalData.name) {
            displayAnimalSuggestions(animalData);
        } else {
            document.getElementById('animalInfoSection').classList.add('hidden');
            document.getElementById('noAnimalSection').classList.remove('hidden');
        }
    }

    function displayAnimalSuggestions(animal) {
        // Show animal info section
        document.getElementById('animalInfoSection').classList.remove('hidden');
        document.getElementById('noAnimalSection').classList.add('hidden');

        // Populate animal details
        document.getElementById('animalName').textContent = animal.name;
        document.getElementById('animalSpecies').textContent = animal.species || 'Unknown';

        // Health status with color
        const healthEl = document.getElementById('animalHealth');
        const healthColors = {
            'Healthy': 'text-green-600',
            'Sick': 'text-red-600',
            'Recovering': 'text-orange-600',
            'Critical': 'text-red-700'
        };
        healthEl.textContent = animal.health_status || 'Unknown';
        healthEl.className = `font-bold ${healthColors[animal.health_status] || 'text-gray-600'}`;

        // Age category
        document.getElementById('animalAge').textContent = animal.age_category || 'Adult';

        // Generate recommendations
        const recommendations = getRecommendations(animal.species, animal.health_status, animal.age_category);
        displayRecommendations(recommendations);
    }

    function getRecommendations(species, healthStatus, ageCategory) {
        // Normalize inputs
        const normalizedSpecies = species || 'Other';
        const normalizedHealth = healthStatus || 'Healthy';
        const normalizedAge = ageCategory || 'Adult';

        // Get recommendations from database
        const speciesRecs = inventoryRecommendations[normalizedSpecies] || inventoryRecommendations['Other'];
        const healthRecs = speciesRecs[normalizedHealth] || speciesRecs['Healthy'];
        return healthRecs[normalizedAge] || healthRecs['Adult'] || [];
    }

    function displayRecommendations(recommendations) {
        const container = document.getElementById('recommendedItems');
        container.innerHTML = '';

        if (recommendations.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-600">No specific recommendations available.</p>';
            return;
        }

        recommendations.forEach((rec, index) => {
            const priorityBadge = rec.priority === 'high'
                ? '<span class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">CRITICAL</span>'
                : rec.priority === 'medium'
                ? '<span class="ml-2 px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">IMPORTANT</span>'
                : '';

            const item = document.createElement('div');
            item.className = 'bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg p-3 border border-blue-200 hover:border-blue-400 cursor-pointer';
            item.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="bg-blue-600 text-white rounded-lg p-2">
                        <i class="fas fa-${rec.icon} text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-gray-800 text-sm flex items-center">
                            ${rec.type}
                            ${priorityBadge}
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            <span class="font-semibold">Category:</span> ${rec.category}
                        </p>
                        <p class="text-xs text-blue-700 mt-1">
                            <span class="font-semibold">Suggested Brands:</span> ${rec.brands.join(', ')}
                        </p>
                    </div>
                    <button type="button" onclick="applyRecommendation('${rec.type}', '${rec.brands[0]}')"
                            class="px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700">
                        Apply
                    </button>
                </div>
            `;
            container.appendChild(item);
        });
    }

    function applyRecommendation(itemType, suggestedBrand) {
        // Auto-fill form with recommendation
        document.getElementById('inventoryItemName').value = itemType;
        document.getElementById('inventoryBrand').value = suggestedBrand;

        // Scroll to form
        document.getElementById('inventoryItemName').scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Highlight field
        document.getElementById('inventoryItemName').classList.add('ring-2', 'ring-blue-500');
        setTimeout(() => {
            document.getElementById('inventoryItemName').classList.remove('ring-2', 'ring-blue-500');
        }, 2000);
    }

    function closeInventoryModal() {
        document.getElementById('inventoryModal').classList.add('hidden');
        document.body.style.overflow = 'auto';

        // Reset visibility
        document.getElementById('animalInfoSection').classList.add('hidden');
        document.getElementById('noAnimalSection').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('inventoryModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeInventoryModal();
        }
    });

    // Handle form submission with loading state
    document.getElementById('inventoryForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('inventorySubmitBtn');
        const submitIcon = document.getElementById('inventorySubmitIcon');
        const submitText = document.getElementById('inventorySubmitText');
        const cancelBtn = document.getElementById('inventoryCancelBtn');

        // Disable buttons
        submitBtn.disabled = true;
        cancelBtn.disabled = true;

        // Change icon to spinner
        submitIcon.className = 'fas fa-spinner fa-spin';
        submitText.textContent = 'Adding...';

        // Allow form to submit
        return true;
    });
</script>

<style>
    /* Spinner animation */
    .fa-spin {
        animation: fa-spin 1s infinite linear;
    }

    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>