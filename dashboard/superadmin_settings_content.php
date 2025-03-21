<div class="mt-6">
    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-gray-200">System Settings</h2>
    <p class="text-gray-600 dark:text-gray-400 mt-1">Manage commission rates, user limits, and site status.</p>

    <form action="superadmin_settings.php" method="POST"
        class="bg-white dark:bg-gray-800 mt-6 p-6 rounded-lg shadow-md">
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300 font-semibold">Commission Rate (%)</label>
            <input type="number" step="0.01" min="0" max="100" name="commission"
                value="<?php echo htmlspecialchars($settings['commission'] ?? 0.00); ?>" required
                class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                placeholder="e.g., 5.50">
            <small class="text-gray-500 dark:text-gray-400">Enter a value between 0 and 100.</small>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300 font-semibold">Max Users Allowed</label>
            <input type="number" min="1" name="max_users"
                value="<?php echo htmlspecialchars($settings['max_users'] ?? 1000); ?>" required
                class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                placeholder="e.g., 1000">
            <small class="text-gray-500 dark:text-gray-400">Set the maximum number of users allowed.</small>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 dark:text-gray-300 font-semibold">Site Status</label>
            <select name="site_status"
                class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200">
                <option value="active"
                    <?php echo ($settings['site_status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="maintenance"
                    <?php echo ($settings['site_status'] ?? 'active') === 'maintenance' ? 'selected' : ''; ?>>
                    Maintenance</option>
                <option value="inactive"
                    <?php echo ($settings['site_status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>Inactive
                </option>
            </select>
            <small class="text-gray-500 dark:text-gray-400">Choose the current site status.</small>
        </div>

        <button type="button" id="saveSettingsBtn"
            class="bg-[#F4A124] text-white w-full py-3 rounded-lg hover:bg-[#d88b1c] focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200">
            Save Changes
        </button>
    </form>
</div>

<!-- SweetAlert2 Library (only if not in navbar.php or layout) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- JavaScript: SweetAlert Confirm Save -->
<script>
    document.getElementById('saveSettingsBtn').addEventListener('click', function() {
        Swal.fire({
            title: "Save Changes?",
            text: "Are you sure you want to update system settings?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#092468", // Match site theme
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, save it!",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector("form").submit();
                Swal.fire({
                    title: "Saving...",
                    text: "Please wait while settings are updated.",
                    icon: "info",
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
        });
    });
</script>