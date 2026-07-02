        <!-- Main Contact Section -->
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Contact Form (Left Column) -->
                <div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-purple-50 border-b border-purple-100 px-6 py-4">
                        <h2 class="text-xl font-semibold text-gray-900">Send Us a Message</h2>
                        <p class="text-sm text-gray-600 mt-1">Fill out the form and we'll get back to you as soon as possible</p>
                    </div>
                    <div class="p-6">
                        <form class="space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" required
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all text-sm"
                                           placeholder="John Doe">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                                    <input type="email" required
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all text-sm"
                                           placeholder="john@example.com">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                    <input type="tel"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all text-sm"
                                           placeholder="+60 12-345 6789">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject <span class="text-red-500">*</span></label>
                                    <select required
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all text-sm">
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
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Message <span class="text-red-500">*</span></label>
                                <textarea required rows="6"
                                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all text-sm resize-none"
                                          placeholder="Tell us how we can help you..."></textarea>
                            </div>

                            <button type="submit"
                                    class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg font-medium transition-colors text-sm shadow-sm">
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar (Right Column - 3 sections stacked vertically) -->
            <div class="space-y-6">
                <!-- Office Hours -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-blue-50 border-b border-blue-100 px-5 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Office Hours</h3>
                    </div>
                    <div class="p-5">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-700">Monday - Friday</span>
                                <span class="text-sm font-semibold text-blue-600">9:00 AM - 6:00 PM</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-700">Saturday</span>
                                <span class="text-sm font-semibold text-blue-600">10:00 AM - 4:00 PM</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-700">Sunday</span>
                                <span class="text-sm font-semibold text-blue-600">10:00 AM - 2:00 PM</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm font-medium text-gray-700">Public Holidays</span>
                                <span class="text-sm font-semibold text-red-600">Closed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="bg-red-600 rounded-lg shadow-md p-5 text-white">
                    <div class="flex items-start gap-3 mb-3">
                        <svg class="w-6 h-6 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold mb-1">Emergency Contact</h3>
                            <p class="text-sm text-red-100 mb-3">For urgent animal rescue or emergency situations</p>
                        </div>
                    </div>
                    <a href="tel:+60111234567" class="block text-center bg-white text-red-600 py-2.5 rounded-lg font-bold hover:bg-red-50 transition-colors text-lg">
                        +60 11-123 4567
                    </a>
                    <p class="text-xs text-red-100 text-center mt-2">Available 24/7</p>
                </div>

                <!-- Additional Contact Info -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-green-50 border-b border-green-100 px-5 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Other Ways to Reach Us</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">WhatsApp</p>
                                <a href="https://wa.me/60123456789" class="text-sm text-green-600 hover:text-green-700">+60 12-345 6789</a>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Facebook</p>
                                <a href="#" class="text-sm text-blue-600 hover:text-blue-700">@StrayAnimalJohor</a>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-pink-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Instagram</p>
                                <a href="#" class="text-sm text-pink-600 hover:text-pink-700">@strayanimal_johor</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
