<script>
    // Intelligent Inventory Recommendation System
    const inventoryRecommendations = {
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
</script>
