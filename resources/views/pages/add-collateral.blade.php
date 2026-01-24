<x-app-layout>
    @section('title', 'Add Collateral')
    <div class="flex items-center gap-2 mb-6">
        <a class="text-gray-400 text-sm font-medium hover:text-primary transition-colors" href="#">Dashboard</a>
        <span class="material-symbols-outlined text-gray-300 text-sm">chevron_right</span>
        <a class="text-gray-400 text-sm font-medium hover:text-primary transition-colors" href="#">Vault</a>
        <span class="material-symbols-outlined text-gray-300 text-sm">chevron_right</span>
        <span class="text-primary text-sm font-bold">Add Collateral</span>
    </div>
    <div class="w-full max-w-4xl">
        <div class="mb-10 text-center lg:text-left">
            <h2 class="text-4xl font-extrabold tracking-tight text-primary dark:text-white mb-2">Add New Collateral</h2>
            <p class="text-gray-500 dark:text-gray-400 text-lg">Secure and link assets to borrower profiles within the vault system.</p>
        </div>
        <!-- Main Form Card -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl shadow-primary/5 border border-border-light dark:border-gray-800 overflow-hidden">
            <div class="p-8 lg:p-12">
                <form class="space-y-10">
                    <!-- Borrower Section -->
                    <section>
                        <div class="flex items-center gap-2 mb-6">
                            <span class="material-symbols-outlined text-primary dark:text-white bg-gray-100 dark:bg-gray-800 p-1.5 rounded-lg text-xl">person_search</span>
                            <h3 class="text-xl font-bold">Borrower Link</h3>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Select Borrower</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                                <select class="w-full pl-12 pr-4 py-4 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary appearance-none transition-all">
                                    <option disabled="" selected="" value="">Search for a borrower (e.g. Chinedu)</option>
                                    <option value="1">Chinedu Okafor (ID: #8821)</option>
                                    <option value="2">Amina Yusuf (ID: #7710)</option>
                                    <option value="3">Olumide Babatunde (ID: #9945)</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">unfold_more</span>
                            </div>
                        </div>
                    </section>
                    <!-- Asset Details Section -->
                    <section>
                        <div class="flex items-center gap-2 mb-6 border-t border-gray-100 dark:border-gray-800 pt-10">
                            <span class="material-symbols-outlined text-primary dark:text-white bg-gray-100 dark:bg-gray-800 p-1.5 rounded-lg text-xl">inventory_2</span>
                            <h3 class="text-xl font-bold">Asset Specifications</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Asset Name</label>
                                <input class="w-full px-4 py-4 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="e.g. MacBook Pro M2" type="text"/>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Asset Category</label>
                                <div class="relative">
                                    <select class="w-full px-4 py-4 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary appearance-none transition-all">
                                        <option disabled="" selected="" value="">Choose category</option>
                                        <option>Electronics</option>
                                        <option>Vehicle</option>
                                        <option>Real Estate</option>
                                        <option>Gold/Jewelry</option>
                                        <option>Others</option>
                                    </select>
                                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Estimated Value (₦)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-500">₦</span>
                                    <input class="w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="5,000,000" type="text"/>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Serial Number / Asset ID</label>
                                <input class="w-full px-4 py-4 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="SN-123456789" type="text"/>
                            </div>
                        </div>
                    </section>
                    <!-- Documentation Section -->
                    <section>
                        <div class="flex items-center gap-2 mb-6 border-t border-gray-100 dark:border-gray-800 pt-10">
                            <span class="material-symbols-outlined text-primary dark:text-white bg-gray-100 dark:bg-gray-800 p-1.5 rounded-lg text-xl">upload_file</span>
                            <h3 class="text-xl font-bold">Documentation</h3>
                        </div>
                        <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-12 text-center hover:border-primary/50 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all cursor-pointer group">
                            <div class="bg-gray-100 dark:bg-gray-800 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-3xl text-gray-500 group-hover:text-primary">cloud_upload</span>
                            </div>
                            <h4 class="text-lg font-bold mb-1">Upload Proof of Ownership</h4>
                            <p class="text-gray-500 text-sm mb-4">Drag and drop photos or legal documents here</p>
                            <span class="inline-block px-6 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-full text-sm font-bold shadow-sm">Browse Files</span>
                        </div>
                    </section>
                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-10 border-t border-gray-100 dark:border-gray-800">
                        <button class="text-gray-500 font-bold hover:text-red-500 transition-colors" type="button">Discard Draft</button>
                        <div class="flex gap-4">
                            <button class="px-8 py-4 text-primary font-bold border border-gray-200 dark:border-gray-700 rounded-full hover:bg-gray-50 dark:hover:bg-gray-800 transition-all" type="button">Save for Later</button>
                            <button class="px-12 py-4 bg-primary text-white font-bold rounded-full shadow-xl shadow-primary/30 hover:scale-105 active:scale-95 transition-all" type="submit">
                                Vault Asset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <p class="mt-8 text-center text-gray-400 text-sm">
            <span class="material-symbols-outlined align-middle text-sm mr-1">verified_user</span>
            All asset data is encrypted and stored securely in the vault for risk assessment.
        </p>
    </div>
</x-app-layout>
