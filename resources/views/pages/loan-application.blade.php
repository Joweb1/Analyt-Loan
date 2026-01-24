<x-app-layout>
    @section('title', 'New Loan Application')
    <div class="flex items-center gap-2 mb-6">
        <a class="text-gray-500 text-sm font-semibold hover:text-primary transition-colors" href="#">Loans</a>
        <span class="text-gray-400 text-sm">/</span>
        <span class="text-gray-900 text-sm font-bold">New Application</span>
    </div>
    <div class="mb-10">
        <h1 class="text-gray-900 text-4xl font-extrabold leading-tight tracking-tight">New Loan Application</h1>
        <p class="text-gray-600 text-base font-medium mt-2">Create a new loan record for a registered borrower within the Nigerian market.</p>
    </div>
    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <form action="#" method="POST">
            <div class="p-8 border-b border-gray-100">
                <div class="flex items-center gap-3 mb-6">
                    <span class="material-symbols-outlined text-primary bg-primary/10 p-2 rounded-lg">person</span>
                    <h2 class="text-gray-900 text-xl font-extrabold tracking-tight">Borrower Information</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700">Select Borrower</label>
                        <div class="relative">
                            <select class="form-select w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary h-12 text-sm">
                                <option disabled="" selected="" value="">Search by name or BVN</option>
                                <option value="1">Chidi Okechukwu - 22109483xxx</option>
                                <option value="2">Amina Yusuf - 22441908xxx</option>
                                <option value="3">Olawale Johnson - 22901123xxx</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700">ID Verification Status</label>
                        <div class="flex items-center h-12 px-4 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100">
                            <span class="material-symbols-outlined text-sm mr-2">verified</span>
                            <span class="text-sm font-semibold">Verified (BVN Link Successful)</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-8 border-b border-gray-100">
                <div class="flex items-center gap-3 mb-6">
                    <span class="material-symbols-outlined text-primary bg-primary/10 p-2 rounded-lg">payments</span>
                    <h2 class="text-gray-900 text-xl font-extrabold tracking-tight">Financial Terms</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700">Principal Amount</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">₦</span>
                            <input class="form-input w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary h-12 pl-10 text-sm" placeholder="0.00" type="text"/>
                        </div>
                        <p class="text-xs text-gray-500 font-medium">Suggested max: ₦5,000,000.00</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700">Interest Rate</label>
                        <div class="relative">
                            <input class="form-input w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary h-12 pr-10 text-sm" placeholder="5.5" type="text"/>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">%</span>
                        </div>
                        <p class="text-xs text-gray-500 font-medium">Calculated monthly</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700">Loan Tenure</label>
                        <select class="form-select w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary h-12 text-sm">
                            <option>3 Months</option>
                            <option>6 Months</option>
                            <option selected="">12 Months</option>
                            <option>24 Months</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="p-8 bg-gray-50/50">
                <div class="flex items-center gap-3 mb-6">
                    <span class="material-symbols-outlined text-primary bg-primary/10 p-2 rounded-lg">gpp_maybe</span>
                    <h2 class="text-gray-900 text-xl font-extrabold tracking-tight">Security &amp; Collateral</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700">Collateral Type</label>
                        <select class="form-select w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary h-12 text-sm">
                            <option disabled="" selected="" value="">Select an option</option>
                            <option>Motor Vehicle</option>
                            <option>Landed Property</option>
                            <option>Business Equipment</option>
                            <option>Cash Collateral</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700">Estimated Market Value</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">₦</span>
                            <input class="form-input w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary h-12 pl-10 text-sm" placeholder="0.00" type="text"/>
                        </div>
                    </div>
                    <div class="md:col-span-2 flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700">Collateral Description</label>
                        <textarea class="form-textarea w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary h-24 px-4 py-3 text-sm" placeholder="Provide detailed description of the collateral..."></textarea>
                    </div>
                </div>
            </div>
            <div class="p-8 bg-white border-t border-gray-100 flex items-center justify-between">
                <button class="px-6 py-3 text-sm font-extrabold text-gray-500 hover:text-gray-900 transition-colors" type="button">
                    Cancel Application
                </button>
                <div class="flex items-center gap-4">
                    <button class="px-6 py-3 text-sm font-extrabold text-primary bg-primary/10 rounded-full hover:bg-primary/20 transition-all" type="button">
                        Save as Draft
                    </button>
                    <button class="px-8 py-3 text-sm font-extrabold text-white bg-primary rounded-full hover:bg-opacity-90 shadow-lg shadow-primary/30 transition-all flex items-center gap-2" type="submit">
                        <span>Submit Application</span>
                        <span class="material-symbols-outlined text-[18px]">send</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 bg-white rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-extrabold text-primary uppercase tracking-wider mb-2">Loan Calculator</p>
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Est. Monthly Repayment</p>
                    <p class="text-2xl font-black text-primary">₦0.00</p>
                </div>
                <span class="material-symbols-outlined text-gray-400">calculate</span>
            </div>
        </div>
        <div class="p-6 bg-white rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-extrabold text-gray-500 uppercase tracking-wider mb-2">Processing Time</p>
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Average Approval</p>
                    <p class="text-2xl font-black text-gray-900">24 Hours</p>
                </div>
                <span class="material-symbols-outlined text-gray-400">schedule</span>
            </div>
        </div>
        <div class="p-6 bg-white rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-extrabold text-gray-500 uppercase tracking-wider mb-2">Compliance</p>
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Required Documents</p>
                    <p class="text-2xl font-black text-gray-900">4 Items</p>
                </div>
                <span class="material-symbols-outlined text-gray-400">article</span>
            </div>
        </div>
    </div>
</x-app-layout>
