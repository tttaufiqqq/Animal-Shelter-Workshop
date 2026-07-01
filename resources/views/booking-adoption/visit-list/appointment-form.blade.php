                    <!-- Appointment Section -->
                    <div class="bg-gradient-to-br from-purple-50 to-blue-50 border-2 border-purple-200 rounded-2xl p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-calendar-check text-purple-600"></i>
                            Schedule Your Visit
                            <span class="text-red-500 text-sm">*</span>
                        </h2>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Date Input -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-calendar text-purple-600 mr-1"></i>
                                        Preferred Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date"
                                           id="appointmentDate"
                                           name="appointment_date"
                                           required
                                           min="{{ date('Y-m-d') }}"
                                           class="w-full border-2 border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-gray-700 rounded-xl p-3.5 text-base transition-all duration-200">
                                </div>

                                <!-- Time Select Dropdown -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-clock text-purple-600 mr-1"></i>
                                        Preferred Time <span class="text-red-500">*</span>
                                    </label>
                                    <select id="appointmentTime"
                                            name="appointment_time"
                                            required
                                            class="w-full border-2 border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-gray-700 rounded-xl p-3.5 text-base transition-all duration-200">
                                        <option value="">Select a time</option>
                                        <option value="09:00">9:00 AM</option>
                                        <option value="09:30">9:30 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="10:30">10:30 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="11:30">11:30 AM</option>
                                        <option value="12:00">12:00 PM</option>
                                        <option value="12:30">12:30 PM</option>
                                        <option value="13:00">1:00 PM</option>
                                        <option value="13:30">1:30 PM</option>
                                        <option value="14:00">2:00 PM</option>
                                        <option value="14:30">2:30 PM</option>
                                        <option value="15:00">3:00 PM</option>
                                        <option value="15:30">3:30 PM</option>
                                        <option value="16:00">4:00 PM</option>
                                        <option value="16:30">4:30 PM</option>
                                        <option value="17:00">5:00 PM</option>
                                    </select>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 mt-2">
                                <i class="fas fa-info-circle"></i>
                                Our adoption center is open Monday-Saturday, 9 AM - 5 PM
                            </p>
                            <p id="appointmentError" class="text-xs text-red-600 mt-2 hidden">
                                <i class="fas fa-exclamation-circle"></i>
                                Please select a date and time for your visit
                            </p>
                        </div>

                        <!-- Terms Checkbox -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-purple-200 mt-4">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input type="checkbox"
                                       name="terms"
                                       id="termsCheckbox"
                                       required
                                       onclick="window.updateConfirmButton();"
                                       class="mt-1 w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-2 focus:ring-purple-500 cursor-pointer">
                                <span class="text-sm text-gray-700 flex-1">
                                        <span class="font-semibold text-gray-800 group-hover:text-purple-600 transition-colors">I understand and agree</span>
                                        <br>
                                        This is a visit appointment request pending staff approval. You will be notified once confirmed.
                                    </span>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="button"
                                onclick="closeVisitModal()"
                                id="visitCancelBtn"
                                class="flex-1 px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-200 flex items-center justify-center gap-2 border-2 border-gray-200">
                            <i class="fas fa-arrow-left"></i>
                            Continue Browsing
                        </button>
                        <button type="submit"
                                id="confirmBookingBtn"
                                disabled
                                class="flex-1 px-6 py-4 bg-gray-300 text-gray-500 font-bold rounded-xl shadow-lg transition-all duration-200 flex items-center justify-center gap-2 cursor-not-allowed disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="visitBtnText">
                                <i class="fas fa-check-circle mr-2"></i>Confirm Visit Booking
                            </span>
                            <span id="visitBtnLoading" class="hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                            </span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
