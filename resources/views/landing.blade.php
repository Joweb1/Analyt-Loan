<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyt Loan 2.0</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 font-sans">

    <!-- Hero Section -->
    <section class="text-center py-20 px-4">
        <h1 class="text-4xl md:text-6xl font-bold mb-4">The Self-Driving Operating System for Micro-Lenders.</h1>
        <p class="text-lg md:text-xl text-gray-600 mb-8">Making lending as simple as sending an email.</p>
        <div class="flex justify-center mb-10">
            <img src="https://placehold.co/800x400" alt="Analyt Loan 2.0 Dashboard" class="rounded-2xl shadow-lg">
        </div>
        <a href="{{ route('register.org') }}" class="bg-primary text-white font-bold py-4 px-8 rounded-2xl text-lg hover:opacity-90 transition inline-block">Start Your Engine</a>
    </section>

    <!-- "Lemonade Stand" Analogy Section -->
    <section class="text-center py-20 px-4 bg-gray-50">
        <div class="max-w-3xl mx-auto">
            <p class="text-xl md:text-2xl leading-relaxed">Imagine you run a lemonade stand, but 100 people promise to pay you later. You can't remember them all. Analyt Loan is the 'Robot Assistant' that remembers who drank the lemonade, calculates the bill, and sends them a text message to pay you back.</p>
        </div>
    </section>

    <!-- "Robot Features" Grid -->
    <section class="py-20 px-4">
        <div class="container mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Card 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-md flex items-center">
                    <div class="mr-6">
                        <span class="material-symbols-outlined text-primary text-5xl">bar_chart</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">The Big Scoreboard</h3>
                        <p class="text-gray-600">See exactly how much money is out and how much is back. No calculator needed.</p>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-md flex items-center">
                    <div class="mr-6">
                        <span class="material-symbols-outlined text-primary text-5xl">chat</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">The Reminder Buddy</h3>
                        <p class="text-gray-600">We send the text messages and payment links automatically so you don't have to be the bad guy.</p>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-md flex items-center">
                    <div class="mr-6">
                        <span class="material-symbols-outlined text-primary text-5xl">shield</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">The Safety Guard</h3>
                        <p class="text-gray-600">We lock the door if a borrower has too much debt. We keep your capital safe.</p>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="bg-white p-8 rounded-2xl shadow-md flex items-center">
                    <div class="mr-6">
                        <span class="material-symbols-outlined text-primary text-5xl">badge</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Digital KYC</h3>
                        <p class="text-gray-600">We scan borrower IDs and remember their faces so you never get scammed.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- "Zero Training" Section -->
    <section class="py-20 px-4 bg-gray-50 text-center">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <div class="max-w-3xl mx-auto p-4 bg-white rounded-full shadow-lg flex items-center gap-4">
                    <span class="material-symbols-outlined text-primary">search</span>
                    <span class="text-gray-400">Search for 'Mr. Okafor' or 'Late Loans'...</span>
                </div>
            </div>
            <p class="text-xl md:text-2xl leading-relaxed">No complex menus. Just type 'Mr. Okafor' or 'Late Loans' in the search bar, and we find it instantly. If you can use Google, you can use this.</p>
        </div>
    </section>

    <!-- Pricing & Footer -->
    <footer class="bg-white text-center py-20 px-4">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">The power of a bank, for the price of Netflix.</h2>
        <a href="{{ route('register.org') }}" class="bg-primary text-white font-bold py-4 px-8 rounded-2xl text-lg hover:opacity-90 transition inline-block">Get Started Now - ₦50,000/month</a>
    </footer>

</body>
</html>
