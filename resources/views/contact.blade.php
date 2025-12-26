<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Stray Animal Shelter Management</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<style>
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #9333ea;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #7e22ce;
    }
    /* Smooth line clamp */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #9333ea;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #7e22ce;
    }
</style>
<body class="bg-gray-50">
    @include('navbar')

    <!-- Page Header -->
    <div class="bg-purple-600 shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-3xl font-bold text-white">Contact Us</h1>
            <p class="text-purple-100 text-sm mt-2 font-bold">We're here to help. Reach out to us anytime.</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Account Status & Security Section (For Suspended/Locked Users) -->
        <div class="mb-8 bg-gradient-to-r from-red-600 to-red-700 rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-white mb-2">Account Suspended or Locked?</h2>
                        <p class="text-red-100 mb-4">If your account has been suspended or locked, contact our admin team immediately to resolve the issue.</p>

                        <div class="bg-white bg-opacity-10 rounded-lg p-4 mb-4">
                            <h3 class="text-black font-semibold mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                What to Include in Your Appeal:
                            </h3>
                            <ul class="text-red-50 text-sm space-y-2">
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Your registered email address</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Full name on your account</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Reason for suspension/lock (if known)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Explanation or appeal statement</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Any supporting evidence or screenshots</span>
                                </li>
                            </ul>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <a href="mailto:admin@strayanimal.org?subject=Account%20Appeal%20Request"
                               class="flex items-center justify-center gap-3 bg-white text-red-600 px-6 py-3 rounded-lg font-semibold hover:bg-red-50 transition-all duration-200 shadow-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Email Admin Team
                            </a>
                            <a href="https://wa.me/60123456789?text=I%20need%20help%20with%20my%20account%20status"
                               class="flex items-center justify-center gap-3 bg-white text-red-600 px-6 py-3 rounded-lg font-semibold hover:bg-red-50 transition-all duration-200 shadow-lg">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path>
                                </svg>
                                WhatsApp Admin
                            </a>
                        </div>

                        <p class="text-red-100 text-xs mt-4 italic">
                            ⏱️ Response Time: Account appeals are typically reviewed within 24-48 hours during business days.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Contact Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Phone Card -->
            <div class="bg-white border-l-4 border-blue-500 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Call Us</h3>
                        <p class="text-sm text-gray-600 mb-3">Mon-Fri, 9AM - 6PM</p>
                        <a href="tel:+60123456789" class="text-blue-600 hover:text-blue-700 font-medium text-sm">+60 12-345 6789</a>
                    </div>
                </div>
            </div>

            <!-- Email Card -->
            <div class="bg-white border-l-4 border-green-500 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Email Us</h3>
                        <p class="text-sm text-gray-600 mb-3">We respond within 24 hours</p>
                        <a href="mailto:info@strayanimal.org" class="text-green-600 hover:text-green-700 font-medium text-sm">info@strayanimal.org</a>
                    </div>
                </div>
            </div>

            <!-- Location Card -->
            <div class="bg-white border-l-4 border-orange-500 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Visit Us</h3>
                        <p class="text-sm text-gray-600 mb-3">Main Office</p>
                        <p class="text-orange-600 font-medium text-sm">Jalan Skudai, Johor Bahru</p>
                    </div>
                </div>
            </div>
        </div>

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

        <!-- All Shelter Locations -->
        <div class="mt-12 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-orange-50 border-b border-orange-100 px-6 py-4">
                <h2 class="text-xl font-semibold text-gray-900">Our Shelter Locations</h2>
                <p class="text-sm text-gray-600 mt-1">Visit any of our facilities across Johor</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <!-- Location 1 -->
                    <div class="border-l-4 border-purple-500 bg-purple-50 rounded-lg p-5">
                        <h3 class="font-semibold text-gray-900 mb-3 text-lg">Main Shelter</h3>
                        <div class="space-y-2 text-sm">
                            <p class="text-gray-700">Jalan Skudai, Johor Bahru</p>
                            <div class="flex items-center gap-2 text-purple-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>+60 12-345 6789</span>
                            </div>
                            <div class="flex items-center gap-2 text-purple-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>main@strayanimal.org</span>
                            </div>
                        </div>
                    </div>

                    <!-- Location 2 -->
                    <div class="border-l-4 border-blue-500 bg-blue-50 rounded-lg p-5">
                        <h3 class="font-semibold text-gray-900 mb-3 text-lg">North Branch</h3>
                        <div class="space-y-2 text-sm">
                            <p class="text-gray-700">Pasir Gudang, Johor</p>
                            <div class="flex items-center gap-2 text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>+60 16-789 1234</span>
                            </div>
                            <div class="flex items-center gap-2 text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>north@strayanimal.org</span>
                            </div>
                        </div>
                    </div>

                    <!-- Location 3 -->
                    <div class="border-l-4 border-green-500 bg-green-50 rounded-lg p-5">
                        <h3 class="font-semibold text-gray-900 mb-3 text-lg">West Center</h3>
                        <div class="space-y-2 text-sm">
                            <p class="text-gray-700">Pontian, Johor</p>
                            <div class="flex items-center gap-2 text-green-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>+60 19-876 5432</span>
                            </div>
                            <div class="flex items-center gap-2 text-green-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>west@strayanimal.org</span>
                            </div>
                        </div>
                    </div>

                    <!-- Location 4 -->
                    <div class="border-l-4 border-yellow-500 bg-yellow-50 rounded-lg p-5">
                        <h3 class="font-semibold text-gray-900 mb-3 text-lg">East Facility</h3>
                        <div class="space-y-2 text-sm">
                            <p class="text-gray-700">Kota Tinggi, Johor</p>
                            <div class="flex items-center gap-2 text-yellow-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>+60 11-234 5678</span>
                            </div>
                            <div class="flex items-center gap-2 text-yellow-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>east@strayanimal.org</span>
                            </div>
                        </div>
                    </div>

                    <!-- Location 5 -->
                    <div class="border-l-4 border-indigo-500 bg-indigo-50 rounded-lg p-5">
                        <h3 class="font-semibold text-gray-900 mb-3 text-lg">South Unit</h3>
                        <div class="space-y-2 text-sm">
                            <p class="text-gray-700">Kulai, Johor</p>
                            <div class="flex items-center gap-2 text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>+60 13-456 7890</span>
                            </div>
                            <div class="flex items-center gap-2 text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>south@strayanimal.org</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-12 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-purple-50 border-b border-purple-100 px-6 py-4">
                <h2 class="text-xl font-semibold text-gray-900">Frequently Asked Questions</h2>
            </div>
            <div class="p-6">
                <div class="space-y-5">
                    <div class="border-l-4 border-purple-500 pl-4 py-2">
                        <h3 class="font-semibold text-gray-900 mb-2">How do I report a stray animal?</h3>
                        <p class="text-sm text-gray-600">You can report a stray animal by calling our hotline at +60 12-345 6789, using our online report form, or visiting any of our shelter locations. For emergencies, call our 24/7 emergency line at +60 11-123 4567.</p>
                    </div>

                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <h3 class="font-semibold text-gray-900 mb-2">Can I visit the animals before adopting?</h3>
                        <p class="text-sm text-gray-600">Yes! We encourage potential adopters to visit and spend time with the animals. Please contact us to schedule a visit during our office hours. Walk-ins are also welcome during operating hours.</p>
                    </div>

                    <div class="border-l-4 border-green-500 pl-4 py-2">
                        <h3 class="font-semibold text-gray-900 mb-2">How can I volunteer at the shelter?</h3>
                        <p class="text-sm text-gray-600">We're always looking for dedicated volunteers! Please fill out the contact form above with "Volunteer Opportunity" as the subject, or email us at info@strayanimal.org for more information about volunteer programs.</p>
                    </div>

                    <div class="border-l-4 border-orange-500 pl-4 py-2">
                        <h3 class="font-semibold text-gray-900 mb-2">Do you accept donations?</h3>
                        <p class="text-sm text-gray-600">Yes, we gratefully accept both monetary donations and supplies such as pet food, bedding, and medical supplies. Contact us for more information about how you can help support our mission.</p>
                    </div>

                    <div class="border-l-4 border-indigo-500 pl-4 py-2">
                        <h3 class="font-semibold text-gray-900 mb-2">What areas do you cover?</h3>
                        <p class="text-sm text-gray-600">We have five shelter locations across Johor, covering Johor Bahru, Pasir Gudang, Pontian, Kota Tinggi, and Kulai. We respond to reports throughout these regions and surrounding areas.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
