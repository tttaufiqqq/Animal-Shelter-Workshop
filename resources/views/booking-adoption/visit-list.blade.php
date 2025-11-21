<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Visit List</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-4xl mx-auto p-6">

    <h1 class="text-3xl font-bold mb-6">Your Visit List</h1>

    <!-- SUCCESS MESSAGE -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 p-4 rounded-xl mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- ERROR MESSAGE -->
    @if (session('error'))
        <div class="bg-red-100 border border-red-300 text-red-800 p-4 rounded-xl mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- VALIDATION ERRORS -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 p-4 rounded-xl mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($animals->count() == 0)
        <div class="bg-gray-100 p-6 rounded-xl text-center">
            <p class="text-gray-600">Your visit list is empty.</p>
        </div>
        @return
    @endif

    <form method="POST" action="{{ route('adoption.book') }}" class="space-y-6">
        @csrf

        <!-- Show selected animals -->
        <div class="bg-white shadow-md rounded-xl p-6">
            <h2 class="text-xl font-bold mb-4">Animals You Want to Visit</h2>

            <div class="space-y-4">
                @foreach($animals as $animal)
                    <div class="border p-4 rounded-lg flex justify-between items-center">
                        <div>
                            <strong>{{ $animal->name }}</strong>
                            <br>
                            <span class="text-gray-600 text-sm">{{ $animal->species }}</span>
                        </div>

                        <button formaction="{{ route('visit.list.remove', $animal->id) }}" 
                                formmethod="POST" class="text-red-600 font-semibold">
                            @csrf
                            Remove
                        </button>
                    </div>

                    <input type="hidden" name="animal_ids[]" value="{{ $animal->id }}">
                @endforeach
            </div>
        </div>

        <!-- Optional remarks -->
        <div class="bg-white shadow-md rounded-xl p-6">
            <h2 class="text-xl font-bold mb-4">Remarks (Optional)</h2>

            @foreach($animals as $animal)
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium">
                        Why are you interested in {{ $animal->name }}?
                    </label>
                    <textarea name="remarks[{{ $animal->id }}]" class="w-full border rounded-lg p-3"
                              rows="2"></textarea>
                </div>
            @endforeach
        </div>

        <!-- Appointment -->
        <div class="bg-white shadow-md rounded-xl p-6">
            <label class="block font-semibold mb-2">Appointment Date & Time</label>
            <input type="datetime-local" name="appointment_date" required
                   min="{{ date('Y-m-d\TH:i') }}"
                   class="w-full border rounded-lg p-3">
        </div>

        <!-- Terms -->
        <div class="flex items-start">
            <input type="checkbox" name="terms" required class="mt-1">
            <span class="ml-3 text-sm">
                I understand this is an appointment request, pending approval.
            </span>
        </div>

        <button type="submit"
            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-xl">
            Confirm Appointment
        </button>

    </form>

</div>

</body>
</html>
