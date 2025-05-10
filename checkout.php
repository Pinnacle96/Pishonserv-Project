<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$base_path = '/';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    $_SESSION['error'] = "Your cart is empty.";
    header("Location: cart.php");
    exit();
}

include 'includes/db_connect.php';
include 'includes/navbar.php';

// Get delivery fees from DB
$delivery_result = $conn->query("SELECT state, fee FROM delivery_fees ORDER BY state ASC");
$delivery_fees = [];
while ($row = $delivery_result->fetch_assoc()) {
    $delivery_fees[$row['state']] = $row['fee'];
}

// Calculate subtotal
$subtotal = array_sum(array_map(function ($item) {
    return $item['price'] * $item['quantity'];
}, $cart));

function log_payment_error($message)
{
    $log_file = __DIR__ . '/payment/payment_error.log';
    $date = date('[Y-m-d H:i:s]');
    file_put_contents($log_file, "$date $message\n", FILE_APPEND);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Checkout - PishonServ</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo $base_path; ?>public/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
</head>

<body class="min-h-screen bg-gray-50 pt-28">
    <div class="container mx-auto px-4 lg:px-16 py-10">
        <h1 class="text-4xl font-bold text-[#092468] mb-6">Checkout</h1>

        <form id="checkout-form" onsubmit="initiatePayment(event)">
            <div class="grid md:grid-cols-2 gap-10">
                <!-- Delivery Info -->
                <div class="bg-white p-6 shadow rounded">
                    <h2 class="text-2xl font-semibold mb-4 text-[#092468]">Delivery Information</h2>
                    <input type="text" name="delivery_name" placeholder="Full Name" required
                        class="w-full mb-3 p-2 border rounded">
                    <input type="text" name="delivery_phone" placeholder="Phone Number" required
                        class="w-full mb-3 p-2 border rounded">
                    <input type="email" name="delivery_email" placeholder="Email (optional)"
                        class="w-full mb-3 p-2 border rounded">
                    <input type="text" name="delivery_address" placeholder="Address" required
                        class="w-full mb-3 p-2 border rounded">
                    <input type="text" name="delivery_city" placeholder="City" required
                        class="w-full mb-3 p-2 border rounded">
                    <select name="delivery_state" id="delivery_state" required class="w-full mb-3 p-2 border rounded"
                        onchange="updateDeliveryFee()">
                        <option value="">Select State</option>
                        <?php foreach ($delivery_fees as $state => $fee): ?>
                            <option value="<?php echo $state; ?>" data-fee="<?php echo $fee; ?>"><?php echo $state; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="delivery_country" placeholder="Country" value="Nigeria" required
                        class="w-full mb-3 p-2 border rounded">
                </div>

                <!-- Payment -->
                <div class="bg-white p-6 shadow rounded">
                    <h2 class="text-2xl font-semibold mb-4 text-[#092468]">Select Payment Method</h2>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <?php
                        $gateways = [
                            "paystack" => "paystack.png",
                            "flutterwave" => "flutterwave.png"
                            //"opay" => "opay.png",
                            //"palmpay" => "palmpay.png"
                        ];
                        foreach ($gateways as $key => $icon): ?>
                            <label
                                class="border rounded-lg p-3 flex items-center justify-center cursor-pointer bg-white shadow hover:ring-2 hover:ring-[#F4A124] transition-all">
                                <input type="radio" name="payment_method" value="<?php echo $key; ?>" class="hidden peer">
                                <img src="<?php echo $base_path . 'public/icons/' . $icon; ?>"
                                    alt="<?php echo ucfirst($key); ?>" class="h-10 peer-checked:scale-110 transition">
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-6 text-lg text-[#092468]">
                        <p class="mb-2 font-semibold">Cart Total: ₦<?php echo number_format($subtotal, 2); ?></p>
                        <p class="mb-2 font-semibold">Delivery Fee: ₦<span id="delivery-fee">0</span></p>
                        <input type="hidden" id="delivery_fee_input" name="delivery_fee" value="0">
                        <hr class="my-2">
                        <p class="text-xl font-bold">Total: ₦<span
                                id="grand-total"><?php echo number_format($subtotal, 2); ?></span></p>
                    </div>

                    <input type="hidden" name="order_description" value="Interior Product Payment">

                    <button type="submit"
                        class="mt-6 bg-[#F4A124] hover:bg-[#d88b1c] text-white px-6 py-3 rounded font-semibold w-full">
                        Pay Now
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php try {
        if (file_exists('includes/footer.php')) include 'includes/footer.php';
    } catch (Exception $e) {
        error_log($e->getMessage());
    } ?>
    <script src="https://checkout.flutterwave.com/v3.js"></script>
    <script>
        const baseSubtotal = <?php echo $subtotal; ?>;

        function updateDeliveryFee() {
            const selected = document.getElementById("delivery_state").selectedOptions[0];
            const fee = parseInt(selected.dataset.fee || 0);
            document.getElementById("delivery-fee").textContent = fee.toLocaleString();
            document.getElementById("delivery_fee_input").value = fee;
            document.getElementById("grand-total").textContent = (baseSubtotal + fee).toLocaleString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.querySelectorAll('input[name="payment_method"]');
            const form = document.getElementById('checkout-form');

            radios.forEach(input => {
                input.addEventListener('change', function() {
                    document.querySelectorAll('label').forEach(label =>
                        label.classList.remove('ring-2', 'ring-[#F4A124]')
                    );
                    this.closest('label').classList.add('ring-2', 'ring-[#F4A124]');
                });
            });

            form.addEventListener('submit', function(e) {
                const selected = document.querySelector('input[name="payment_method"]:checked');
                if (!selected) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Select Payment Method',
                        text: 'Please choose a payment option before continuing.',
                        confirmButtonColor: '#F4A124'
                    });
                }
            });
        });

        function initiatePayment(event) {
            event.preventDefault();

            const form = document.getElementById('checkout-form');
            const formData = new FormData(form);

            fetch('payment/process_payment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    console.log('Payment Response:', data);

                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Payment initialization failed.'
                        });
                        return;
                    }

                    if (data.payment_method === 'paystack') {
                        const handler = PaystackPop.setup({
                            key: data.public_key,
                            email: data.email,
                            amount: data.amount, // in kobo
                            currency: "NGN",
                            ref: data.reference,
                            metadata: {
                                custom_fields: [{
                                    display_name: "Customer Name",
                                    variable_name: "delivery_name",
                                    value: data.name || 'Guest'
                                }]
                            },
                            callback: function(response) {
                                window.location.href = 'payment/success_callback.php?ref=' +
                                    encodeURIComponent(response.reference);
                            },
                            onClose: function() {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Payment Cancelled',
                                    text: 'You cancelled the payment.'
                                });
                            }
                        });
                        handler.openIframe();
                    } else if (data.payment_method === 'flutterwave') {
                        if (!data.public_key || !data.reference) {
                            console.error("Missing required Flutterwave values", data);
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid Payment Info',
                                text: 'Flutterwave credentials are incomplete or invalid.'
                            });
                            return;
                        }

                        const flutterAmount = parseFloat(data.amount); // amount already in Naira

                        console.log("Flutterwave Payload", {
                            key: data.public_key,
                            tx_ref: data.reference,
                            amount: flutterAmount,
                            email: data.email,
                            name: data.name
                        });

                        try {
                            FlutterwaveCheckout({
                                public_key: data.public_key,
                                tx_ref: data.reference,
                                amount: flutterAmount,
                                currency: "NGN",
                                customer: {
                                    email: data.email,
                                    name: data.name || 'Guest',
                                },
                                customizations: {
                                    title: "PishonServ Payment",
                                    description: data.description,
                                    logo: "https://pishonserv.com/public/images/logo.png"
                                },
                                callback: function(response) {
                                    if (response.status === "successful") {
                                        window.location.href = 'payment/success_callback.php?ref=' +
                                            encodeURIComponent(response.tx_ref);
                                    } else {
                                        window.location.href = 'payment/failure_callback.php?ref=' +
                                            encodeURIComponent(response.tx_ref);
                                    }
                                },
                                onclose: function() {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Payment Cancelled',
                                        text: 'You cancelled the payment.'
                                    });
                                }
                            });
                        } catch (err) {
                            console.error("⚠️ FlutterwaveCheckout failed to initialize", err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Flutterwave Init Error',
                                text: err.message || 'Something went wrong initializing payment.'
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Unsupported Method',
                            text: 'This payment method is not yet implemented.'
                        });
                    }
                })
                .catch((err) => {
                    console.error('Payment Init Error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Unable to connect to server.'
                    });
                });
        }
    </script>

</body>

</html>