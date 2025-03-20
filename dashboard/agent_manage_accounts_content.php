<div class="p-6">
    <h2 class="text-2xl font-bold text-[#092468]">Manage Bank Accounts</h2>

    <!-- ✅ Display Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            Swal.fire("Success!", "<?php echo $_SESSION['success']; ?>", "success");
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <script>
            Swal.fire("Error!", "<?php echo $_SESSION['error']; ?>", "error");
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- ✅ List Existing Accounts -->
    <h3 class="text-lg font-bold mt-6">Your Bank Accounts</h3>
    <table class="w-full mt-4 border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-3 border">Bank Name</th>
                <th class="p-3 border">Account Number</th>
                <th class="p-3 border">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($bank = $bank_accounts->fetch_assoc()): ?>
                <tr>
                    <td class="p-3 border"><?php echo $bank['bank_name']; ?></td>
                    <td class="p-3 border"><?php echo $bank['account_number']; ?></td>
                    <td class="p-3 border">
                        <a href="#" class="text-red-500 hover:text-red-700 delete-btn" data-id="<?php echo $bank['id']; ?>">
                            Delete
                        </a>

                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- ✅ Add New Account Form -->
    <h3 class="text-lg font-bold mt-6">Add New Bank Account</h3>
    <form method="POST" id="accountForm" class="mt-4">
        <label class="block text-gray-700">Select Bank</label>
        <select id="bank_code" name="bank_code" required class="w-full p-3 border rounded mt-2">
            <option value="">-- Select Bank --</option>
        </select>

        <label class="block text-gray-700 mt-4">Account Number</label>
        <input type="text" id="account_number" name="account_number" required class="w-full p-3 border rounded mt-2"
            onkeyup="verifyAccount()">

        <!-- <label class="block text-gray-700 mt-4">Bank Name</label> -->
        <input type="hidden" id="bank_name" name="bank_name" readonly
            class="w-full p-3 border rounded mt-2 bg-gray-100">

        <label class="block text-gray-700 mt-4">Account Name</label>
        <input type="text" id="account_name" name="account_name" readonly
            class="w-full p-3 border rounded mt-2 bg-gray-100">

        <button type="submit" name="add_account"
            class="bg-[#F4A124] text-white w-full py-3 rounded mt-4 hover:bg-[#d88b1c]">
            Add Account
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetchBanks(); // Fetch banks when page loads
        });

        function fetchBanks() {
            fetch('get_banks.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        let bankSelect = document.getElementById('bank_code');
                        data.banks.forEach(bank => {
                            let option = document.createElement('option');
                            option.value = bank.code;
                            option.textContent = bank.name;
                            bankSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.log('Error fetching banks:', error));
        }

        function verifyAccount() {
            let accountNumber = document.getElementById('account_number').value;
            let bankCode = document.getElementById('bank_code').value;
            let bankName = document.getElementById('bank_code').options[document.getElementById('bank_code').selectedIndex]
                .text;

            if (accountNumber.length === 10 && bankCode) { // Nigerian accounts are 10 digits
                fetch(`verify_account.php?account_number=${accountNumber}&bank_code=${bankCode}&bank_name=${bankName}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            document.getElementById('bank_name').value = data.bank_name;
                            document.getElementById('account_name').value = data.account_name;
                        } else {
                            document.getElementById('bank_name').value = "";
                            document.getElementById('account_name').value = "";
                            Swal.fire("Error", "Account verification failed. Please check the number.", "error");
                        }
                    })
                    .catch(error => console.log('Error:', error));
            }
        }
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".delete-btn").forEach(button => {
                button.addEventListener("click", function(event) {
                    event.preventDefault();
                    let deleteId = this.getAttribute("data-id");

                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "?delete_id=" + deleteId;
                        }
                    });
                });
            });
        });
    </script>



</div>