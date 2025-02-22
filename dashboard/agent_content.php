<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Welcome, <?php echo $_SESSION['name']; ?></h2>
    <p class="text-gray-600 dark:text-gray-400">Manage your listings, inquiries, and earnings.</p>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Total Properties</h3>
            <p class="text-2xl font-bold">8</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Pending Inquiries</h3>
            <p class="text-2xl font-bold">3</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Total Earnings</h3>
            <p class="text-2xl font-bold">$15,250</p>
        </div>
    </div>

    <!-- Recent Inquiries -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Recent Inquiries</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-200 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                        <th class="p-3 border">Buyer</th>
                        <th class="p-3 border">Property</th>
                        <th class="p-3 border">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="p-3 border">John Doe</td>
                        <td class="p-3 border">Luxury Apartment</td>
                        <td class="p-3 border text-green-500">Interested</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>