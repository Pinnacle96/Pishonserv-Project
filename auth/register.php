<?php
session_start();
include '../includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PISHONSERV</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <?php include '../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-40">
        <h2 class="text-3xl font-bold text-center text-[#092468]">Register</h2>

        <form action="../process/register_process.php" method="POST" enctype="multipart/form-data"
            class="max-w-lg mx-auto mt-6 bg-white p-6 rounded-lg shadow-lg" id="registerForm" novalidate>

            <div class="mb-4">
                <input type="text" name="name" placeholder="First Name" required
                    class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#092468]"
                    value="<?php echo isset($_SESSION['form_data']['name']) ? htmlspecialchars($_SESSION['form_data']['name']) : ''; ?>">
            </div>
            <div class="mb-4">
                <input type="text" name="lname" placeholder="Last Name" required
                    class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#092468]"
                    value="<?php echo isset($_SESSION['form_data']['lname']) ? htmlspecialchars($_SESSION['form_data']['lname']) : ''; ?>">
            </div>
            <div class="mb-4">
                <input type="email" name="email" placeholder="Email" required
                    class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#092468]"
                    value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">
            </div>
            <div class="mb-4">
                <input type="text" name="phone" placeholder="Phone Number" required
                    class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#092468]"
                    value="<?php echo isset($_SESSION['form_data']['phone']) ? htmlspecialchars($_SESSION['form_data']['phone']) : ''; ?>">
            </div>
            <div class="mb-4">
                <input type="text" name="address" placeholder="Residential Address" required
                    class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#092468]"
                    value="<?php echo isset($_SESSION['form_data']['address']) ? htmlspecialchars($_SESSION['form_data']['address']) : ''; ?>">
            </div>
            <div class="mb-4">
                <input type="text" name="nin" id="nin" placeholder="National Identification Number (NIN)" required
                    maxlength="11"
                    class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#092468]"
                    pattern="[0-9]{11}" title="NIN must be exactly 11 digits"
                    value="<?php echo isset($_SESSION['form_data']['nin']) ? htmlspecialchars($_SESSION['form_data']['nin']) : ''; ?>">
            </div>
            <div class="mb-4">
                <input type="password" name="password" placeholder="Password" required
                    class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#092468]">
            </div>
            <div class="mb-4">
                <select name="role" id="role"
                    class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#092468]" required>
                    <option value="">Select Role</option>
                    <option value="buyer"
                        <?php echo isset($_SESSION['form_data']['role']) && $_SESSION['form_data']['role'] == 'buyer' ? 'selected' : ''; ?>>
                        Customer</option>
                    <option value="agent"
                        <?php echo isset($_SESSION['form_data']['role']) && $_SESSION['form_data']['role'] == 'agent' ? 'selected' : ''; ?>>
                        Agent</option>
                    <option value="owner"
                        <?php echo isset($_SESSION['form_data']['role']) && $_SESSION['form_data']['role'] == 'owner' ? 'selected' : ''; ?>>
                        Property Owner</option>
                    <option value="hotel_owner"
                        <?php echo isset($_SESSION['form_data']['role']) && $_SESSION['form_data']['role'] == 'hotel_owner' ? 'selected' : ''; ?>>
                        Hotel Owner</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="profile_image" class="block font-semibold">Profile Picture:</label>
                <input type="file" name="profile_image" id="profile_image"
                    class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#092468]">
            </div>

            <!-- MOU Agreement Section -->
            <div id="mou-section"
                class="mt-4 <?php echo !isset($_SESSION['form_data']['role']) || !in_array($_SESSION['form_data']['role'], ['agent', 'owner', 'hotel_owner']) ? 'hidden' : ''; ?>">
                <label class="block font-semibold mb-2 text-[#092468]">Memorandum of Understanding (MOU)</label>
                <div class="h-48 overflow-y-scroll border p-3 text-sm bg-gray-50 rounded">
                    <p>By registering as an <strong>Agent</strong>, <strong>Property Owner</strong>, or <strong>Hotel
                            Owner</strong> on <strong>PISHONSERV</strong>, you agree to abide by our terms & conditions.
                    </p>
                    <p>This MOU outlines your responsibility to provide accurate property details and maintain the
                        highest
                        standards of honesty, transparency, and integrity.</p>
                    <p>Failure to comply may result in account suspension or termination.</p>
                </div>
                <a href="../documents/pishonserv_mou_sample.pdf" target="_blank"
                    class="inline-block mt-3 bg-[#092468] text-white px-4 py-2 rounded hover:bg-[#051B47] text-sm">
                    View Full MOU Document
                </a>
                <div class="mt-3">
                    <input type="checkbox" name="agree_mou" id="mou_agree"
                        <?php echo isset($_SESSION['form_data']['agree_mou']) ? 'checked' : ''; ?>>
                    <label for="mou_agree" class="text-sm text-gray-700">I have read and agree to the MOU</label>
                </div>
                <input type="hidden" name="signed_name" id="mou_fullname">
            </div>

            <button type="submit"
                class="w-full bg-[#CC9933] text-white py-3 rounded hover:bg-[#d88b1c] mt-4">Register</button>

            <p class="text-center text-gray-600 mt-2">
                Already have an account?
                <a href="login.php" class="text-blue-500 font-semibold">Sign in</a>
            </p>
        </form>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?php echo addslashes($_SESSION['error']); ?>',
                confirmButtonColor: '#092468'
            });
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo addslashes($_SESSION['success']); ?>',
                confirmButtonColor: '#092468'
            });
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <script>
        // Show or Hide MOU Section based on Role
        document.getElementById('role').addEventListener('change', function() {
            const mouSection = document.getElementById('mou-section');
            const agreeMou = document.getElementById('mou_agree');
            if (['agent', 'owner', 'hotel_owner'].includes(this.value)) {
                mouSection.classList.remove('hidden');
                agreeMou.required = true;
            } else {
                mouSection.classList.add('hidden');
                agreeMou.required = false;
            }
        });

        // Auto Fill Full Name for MOU Signature
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            try {
                const fname = document.querySelector('input[name="name"]').value.trim();
                const lname = document.querySelector('input[name="lname"]').value.trim();
                document.getElementById('mou_fullname').value = fname + ' ' + lname;
            } catch (err) {
                console.error('Submit error:', err);
            }
        });

        // Restrict NIN Field to Digits Only
        document.getElementById('nin').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
        });

        // Trigger initial MOU section visibility based on selected role
        document.getElementById('role').dispatchEvent(new Event('change'));
    </script>

    <?php include '../includes/footer.php'; ?>
    <?php unset($_SESSION['form_data']); ?>
</body>

</html>