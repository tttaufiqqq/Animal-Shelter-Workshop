# Dashboard Components

This folder contains modular Livewire components for the Dashboard page, breaking down the monolithic dashboard into reusable, maintainable pieces.

## Component Structure

```
app/Livewire/Dashboard/
├── DatabaseWarningBanner.php    # Shows database connectivity warnings
├── PageHeader.php               # Dashboard title and description
├── YearFilter.php               # Year selection dropdown
├── MetricCard.php               # Reusable metric card component
├── RevenueBySpeciesChart.php    # Revenue bar chart
├── BookingStatusChart.php       # Doughnut chart for booking status
├── BookingsByMonthChart.php     # Line chart for monthly bookings
└── VolumeVsValueChart.php       # Combined bar/line chart
```

## Components Overview

### 1. DatabaseWarningBanner
**Purpose:** Displays warnings when distributed databases are offline

**Props:**
- `$dbDisconnected` (array) - Array of offline database connections

**Usage:**
```blade
@livewire('dashboard.database-warning-banner', ['dbDisconnected' => $dbDisconnected ?? []])
```

**Features:**
- Shows count of offline databases
- Lists affected modules
- Dismissible banner
- Auto-hides when all databases are online

---

### 2. PageHeader
**Purpose:** Displays dashboard title and description

**Props:**
- `$title` (string, default: 'Booking Analytics Dashboard') - Page title
- `$description` (string, default: 'Overview of booking performance and trends') - Page description

**Usage:**
```blade
@livewire('dashboard.page-header')
// OR with custom values
@livewire('dashboard.page-header', ['title' => 'Custom Title', 'description' => 'Custom description'])
```

**Customization:**
- Easy to modify text without touching main dashboard
- Can be reused across multiple dashboards

---

### 3. YearFilter
**Purpose:** Year selection dropdown with Livewire reactivity

**Props:**
- `$years` (array) - Array of available years
- `$selectedYear` (string/int) - Currently selected year

**Events:**
- Dispatches `yearChanged` event when year selection changes

**Usage:**
```blade
@livewire('dashboard.year-filter', ['years' => $years, 'selectedYear' => $selectedYear], key('year-filter-'.$selectedYear))
```

**Event Handling:**
The parent Dashboard component listens for `yearChanged` events:
```php
protected $listeners = ['yearChanged'];

public function yearChanged($year)
{
    $this->selectedYear = $year;
}
```

---

### 4. MetricCard (Reusable Component)
**Purpose:** Displays key metrics in a styled card

**Props:**
- `$title` (string) - Card title (e.g., "Total Bookings")
- `$value` (string/int) - Metric value (e.g., "150" or "85%")
- `$description` (string) - Supporting text (e.g., "All time bookings")
- `$icon` (string) - Font Awesome icon class (e.g., "fas fa-calendar-alt")
- `$colorScheme` (string) - Tailwind color name (e.g., "blue", "green", "red", "purple", "amber")

**Usage:**
```blade
@livewire('dashboard.metric-card', [
    'title' => 'Total Bookings',
    'value' => $totalBookings,
    'description' => 'All time bookings',
    'icon' => 'fas fa-calendar-alt',
    'colorScheme' => 'blue'
])
```

**Available Color Schemes:**
- `blue` - Default, for general metrics
- `green` - Success/positive metrics
- `red` - Error/negative metrics
- `purple` - Important metrics
- `amber` - Warning/attention metrics

**Adding New Color Schemes:**
Simply pass any Tailwind color name (e.g., "indigo", "pink", "teal"). The component uses dynamic classes:
- `from-{color}-50`, `to-{color}-100` (background gradient)
- `border-{color}-200` (border)
- `text-{color}-700` (title)
- `from-{color}-500`, `to-{color}-600` (icon gradient)
- `text-{color}-900` (value)
- `text-{color}-600` (description)

---

### 5. RevenueBySpeciesChart
**Purpose:** Displays revenue breakdown by animal species as horizontal bars

**Props:**
- `$topAnimals` (Collection) - Collection of top animals with revenue data

**Data Structure:**
```php
[
    (object)[
        'name' => 'Dog',
        'total_revenue' => 1500.00,
        'percentage' => 45.50
    ],
    // ...
]
```

**Usage:**
```blade
@livewire('dashboard.revenue-by-species-chart', ['topAnimals' => $topAnimals])
```

**Features:**
- Shows top 5 species by revenue
- Displays revenue amount and percentage
- Animated progress bars
- Empty state handling

---

### 6. BookingStatusChart
**Purpose:** Doughnut chart showing booking status distribution

**Props:**
- `$bookingTypeBreakdown` (Collection) - Collection of booking statuses

**Data Structure:**
```php
[
    (object)[
        'status' => 'Pending',
        'count' => 25,
        'percentage' => 30.00
    ],
    // ...
]
```

**Usage:**
```blade
@livewire('dashboard.booking-status-chart', ['bookingTypeBreakdown' => $bookingTypeBreakdown])
```

**Features:**
- Chart.js powered doughnut chart
- Auto-initializes on page load and Livewire updates
- Responsive design
- Legend on the right side

---

### 7. BookingsByMonthChart
**Purpose:** Line chart showing bookings trend by month

**Props:**
- `$bookingsByMonth` (Collection) - Collection of monthly booking data

**Data Structure:**
```php
[
    (object)[
        'month' => 1,
        'month_name' => 'January',
        'count' => 45
    ],
    // ...
]
```

**Usage:**
```blade
@livewire('dashboard.bookings-by-month-chart', ['bookingsByMonth' => $bookingsByMonth])
```

**Features:**
- Smooth line chart with data labels
- Shows exact booking count on each point
- Responsive and animated
- Area fill for better visualization

---

### 8. VolumeVsValueChart
**Purpose:** Combined bar and line chart showing adoption volume vs average value

**Props:**
- `$volumeVsValue` (Collection) - Collection of volume and value data

**Data Structure:**
```php
[
    (object)[
        'year' => 2025,
        'month' => 1,
        'month_name' => 'Jan 2025',
        'volume' => 12,
        'avg_value' => 350.50
    ],
    // ...
]
```

**Usage:**
```blade
@livewire('dashboard.volume-vs-value-chart', ['volumeVsValue' => $volumeVsValue])
```

**Features:**
- Dual Y-axis chart (volume on left, value on right)
- Bar chart for volume, line chart for average value
- Custom tooltip formatting
- Shows last 6 months of data

---

## Adding New Components

### Step 1: Create Component Class
```bash
php artisan make:livewire Dashboard/YourComponent
```

This creates:
- `app/Livewire/Dashboard/YourComponent.php`
- `resources/views/livewire/dashboard/your-component.blade.php`

### Step 2: Define Component Logic
```php
<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class YourComponent extends Component
{
    public $data;

    public function mount($data)
    {
        $this->data = $data;
    }

    public function render()
    {
        return view('livewire.dashboard.your-component');
    }
}
```

### Step 3: Create Blade View
```blade
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">Your Component</h2>
    <!-- Your component content here -->
</div>
```

### Step 4: Use in Dashboard
```blade
@livewire('dashboard.your-component', ['data' => $yourData])
```

---

## Chart Components Best Practices

### 1. Chart Initialization
All chart components follow this pattern:
```javascript
document.addEventListener('DOMContentLoaded', function() {
    initializeYourChart();
});

document.addEventListener('livewire:navigated', function() {
    initializeYourChart();
});

Livewire.hook('morph.updated', () => {
    initializeYourChart();
});

let yourChart;

function initializeYourChart() {
    // Destroy existing chart to prevent duplicates
    if (yourChart) {
        yourChart.destroy();
    }

    // Initialize new chart
    yourChart = new Chart(ctx, config);
}
```

### 2. Chart.js Dependencies
The main dashboard blade loads Chart.js scripts once:
```blade
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
@endpush
```

Chart components just use these libraries without re-importing.

### 3. Responsive Charts
Always use:
```javascript
options: {
    responsive: true,
    maintainAspectRatio: false, // For fixed height charts
    // ...
}
```

---

## Event Communication

### Parent to Child
Pass data as props:
```blade
@livewire('dashboard.metric-card', ['value' => $totalBookings])
```

### Child to Parent
Dispatch events:
```php
// In child component
$this->dispatch('eventName', parameter: $value);

// In parent component
protected $listeners = ['eventName'];

public function eventName($value)
{
    // Handle event
}
```

**Example: Year Filter**
```php
// YearFilter.php
public function updatedSelectedYear($value)
{
    $this->dispatch('yearChanged', year: $value);
}

// Dashboard.php
protected $listeners = ['yearChanged'];

public function yearChanged($year)
{
    $this->selectedYear = $year;
}
```

---

## Benefits of Component Architecture

### 1. **Maintainability**
- Each component has a single responsibility
- Easy to locate and fix bugs
- Changes to one component don't affect others

### 2. **Reusability**
- MetricCard can be used with any metric
- Charts can be reused in other dashboards
- Components can be composed in different layouts

### 3. **Scalability**
- Add new metrics by adding MetricCard instances
- Create new chart types without modifying existing ones
- Easy to add new dashboard sections

### 4. **Testability**
- Each component can be tested independently
- Easier to write unit tests for small components
- Faster test execution

### 5. **Code Organization**
- Clear separation of concerns
- Logical file structure
- Easy for new developers to understand

---

## Migration from Monolithic Dashboard

The original dashboard had all HTML in one file (`livewire/dashboard.blade.php`). The refactored version:

**Before:**
- 1 file with 408 lines
- All charts in one script block
- Hard to modify specific sections
- Difficult to reuse components

**After:**
- Main dashboard: ~80 lines (clean, readable)
- 9 focused components
- Each component self-contained
- Easy to add/remove/modify components

---

## Future Enhancements

### Possible Improvements:
1. **Add More Chart Types**
   - Pie charts for animal distribution
   - Scatter plots for correlations
   - Heatmaps for time-based patterns

2. **Component Variants**
   - MetricCard with trend indicators (↑ ↓)
   - MetricCard with sparkline charts
   - Chart zoom and pan features

3. **Interactivity**
   - Click on chart to filter data
   - Drill-down functionality
   - Export chart as image

4. **Performance**
   - Lazy load charts below the fold
   - Cache chart data
   - Progressive rendering

5. **Accessibility**
   - Add ARIA labels
   - Keyboard navigation for charts
   - Screen reader support

---

## Troubleshooting

### Charts Not Rendering
1. Check browser console for JavaScript errors
2. Verify Chart.js and plugin scripts are loaded
3. Ensure canvas elements have unique IDs
4. Check that data is being passed correctly to component

### Year Filter Not Working
1. Verify `yearChanged` event is dispatched
2. Check that parent Dashboard has `$listeners` array
3. Ensure Livewire is properly initialized
4. Check browser console for Livewire errors

### Styling Issues
1. Ensure Tailwind CSS is compiled with all component classes
2. Check for color scheme typos
3. Verify responsive classes are correct
4. Use browser dev tools to inspect applied styles

---

## Support

For questions or issues with the dashboard components:
1. Check this README
2. Review component source code
3. Check Laravel Livewire documentation
4. Check Chart.js documentation
