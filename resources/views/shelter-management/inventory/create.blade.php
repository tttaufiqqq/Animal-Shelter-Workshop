<!DOCTYPE html>
<html>
<head>
    <title>Add Inventory Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">Add Inventory Item</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('inventory.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Item Name</label>
            <input type="text" class="form-control" name="item_name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control" name="quantity" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Brand</label>
            <input type="text" class="form-control" name="brand">
        </div>

        <div class="mb-3">
            <label class="form-label">Weight</label>
            <input type="text" class="form-control" name="weight">
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" required>
                <option value="">-- Select Status --</option>
                <option value="Available">Available</option>
                <option value="Out of Stock">Out of Stock</option>
                <option value="Reserved">Reserved</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Slot</label>
            <select class="form-select" name="slotID" required>
                <option value="">-- Select Slot --</option>
                @foreach($slots as $slot)
                    <option value="{{ $slot->id }}">{{ $slot->slot_name ?? 'Slot '.$slot->id }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select" name="categoryID" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->category_name ?? 'Category '.$category->id }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Add Inventory</button>
    </form>
</div>

</body>
</html>
