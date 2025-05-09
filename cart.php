<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$base_path = '/';

// Check login BEFORE includes
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

include 'includes/db_connect.php';

// 🧠 Load cart from DB if session is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['cart'] = [];

    $stmt = $conn->prepare("SELECT ci.product_id, ci.quantity, ci.color, p.name, p.price, p.image 
                            FROM cart_items ci 
                            JOIN products p ON ci.product_id = p.id 
                            WHERE ci.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $cart_key = $row['product_id'] . '_' . $row['color'];
        $_SESSION['cart'][$cart_key] = [
            'id' => $row['product_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'image' => $row['image'],
            'color' => $row['color'],
            'quantity' => $row['quantity']
        ];
    }
}

$cart = $_SESSION['cart'];

// ✅ Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['quantities'] as $product_key => $quantity) {
        if (isset($cart[$product_key])) {
            $_SESSION['cart'][$product_key]['quantity'] = max(1, (int)$quantity);

            // Sync to DB
            $parts = explode('_', $product_key);
            $pid = (int) $parts[0];
            $color = $parts[1] ?? 'default';

            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ? AND color = ?");
            $stmt->bind_param("iiis", $_SESSION['cart'][$product_key]['quantity'], $user_id, $pid, $color);
            $stmt->execute();
        }
    }
    header("Location: cart.php");
    exit();
}

// ✅ Handle remove
if (isset($_GET['remove'])) {
    $key = $_GET['remove'];
    if (isset($_SESSION['cart'][$key])) {
        $parts = explode('_', $key);
        $pid = (int) $parts[0];
        $color = $parts[1] ?? 'default';

        // Remove from DB
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ? AND color = ?");
        $stmt->bind_param("iis", $user_id, $pid, $color);
        $stmt->execute();

        // Remove from session
        unset($_SESSION['cart'][$key]);
    }
    header("Location: cart.php");
    exit();
}

include 'includes/navbar.php';
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <title>Your Cart - PishonServ</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>public/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
    body {
        background: #f5f7fa;
        color: #092468;
    }

    .btn-primary {
        background-color: #F4A124;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #d88b1c;
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(244, 161, 36, 0.3);
    }

    .btn-secondary {
        background-color: #092468;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #071a4d;
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(9, 36, 104, 0.3);
    }

    .content-start {
        padding-top: 5rem;
    }
    </style>
</head>

<body class="min-h-screen pt-28">
    <div class="container mx-auto py-12 px-4 sm:px-6 lg:px-16 mt-2">
        <h2 class="text-4xl font-bold text-center mb-10 text-[#092468]">Your Shopping Cart</h2>

        <?php if (empty($cart)): ?>
        <div class="bg-white p-6 rounded shadow text-center">
            <p>Your cart is empty.</p>
            <a href="interior_deco.php" class="text-blue-600 underline mt-4 inline-block">Go Shopping</a>
        </div>
        <?php else: ?>
        <form method="POST" id="cart-form">
            <div class="bg-white p-4 rounded shadow overflow-x-auto mt-2">
                <table class="min-w-[600px] w-full text-left">
                    <thead>
                        <tr class="border-b text-[#092468] font-semibold">
                            <th>Image</th>
                            <th>Name</th>
                            <th>Color</th> <!-- NEW -->
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $grand_total = 0; ?>
                        <?php foreach ($cart as $id => $item): ?>
                        <?php
                                $subtotal = $item['price'] * $item['quantity'];
                                $grand_total += $subtotal;

                                // Try extract color from the key (productId_color)
                                $parts = explode('_', $id);
                                $color_variant = $item['color'] ?? ($parts[1] ?? 'default');
                                ?>
                        <tr class="border-b">
                            <td><img src="<?php echo $item['image']; ?>" class="w-24 h-16 object-cover rounded"></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($color_variant)); ?></td> <!-- Color shown here -->
                            <td>₦<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <input type="number" name="quantities[<?php echo $id; ?>]"
                                    value="<?php echo $item['quantity']; ?>" min="1" class="w-16 p-1 border rounded">
                            </td>
                            <td>₦<?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <button type="button" onclick="confirmRemove('<?php echo $id; ?>')"
                                    class="text-red-600 hover:underline">Remove</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center mt-6 gap-4">
                <button type="submit" class="btn-secondary text-white px-6 py-2 rounded-lg font-semibold">
                    Update Quantities
                </button>
                <div class="text-right">
                    <p class="text-xl font-bold mb-2 text-[#092468]">
                        Total: ₦<?php echo number_format($grand_total, 2); ?>
                    </p>
                    <a href="checkout.php" class="btn-primary text-white px-6 py-2 rounded-lg font-semibold">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <?php
    try {
        if (file_exists('includes/footer.php')) {
            include 'includes/footer.php';
        } else {
            error_log('Missing footer.php');
        }
    } catch (Exception $e) {
        error_log('Footer error: ' . $e->getMessage());
    }
    ?>

    <!-- SweetAlert Remove Confirmation -->
    <script>
    // function confirmRemove(productId) {
    //     Swal.fire({
    //         title: 'Remove Item?',
    //         text: "Are you sure you want to remove this item from your cart?",
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#d33',
    //         cancelButtonColor: '#3085d6',
    //         confirmButtonText: 'Yes, remove it!',
    //         cancelButtonText: 'Cancel'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             window.location.href = `cart.php?remove=${productId}`;
    //         }
    //     });
    // }

    function confirmRemove(productKey) {
        Swal.fire({
            title: 'Remove Item?',
            text: "Are you sure you want to remove this item from your cart?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `cart.php?remove=${productKey}`;
            }
        });
    }
    </script>
</body>

</html>