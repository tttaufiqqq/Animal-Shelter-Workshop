<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoption - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    @include('navbar')

    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl font-bold mb-4">Find Your Perfect Companion</h1>
            <p class="text-xl text-purple-100 mb-6">Give a loving home to a furry friend in need</p>
            <button class="bg-white text-purple-700 px-8 py-3 rounded-lg font-semibold hover:bg-purple-50 transition duration-300">
                Start Your Adoption Journey
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-5xl mb-4">❤️</div>
                <p class="text-4xl font-bold text-purple-700 mb-2">287</p>
                <p class="text-gray-600">Animals Adopted</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-5xl mb-4">🏠</div>
                <p class="text-4xl font-bold text-purple-700 mb-2">45</p>
                <p class="text-gray-600">Available for Adoption</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-5xl mb-4">😊</div>
                <p class="text-4xl font-bold text-purple-700 mb-2">98%</p>
                <p class="text-gray-600">Happy Families</p>
            </div>
        </div>

        <!-- Adoption Process -->
        <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Adoption Process</h2>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <div class="text-center">
                    <div class="bg-purple-700 text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                    <h3 class="font-semibold text-lg mb-2">Browse Animals</h3>
                    <p class="text-gray-600 text-sm">Find your perfect match from our available animals</p>
                </div>
                <div class="text-center">
                    <div class="bg-purple-700 text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                    <h3 class="font-semibold text-lg mb-2">Submit Application</h3>
                    <p class="text-gray-600 text-sm">Fill out our adoption application form</p>
                </div>
                <div class="text-center">
                    <div class="bg-purple-700 text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                    <h3 class="font-semibold text-lg mb-2">Meet & Greet</h3>
                    <p class="text-gray-600 text-sm">Visit and interact with your chosen animal</p>
                </div>
                <div class="text-center">
                    <div class="bg-purple-700 text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">4</div>
                    <h3 class="font-semibold text-lg mb-2">Pay Adoption Fee</h3>
                    <p class="text-gray-600 text-sm">Complete payment to cover vaccination and care costs</p>
                </div>
                <div class="text-center">
                    <div class="bg-purple-700 text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">5</div>
                    <h3 class="font-semibold text-lg mb-2">Take Them Home</h3>
                    <p class="text-gray-600 text-sm">Complete paperwork and welcome your new friend</p>
                </div>
            </div>
        </div>

        <!-- Adoption Fees -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Adoption Fees</h2>
            <p class="text-center text-gray-600 mb-8">Our adoption fees help cover the cost of vaccinations, spaying/neutering, medical care, and shelter expenses.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="border-2 border-purple-200 rounded-lg p-6 text-center hover:border-purple-500 transition duration-300">
                    <div class="text-4xl mb-4">🐕</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Adult Dog</h3>
                    <p class="text-3xl font-bold text-purple-700 mb-2">RM 150</p>
                    <p class="text-sm text-gray-600">3+ years old</p>
                </div>
                <div class="border-2 border-purple-200 rounded-lg p-6 text-center hover:border-purple-500 transition duration-300">
                    <div class="text-4xl mb-4">🐶</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Puppy</h3>
                    <p class="text-3xl font-bold text-purple-700 mb-2">RM 200</p>
                    <p class="text-sm text-gray-600">Under 1 year</p>
                </div>
                <div class="border-2 border-purple-200 rounded-lg p-6 text-center hover:border-purple-500 transition duration-300">
                    <div class="text-4xl mb-4">🐈</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Adult Cat</h3>
                    <p class="text-3xl font-bold text-purple-700 mb-2">RM 100</p>
                    <p class="text-sm text-gray-600">1+ years old</p>
                </div>
                <div class="border-2 border-purple-200 rounded-lg p-6 text-center hover:border-purple-500 transition duration-300">
                    <div class="text-4xl mb-4">🐱</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Kitten</h3>
                    <p class="text-3xl font-bold text-purple-700 mb-2">RM 120</p>
                    <p class="text-sm text-gray-600">Under 1 year</p>
                </div>
            </div>
            <div class="mt-8 bg-purple-50 rounded-lg p-6">
                <h4 class="font-semibold text-gray-800 mb-3">What's Included:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="flex items-center text-gray-700">
                        <span class="text-green-600 mr-2">✓</span>
                        <span>Complete health check-up</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <span class="text-green-600 mr-2">✓</span>
                        <span>All vaccinations</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <span class="text-green-600 mr-2">✓</span>
                        <span>Spaying/Neutering</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <span class="text-green-600 mr-2">✓</span>
                        <span>Deworming treatment</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <span class="text-green-600 mr-2">✓</span>
                        <span>Microchipping</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <span class="text-green-600 mr-2">✓</span>
                        <span>Initial supplies kit</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Find Your Match</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Animal Type</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>All Animals</option>
                        <option>Dogs</option>
                        <option>Cats</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Age</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>Any Age</option>
                        <option>Puppy/Kitten</option>
                        <option>Young (1-3 years)</option>
                        <option>Adult (3-7 years)</option>
                        <option>Senior (7+ years)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Size</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>Any Size</option>
                        <option>Small</option>
                        <option>Medium</option>
                        <option>Large</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>Any Gender</option>
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Available Animals -->
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Available for Adoption</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Animal Card 1 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition duration-300">
                <div class="relative">
                    <div class="h-64 bg-gradient-to-br from-orange-300 to-orange-400 flex items-center justify-center">
                        <span class="text-9xl">🐕</span>
                    </div>
                    <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                        Available
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Max</h3>
                    <div class="flex items-center space-x-2 text-gray-600 mb-4">
                        <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-medium">Golden Retriever</span>
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">2 years</span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p>✓ Vaccinated</p>
                        <p>✓ Neutered</p>
                        <p>✓ Good with kids</p>
                        <p>✓ House trained</p>
                    </div>
                    <p class="text-gray-700 mb-4">Energetic and loving dog who enjoys outdoor activities. Perfect for active families!</p>
                    <button class="w-full bg-purple-700 hover:bg-purple-800 text-white py-3 rounded-lg font-semibold transition duration-300">
                        Adopt Me
                    </button>
                </div>
            </div>

            <!-- Animal Card 2 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition duration-300">
                <div class="relative">
                    <div class="h-64 bg-gradient-to-br from-gray-200 to-gray-400 flex items-center justify-center">
                        <span class="text-9xl">🐈</span>
                    </div>
                    <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                        Available
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Luna</h3>
                    <div class="flex items-center space-x-2 text-gray-600 mb-4">
                        <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-medium">Persian Cat</span>
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">1 year</span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p>✓ Vaccinated</p>
                        <p>✓ Spayed</p>
                        <p>✓ Calm temperament</p>
                        <p>✓ Indoor cat</p>
                    </div>
                    <p class="text-gray-700 mb-4">Sweet and gentle cat who loves cuddles. Perfect companion for quiet homes.</p>
                    <button class="w-full bg-purple-700 hover:bg-purple-800 text-white py-3 rounded-lg font-semibold transition duration-300">
                        Adopt Me
                    </button>
                </div>
            </div>

            <!-- Animal Card 3 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition duration-300">
                <div class="relative">
                    <div class="h-64 bg-gradient-to-br from-yellow-200 to-yellow-400 flex items-center justify-center">
                        <span class="text-9xl">🐕</span>
                    </div>
                    <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                        Available
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Charlie</h3>
                    <div class="flex items-center space-x-2 text-gray-600 mb-4">
                        <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-medium">Labrador</span>
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">4 years</span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p>✓ Vaccinated</p>
                        <p>✓ Neutered</p>
                        <p>✓ Friendly</p>
                        <p>✓ Obedient</p>
                    </div>
                    <p class="text-gray-700 mb-4">Loyal and playful companion. Great with children and other pets!</p>
                    <button class="w-full bg-purple-700 hover:bg-purple-800 text-white py-3 rounded-lg font-semibold transition duration-300">
                        Adopt Me
                    </button>
                </div>
            </div>

            <!-- Animal Card 4 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition duration-300">
                <div class="relative">
                    <div class="h-64 bg-gradient-to-br from-orange-200 to-orange-300 flex items-center justify-center">
                        <span class="text-9xl">🐈</span>
                    </div>
                    <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                        Available
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Milo</h3>
                    <div class="flex items-center space-x-2 text-gray-600 mb-4">
                        <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-medium">Tabby Cat</span>
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">3 years</span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p>✓ Vaccinated</p>
                        <p>✓ Neutered</p>
                        <p>✓ Playful</p>
                        <p>✓ Independent</p>
                    </div>
                    <p class="text-gray-700 mb-4">Active and curious cat. Loves to explore and play with toys!</p>
                    <button class="w-full bg-purple-700 hover:bg-purple-800 text-white py-3 rounded-lg font-semibold transition duration-300">
                        Adopt Me
                    </button>
                </div>
            </div>

            <!-- Animal Card 5 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition duration-300">
                <div class="relative">
                    <div class="h-64 bg-gradient-to-br from-pink-200 to-pink-300 flex items-center justify-center">
                        <span class="text-9xl">🐕</span>
                    </div>
                    <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                        Available
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Daisy</h3>
                    <div class="flex items-center space-x-2 text-gray-600 mb-4">
                        <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-medium">Mixed Breed</span>
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">1 year</span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p>✓ Vaccinated</p>
                        <p>✓ Spayed</p>
                        <p>✓ Sweet nature</p>
                        <p>✓ Adaptable</p>
                    </div>
                    <p class="text-gray-700 mb-4">Young and adaptable pup looking for her forever family. Very affectionate!</p>
                    <button class="w-full bg-purple-700 hover:bg-purple-800 text-white py-3 rounded-lg font-semibold transition duration-300">
                        Adopt Me
                    </button>
                </div>
            </div>

            <!-- Animal Card 6 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition duration-300">
                <div class="relative">
                    <div class="h-64 bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
                        <span class="text-9xl">🐈</span>
                    </div>
                    <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                        Available
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Shadow</h3>
                    <div class="flex items-center space-x-2 text-gray-600 mb-4">
                        <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-medium">Black Cat</span>
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">6 months</span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p>✓ Vaccinated</p>
                        <p>✓ Neutered</p>
                        <p>✓ Energetic</p>
                        <p>✓ Curious</p>
                    </div>
                    <p class="text-gray-700 mb-4">Playful kitten with lots of energy. Loves to chase toys and climb!</p>
                    <button class="w-full bg-purple-700 hover:bg-purple-800 text-white py-3 rounded-lg font-semibold transition duration-300">
                        Adopt Me
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Stories -->
        <div class="mt-16 mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Happy Adoption Stories</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-100 rounded-full p-3 mr-4">
                            <span class="text-2xl">❤️</span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Sarah & Bella</h4>
                            <p class="text-sm text-gray-500">Adopted 2 months ago</p>
                        </div>
                    </div>
                    <p class="text-gray-700">"Bella has brought so much joy to our family. She's the perfect companion and we can't imagine life without her!"</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-100 rounded-full p-3 mr-4">
                            <span class="text-2xl">❤️</span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Ahmad & Leo</h4>
                            <p class="text-sm text-gray-500">Adopted 4 months ago</p>
                        </div>
                    </div>
                    <p class="text-gray-700">"Leo is such a gentle soul. Adopting him was the best decision we ever made. Thank you for this amazing process!"</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-100 rounded-full p-3 mr-4">
                            <span class="text-2xl">❤️</span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Emily & Whiskers</h4>
                            <p class="text-sm text-gray-500">Adopted 1 month ago</p>
                        </div>
                    </div>
                    <p class="text-gray-700">"Whiskers adapted to our home immediately. He's playful, loving, and has become part of our family!"</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-purple-700 to-purple-900 rounded-lg p-12 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Ready to Change a Life?</h2>
            <p class="text-xl mb-6">Start your adoption journey today and give a loving home to an animal in need.</p>
            <button class="bg-white text-purple-700 px-8 py-3 rounded-lg font-semibold hover:bg-purple-50 transition duration-300">
                Apply for Adoption
            </button>
        </div>
    </div>
</body>
</html>