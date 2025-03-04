<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">System Settings</h2>

    <form action="" method="POST" class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Commission Rate (%)</label>
            <input type="number" step="0.01" name="commission" value="<?php echo $settings['commission'] ?? 0; ?>"
                required class="w-full p-3 border rounded mt-1">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Max Users Allowed</label>
            <input type="number" name="max_users" value="<?php echo $settings['max_users'] ?? 1000; ?>" required
                class="w-full p-3 border rounded mt-1">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Site Status</label>
            <select name="site_status" class="w-full p-3 border rounded mt-1">
                <option value="active"
                    <?php echo ($settings['site_status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="maintenance"
                    <?php echo ($settings['site_status'] ?? 'active') === 'maintenance' ? 'selected' : ''; ?>>
                    Maintenance</option>
                <option value="inactive"
                    <?php echo ($settings['site_status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>Inactive
                </option>
            </select>

        </div>
        <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">Save
            Changes</button>
    </form>
</div>