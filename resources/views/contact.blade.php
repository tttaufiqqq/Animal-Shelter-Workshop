<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    @include('navbar')

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl font-bold mb-4">Get In Touch</h1>
            <p class="text-xl text-purple-100">We're here to help and answer any questions you might have</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Contact Options -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transition duration-300">
                <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl">📞</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Call Us</h3>
                <p class="text-gray-600 mb-4">Available Mon-Fri, 9AM - 6PM</p>
                <a href="tel:+60123456789" class="text-purple-700 font-semibold hover:text-purple-900">+60 12-345 6789</a>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transition duration-300">
                <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl">✉️</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Email Us</h3>
                <p class="text-gray-600 mb-4">We'll respond within 24 hours</p>
                <a href="mailto:info@strayanimal.org" class="text-purple-700 font-semibold hover:text-purple-900">info@strayanimal.org</a>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transition duration-300">
                <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl">📍</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Visit Us</h3>
                <p class="text-gray-600 mb-4">Main Shelter Office</p>
                <p class="text-purple-700 font-semibold">Jalan Skudai, Johor Bahru</p>
            </div>
        </div>

        <!-- Main Contact Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-12">
            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Send Us a Message</h2>
                <form class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="John Doe">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="john@example.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="+60 12-345 6789">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                        <select required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select a subject</option>
                            <option>General Inquiry</option>
                            <option>Report a Stray Animal</option>
                            <option>Adoption Question</option>
                            <option>Volunteer Opportunity</option>
                            <option>Donation</option>
                            <option>Partnership</option>
                            <option>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                        <textarea required rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Tell us how we can help you..."></textarea>
                    </div>

                    <button type="submit" class="w-full bg-purple-700 hover:bg-purple-800 text-white py-3 rounded-lg font-semibold transition duration-300">
                        Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Information & Map -->
            <div class="space-y-8">
                <!-- Office Hours -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Office Hours</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                            <span class="text-gray-700 font-medium">Monday - Friday</span>
                            <span class="text-purple-700 font-semibold">9:00 AM - 6:00 PM</span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                            <span class="text-gray-700 font-medium">Saturday</span>
                            <span class="text-purple-700 font-semibold">10:00 AM - 4:00 PM</span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                            <span class="text-gray-700 font-medium">Sunday</span>
                            <span class="text-purple-700 font-semibold">10:00 AM - 2:00 PM</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Public Holidays</span>
                            <span class="text-red-600 font-semibold">Closed</span>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-lg p-8 text-white">
                    <div class="flex items-center mb-4">
                        <span class="text-4xl mr-4">🚨</span>
                        <h2 class="text-2xl font-bold">Emergency Contact</h2>
                    </div>
                    <p class="mb-4">For urgent animal rescue or emergency situations:</p>
                    <a href="tel:+60111234567" class="text-2xl font-bold hover:underline">+60 11-123 4567</a>
                    <p class="text-sm mt-2 text-red-100">Available 24/7</p>
                </div>

                <!-- Map Placeholder -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="h-64 bg-gradient-to-br from-purple-200 to-purple-300 flex items-center justify-center">
                        <div class="text-center">
                            <span class="text-6xl mb-4 block">🗺️</span>
                            <p class="text-gray-700 font-semibold">Main Shelter Location</p>
                            <p class="text-gray-600 text-sm">Jalan Skudai, Johor Bahru</p>
                        </div>
                    </div>
                    <div class="p-4 bg-gray-50">
                        <button class="w-full bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg font-medium transition duration-300">
                            Open in Maps
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Shelter Locations -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">All Shelter Locations</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Location 1 -->
                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition duration-300">
                    <div class="flex items-start mb-4">
                        <span class="text-3xl mr-3">🏠</span>
                        <div>
                            <h3 class="font-bold text-lg text-gray-800 mb-1">Main Shelter</h3>
                            <p class="text-sm text-gray-600">Jalan Skudai, Johor Bahru</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <p class="text-gray-700">📞 +60 12-345 6789</p>
                        <p class="text-gray-700">✉️ main@strayanimal.org</p>
                    </div>
                </div>

                <!-- Location 2 -->
                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition duration-300">
                    <div class="flex items-start mb-4">
                        <span class="text-3xl mr-3">🏠</span>
                        <div>
                            <h3 class="font-bold text-lg text-gray-800 mb-1">North Branch</h3>
                            <p class="text-sm text-gray-600">Pasir Gudang, Johor</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <p class="text-gray-700">📞 +60 16-789 1234</p>
                        <p class="text-gray-700">✉️ north@strayanimal.org</p>
                    </div>
                </div>

                <!-- Location 3 -->
                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition duration-300">
                    <div class="flex items-start mb-4">
                        <span class="text-3xl mr-3">🏠</span>
                        <div>
                            <h3 class="font-bold text-lg text-gray-800 mb-1">West Center</h3>
                            <p class="text-sm text-gray-600">Pontian, Johor</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <p class="text-gray-700">📞 +60 19-876 5432</p>
                        <p class="text-gray-700">✉️ west@strayanimal.org</p>
                    </div>
                </div>

                <!-- Location 4 -->
                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition duration-300">
                    <div class="flex items-start mb-4">
                        <span class="text-3xl mr-3">🏠</span>
                        <div>
                            <h3 class="font-bold text-lg text-gray-800 mb-1">East Facility</h3>
                            <p class="text-sm text-gray-600">Kota Tinggi, Johor</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <p class="text-gray-700">📞 +60 11-234 5678</p>
                        <p class="text-gray-700">✉️ east@strayanimal.org</p>
                    </div>
                </div>

                <!-- Location 5 -->
                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition duration-300">
                    <div class="flex items-start mb-4">
                        <span class="text-3xl mr-3">🏠</span>
                        <div>
                            <h3 class="font-bold text-lg text-gray-800 mb-1">South Unit</h3>
                            <p class="text-sm text-gray-600">Kulai, Johor</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <p class="text-gray-700">📞 +60 13-456 7890</p>
                        <p class="text-gray-700">✉️ south@strayanimal.org</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Frequently Asked Questions</h2>
            <div class="space-y-6">
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">How do I report a stray animal?</h3>
                    <p class="text-gray-600">You can report a stray animal by calling our hotline at +60 12-345 6789, using our online report form, or visiting any of our shelter locations. For emergencies, call our 24/7 emergency line at +60 11-123 4567.</p>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Can I visit the animals before adopting?</h3>
                    <p class="text-gray-600">Yes! We encourage potential adopters to visit and spend time with the animals. Please contact us to schedule a visit during our office hours. Walk-ins are also welcome during operating hours.</p>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">How can I volunteer at the shelter?</h3>
                    <p class="text-gray-600">We're always looking for dedicated volunteers! Please fill out the contact form above with "Volunteer Opportunity" as the subject, or email us at info@strayanimal.org for more information about volunteer programs.</p>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Do you accept donations?</h3>
                    <p class="text-gray-600">Yes, we gratefully accept both monetary donations and supplies such as pet food, bedding, and medical supplies. Contact us for more information about how you can help support our mission.</p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">What areas do you cover?</h3>
                    <p class="text-gray-600">We have five shelter locations across Johor, covering Johor Bahru, Pasir Gudang, Pontian, Kota Tinggi, and Kulai. We respond to reports throughout these regions and surrounding areas.</p>
                </div>
            </div>
        </div>

        <!-- Social Media -->
        <div class="mt-12 text-center">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">Follow Us on Social Media</h3>
            <div class="flex justify-center space-x-6">
                <a href="#" class="bg-blue-600 hover:bg-blue-700 text-white rounded-full w-12 h-12 flex items-center justify-center text-xl transition duration-300">
                    f
                </a>
                <a href="#" class="bg-blue-400 hover:bg-blue-500 text-white rounded-full w-12 h-12 flex items-center justify-center text-xl transition duration-300">
                    t
                </a>
                <a href="#" class="bg-pink-600 hover:bg-pink-700 text-white rounded-full w-12 h-12 flex items-center justify-center text-xl transition duration-300">
                    📷
                </a>
                <a href="#" class="bg-red-600 hover:bg-red-700 text-white rounded-full w-12 h-12 flex items-center justify-center text-xl transition duration-300">
                    ▶
                </a>
            </div>
        </div>
    </div>
</body>
</html>