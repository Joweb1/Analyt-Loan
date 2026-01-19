<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyt Loan 2.0</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|google-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-white text-gray-900 font-sans">

    <!-- Hero Section -->
    <section class="text-center py-20 px-4">
        <h1 class="text-4xl md:text-6xl font-bold mb-4">The Self-Driving Operating System for Micro-Lenders.</h1>
        <p class="text-lg md:text-xl text-gray-600 mb-8">Making lending as simple as sending an email.</p>
        <div class="flex justify-center mb-10">
            <img src="https://placehold.co/800x400" alt="Analyt Loan 2.0 Dashboard" class="rounded-2xl shadow-lg">
        </div>
        <a href="#" class="bg-blue-600 text-white font-bold py-4 px-8 rounded-2xl text-lg hover:bg-blue-700 transition">Start Your Engine</a>
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
                        <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">The Big Scoreboard</h3>
                        <p class="text-gray-600">See exactly how much money is out and how much is back. No calculator needed.</p>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-md flex items-center">
                    <div class="mr-6">
                         <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">The Reminder Buddy</h3>
                        <p class="text-gray-600">We send the text messages and payment links automatically so you don't have to be the bad guy.</p>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-md flex items-center">
                    <div class="mr-6">
                        <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">The Safety Guard</h3>
                        <p class="text-gray-600">We lock the door if a borrower has too much debt. We keep your capital safe.</p>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="bg-white p-8 rounded-2xl shadow-md flex items-center">
                    <div class="mr-6">
                        <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6"></path></svg>
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
                <img src="https://placehold.co/800x100" alt="Omnibar Search" class="rounded-2xl shadow-lg mx-auto">
            </div>
            <p class="text-xl md:text-2xl leading-relaxed">No complex menus. Just type 'Mr. Okafor' or 'Late Loans' in the search bar, and we find it instantly. If you can use Google, you can use this.</p>
        </div>
    </section>

    <!-- Pricing & Footer -->
    <footer class="bg-white text-center py-20 px-4">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">The power of a bank, for the price of Netflix.</h2>
        <a href="#" class="bg-blue-600 text-white font-bold py-4 px-8 rounded-2xl text-lg hover:bg-blue-700 transition">Get Started Now - ₦50,000/month</a>
    </footer>

</body>
</html>
