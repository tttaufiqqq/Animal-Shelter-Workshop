                {{-- Upload New Images --}}
                <div class="group">
                    <label class="block text-gray-700 font-bold mb-3 flex items-center gap-2">
                        <i class="fas fa-cloud-upload-alt text-purple-600"></i>
                        Upload New Images
                    </label>

                    {{-- File Input with Custom Styling --}}
                    <div class="relative">
                        <input type="file" name="images[]" multiple accept="image/*" id="newImageInput"
                               class="hidden"
                               onchange="previewNewImages(event)">
                        <label for="newImageInput"
                               class="flex flex-col items-center justify-center w-full h-40 border-3 border-dashed border-purple-300 rounded-2xl cursor-pointer bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 transition-all hover:border-purple-500 hover:scale-[1.02] shadow-lg">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center">
                                <div class="bg-purple-200 p-4 rounded-full mb-3">
                                    <i class="fas fa-images text-purple-600 text-3xl"></i>
                                </div>
                                <p class="mb-2 text-sm text-gray-700 font-bold">
                                    <span class="text-purple-600">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500">PNG, JPG, JPEG (MAX. 5MB each)</p>
                                <p class="text-xs text-purple-600 font-semibold mt-1">
                                    <i class="fas fa-info-circle"></i>
                                    Hold Ctrl/Cmd to select multiple files
                                </p>
                            </div>
                        </label>
                    </div>

                    {{-- Image Preview Container --}}
                    <div id="newImagePreviewContainer" class="hidden mt-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-bold text-gray-700 flex items-center gap-2">
                                <i class="fas fa-eye text-purple-600"></i>
                                New Images Preview
                            </p>
                            <button type="button" onclick="clearNewImages()"
                                    class="text-xs text-red-600 hover:text-red-800 font-semibold flex items-center gap-1 hover:scale-105 transition-transform">
                                <i class="fas fa-times-circle"></i>
                                Clear All
                            </button>
                        </div>
                        <div id="newImagePreviewGrid" class="grid grid-cols-2 md:grid-cols-4 gap-3"></div>
                    </div>

                    @error('images')
                    <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                    @enderror
                    @error('images.*')
                    <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-6 border-t-2 border-gray-200 mt-6">
                    <p class="text-xs text-gray-500 flex items-center gap-1">
                        <i class="fas fa-info-circle text-purple-600"></i>
                        Fields marked with <span class="text-red-500 font-bold">*</span> are required
                    </p>
                    <div class="flex gap-3 w-full sm:w-auto">
                        <button type="button" onclick="closeEditModal({{ $animal->id }})"
                                class="flex-1 sm:flex-none group px-6 py-3.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <i class="fas fa-times group-hover:rotate-90 transition-transform"></i>
                            <span>Cancel</span>
                        </button>

                        <button type="submit" id="submitButton"
                                class="flex-1 sm:flex-none group px-8 py-3.5 bg-gradient-to-r from-purple-600 via-purple-700 to-purple-800 hover:from-purple-700 hover:via-purple-800 hover:to-purple-900 text-white font-bold rounded-xl transition-all duration-300 shadow-xl hover:shadow-2xl hover:scale-105 flex items-center justify-center gap-2">
                            <i class="fas fa-save group-hover:scale-110 transition-transform"></i>
                            <span>Update Animal</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
