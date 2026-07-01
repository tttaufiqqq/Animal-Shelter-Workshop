<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assigned Rescues - Stray Animals Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @include('stray-reporting.index-caretaker.styles')
</head>
<body class="bg-gray-50 min-h-screen">

@include('navbar')
@include('stray-reporting.index-caretaker.connectivity-banner')
@include('stray-reporting.index-caretaker.status-filters')
@include('stray-reporting.index-caretaker.priority-filter')
@include('stray-reporting.index-caretaker.rescues-table')
@include('stray-reporting.index-caretaker.map-modal')
@include('stray-reporting.index-caretaker.scripts')

</body>
</html>
