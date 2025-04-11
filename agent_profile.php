<?php
session_start();
include 'includes/db_connect.php';
include 'includes/navbar.php';

$agent_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($agent_id <= 0) {
    echo "<p class='text-center text-red-500 mt-10'>Invalid Agent ID provided.</p>";
    exit;
}

// Fetch Agent Details
$agent_stmt = $conn->prepare("SELECT name, email, phone, role, profile_image, created_at FROM users WHERE id = ?");
$agent_stmt->bind_param('i', $agent_id);
$agent_stmt->execute();
$agent_result = $agent_stmt->get_result();

if ($agent_result->num_rows === 0) {
    echo "<p class='text-center text-red-500 mt-10'>Agent not found.</p>";
    exit;
}

$agent = $agent_result->fetch_assoc();
$agent_image = (!empty($agent['profile_image'])) ? "public/uploads/" . htmlspecialchars($agent['profile_image']) : 'public/uploads/default.png';

// Fetch Agent Properties
$property_stmt = $conn->prepare("SELECT * FROM properties WHERE owner_id = ? AND admin_approved = 1 ORDER BY created_at DESC");
$property_stmt->bind_param('i', $agent_id);
$property_stmt->execute();
$property_result = $property_stmt->get_result();
?>

<section class="container mx-auto py-12 px-4 grid grid-cols-1 md:grid-cols-4 gap-8 mt-16">

    <!-- Agent Info -->
    <aside class="bg-white shadow-lg rounded-lg p-6">
        <div class="text-center">
            <img src="<?php echo $agent_image; ?>" alt="Agent Profile"
                class="w-32 h-32 rounded-full mx-auto object-cover mb-4">
            <h2 class="text-2xl font-bold text-[#092468]"><?php echo htmlspecialchars($agent['name']); ?></h2>
            <p class="text-sm text-gray-500 mb-1"><?php echo ucfirst(htmlspecialchars($agent['role'])); ?></p>
            <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($agent['email']); ?></p>
            <p class="text-gray-600 text-sm mt-1">📞 <?php echo htmlspecialchars($agent['phone']); ?></p>
            <p class="text-gray-400 text-xs mt-2">Joined: <?php echo date('M d, Y', strtotime($agent['created_at'])); ?>
            </p>
        </div>
    </aside>

    <!-- Properties by Agent -->
    <div class="md:col-span-3">
        <h3 class="text-2xl font-semibold text-[#092468] mb-4">
            Properties Listed by <?php echo htmlspecialchars($agent['name']); ?>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($property_result->num_rows > 0): ?>
                <?php while ($property = $property_result->fetch_assoc()): ?>
                    <?php
                    $images = explode(',', $property['images']);
                    $property_image = !empty($images[0]) ? "public/uploads/" . htmlspecialchars($images[0]) : "public/uploads/default.jpg";
                    ?>
                    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                        <img src="<?php echo $property_image; ?>" alt="Property Image" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h4 class="text-lg font-bold text-[#092468]"><?php echo htmlspecialchars($property['title']); ?>
                            </h4>
                            <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($property['location']); ?></p>
                            <p class="text-[#CC9933] font-semibold mt-1">₦<?php echo number_format($property['price'], 2); ?>
                            </p>
                            <a href="property.php?id=<?php echo $property['id']; ?>"
                                class="inline-block mt-2 text-sm text-white bg-[#CC9933] px-4 py-2 rounded hover:bg-[#d88b1c]">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500">No properties listed by this agent yet.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>