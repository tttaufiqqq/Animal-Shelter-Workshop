<script>
    // ==================== VIEW SWITCHING FUNCTIONS ====================
    let currentView = 'slots'; // Default view

    function switchView(view) {
        currentView = view;

        document.querySelectorAll('.view-tab').forEach(tab => {
            tab.classList.remove('bg-gradient-to-r', 'from-purple-500', 'to-purple-600', 'text-white', 'shadow-md');
            tab.classList.add('text-gray-600', 'hover:bg-gray-100');
        });

        const activeTab = document.getElementById(view + 'Tab');
        activeTab.classList.remove('text-gray-600', 'hover:bg-gray-100');
        activeTab.classList.add('bg-gradient-to-r', 'from-purple-500', 'to-purple-600', 'text-white', 'shadow-md');

        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.add('hidden');
        });

        document.querySelectorAll('.stats-section').forEach(section => {
            section.classList.add('hidden');
        });

        document.querySelectorAll('.search-filter-section').forEach(section => {
            section.classList.add('hidden');
        });

        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.classList.add('hidden');
        });

        document.getElementById(view + 'Content').classList.remove('hidden');
        document.getElementById(view + 'Stats').classList.remove('hidden');
        document.getElementById(view + 'Filters').classList.remove('hidden');

        let buttonId;
        if (view === 'categories') {
            buttonId = 'addCategoryBtn';
        } else {
            buttonId = 'add' + view.charAt(0).toUpperCase() + view.slice(1, -1) + 'Btn';
        }
        document.getElementById(buttonId).classList.remove('hidden');

        clearFilters();
    }

    // ==================== FILTER FUNCTIONS ====================
    function filterSlots() {
        const searchTerm = document.getElementById('searchSlotsInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
        const sectionFilter = document.getElementById('sectionFilter').value;

        const rows = document.querySelectorAll('.slot-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const slotName = row.getAttribute('data-slot-name') || '';
            const slotStatus = row.getAttribute('data-slot-status') || '';
            const slotSection = row.getAttribute('data-slot-section') || '';

            const matchesSearch = slotName.includes(searchTerm);
            const matchesStatus = !statusFilter || slotStatus === statusFilter;
            const matchesSection = !sectionFilter || slotSection === sectionFilter;

            if (matchesSearch && matchesStatus && matchesSection) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        updateResultsCount('slots', visibleCount, rows.length);
    }

    function filterSections() {
        const searchTerm = document.getElementById('searchSectionsInput').value.toLowerCase();
        const rows = document.querySelectorAll('.section-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const sectionName = row.getAttribute('data-section-name') || '';
            const matchesSearch = sectionName.includes(searchTerm);

            if (matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        updateResultsCount('sections', visibleCount, rows.length);
    }

    function filterCategories() {
        const searchTerm = document.getElementById('searchCategoriesInput').value.toLowerCase();
        const rows = document.querySelectorAll('.category-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const categoryMain = row.getAttribute('data-category-main') || '';
            const categorySub = row.getAttribute('data-category-sub') || '';
            const matchesSearch = categoryMain.includes(searchTerm) || categorySub.includes(searchTerm);

            if (matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        updateResultsCount('categories', visibleCount, rows.length);
    }

    function clearFilters() {
        const searchInputs = ['searchSlotsInput', 'searchSectionsInput', 'searchCategoriesInput'];
        searchInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) input.value = '';
        });

        const statusFilter = document.getElementById('statusFilter');
        const sectionFilter = document.getElementById('sectionFilter');
        if (statusFilter) statusFilter.value = '';
        if (sectionFilter) sectionFilter.value = '';

        if (currentView === 'slots') filterSlots();
        else if (currentView === 'sections') filterSections();
        else if (currentView === 'categories') filterCategories();
    }

    function updateResultsCount(view, visible, total) {
        const resultsDiv = document.getElementById(view + 'ResultsCount');
        if (!resultsDiv) return;

        const viewLabels = {
            'slots': 'slot',
            'sections': 'section',
            'categories': 'categor'
        };
        const label = viewLabels[view];
        const pluralLabel = label + (label.endsWith('r') ? 'ies' : 's');

        if (visible === total) {
            resultsDiv.innerHTML = `<i class="fas fa-check-circle text-green-600 mr-1"></i>Showing all <strong>${total}</strong> ${total === 1 ? label : pluralLabel}`;
        } else {
            resultsDiv.innerHTML = `<i class="fas fa-filter text-purple-600 mr-1"></i>Showing <strong>${visible}</strong> of <strong>${total}</strong> ${pluralLabel}`;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const slotsCount = document.querySelectorAll('.slot-row').length;
        const sectionsCount = document.querySelectorAll('.section-row').length;
        const categoriesCount = document.querySelectorAll('.category-row').length;

        updateResultsCount('slots', slotsCount, slotsCount);
        updateResultsCount('sections', sectionsCount, sectionsCount);
        updateResultsCount('categories', categoriesCount, categoriesCount);
    });
</script>
