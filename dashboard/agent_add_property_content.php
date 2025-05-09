<?php
// ✅ CSRF token setup
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (!function_exists('csrf_token_input')) {
    function csrf_token_input() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
    }
}
?>

<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Add New Property</h2>
    <form action="../process/agent_add_property.php" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">

        <?php echo csrf_token_input(); ?>
        <input type="hidden" name="MAX_FILE_SIZE" value="20971520">

        <!-- Basic Fields -->
        <div class="mb-4">
            <label class="block" id="title-label">Title</label>
            <input type="text" name="title" id="title-input" required class="w-full p-3 border rounded mt-1" placeholder="e.g., Luxurious Apartment">
        </div>

        <div class="mb-4">
            <label class="block">Price (₦)</label>
            <input type="number" id="price" name="price" required class="w-full p-3 border rounded mt-1" placeholder="e.g., 5000000">
        </div>

        <div class="mb-4">
            <label class="block">Location</label>
            <input type="text" name="location" required class="w-full p-3 border rounded mt-1">
        </div>

        <div class="mb-4">
    <label class="block">Listing Type</label>
    <select name="listing_type" id="listing_type" required class="w-full p-3 border rounded mt-1">
        <option value="">Select</option>
        <option value="for_sale">For Sale</option>
        <option value="for_rent">For Rent</option>
        <option value="short_let">Short Let</option>
        <option value="hotel">Hotel</option>
    </select>
</div>

<div class="mb-4">
    <label class="block">Property Type</label>
    <select name="type" required class="w-full p-3 border rounded mt-1">
        <option value="">Select</option>
        <option value="apartment">Apartment</option>
        <option value="office">Office</option>
        <option value="event_center">Event Center</option>
        <option value="hotel">Hotel</option>
        <option value="short_stay">Short Stay</option>
        <option value="house">House</option>
        <option value="villa">Villa</option>
        <option value="condo">Condo</option>
        <option value="townhouse">Townhouse</option>
        <option value="duplex">Duplex</option>
        <option value="penthouse">Penthouse</option>
        <option value="studio">Studio</option>
        <option value="bungalow">Bungalow</option>
        <option value="commercial">Commercial</option>
        <option value="warehouse">Warehouse</option>
        <option value="retail">Retail</option>
        <option value="land">Land</option>
        <option value="farmhouse">Farmhouse</option>
        <option value="mixed_use">Mixed Use</option>
    </select>
</div>

        <!-- Global Fields -->
        <div class="mb-4">
    <label class="block">Amenities</label>
    <div class="flex flex-wrap">
        <hr class='my-6 w-full'>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Pool"> Pool</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Gym"> Gym</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Parking"> Parking</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Security"> Security</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Garden"> Garden</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Elevator"> Elevator</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Balcony"> Balcony</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="CCTV"> CCTV</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Internet"> Internet</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Air Conditioning"> Air Conditioning</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Fireplace"> Fireplace</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Washer/Dryer"> Washer/Dryer</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Generator"> Generator</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Solar Power"> Solar Power</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Borehole Water"> Borehole Water</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Playground"> Playground</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Clubhouse"> Clubhouse</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Tennis Court"> Tennis Court</label>
        <label class="w-1/3 p-2"><input type="checkbox" name="amenities[]" value="Sauna"> Sauna</label>
    </div>
</div>

        <div class="mb-4">
            <label class="block">Furnishing Status <small class='text-gray-500'>(e.g., Fully Furnished Apartment)</small></label>
            <select name="furnishing_status" class="w-full p-3 border rounded mt-1">
                <option value="">Select</option>
                <option value="furnished">Furnished</option>
                <option value="semi_furnished">Semi-Furnished</option>
                <option value="unfurnished">Unfurnished</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block">Property Condition <small class='text-gray-500'>(e.g., Recently Renovated)</small></label>
            <select name="property_condition" class="w-full p-3 border rounded mt-1">
                <option value="">Select</option>
                <option value="new">New</option>
                <option value="fairly_used">Fairly Used</option>
                <option value="renovated">Renovated</option>
            </select>
        </div>

        <!-- Final Global Fields -->
        <div class="mb-4">
            <label class="block">Bedrooms</label>
            <input type="number" name="bedrooms" required min="0" class="w-full p-3 border rounded mt-1" placeholder="e.g., 3">
        </div>

        <div class="mb-4">
            <label class="block">Bathrooms</label>
            <input type="number" name="bathrooms" required min="0" class="w-full p-3 border rounded mt-1" placeholder="e.g., 2">
        </div>

        <div class="mb-4">
            <label class="block">Size (sqft or acres)</label>
            <input type="text" name="size" required class="w-full p-3 border rounded mt-1" placeholder="e.g., 2000 sqft or 5 acres">
        </div>

        <div class="mb-4">
    <label class="block">Garage Spaces</label>
    <input type="number" name="garage" required min="0" class="w-full p-3 border rounded mt-1" placeholder="e.g., 1">
</div>

<div class="mb-4">
    <label class="block">Description</label>
    <textarea name="description" required minlength="20" class="w-full p-3 border rounded mt-1" placeholder="Provide a detailed description of the property..." rows="4" class="w-full p-3 border rounded mt-1" placeholder="Describe the property features..."></textarea>
</div>

<div class="mb-4">
            <label class="block">Upload Property Images <small class="text-gray-500">(Max 7 images)</small></label>
            <input type="file" name="images[]" id="images" multiple required accept="image/*" class="w-full p-3 border rounded mt-1">
            <small class="text-gray-500">Images will be auto-compressed. Accepted: JPG, PNG, GIF.</small>
            <div id="image-preview" class="mt-2 flex flex-wrap gap-2"></div>
        </div>

        <!-- Dynamic Fields Placeholder -->
        <div id="dynamic-fields"></div>

        <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c] mt-4">
            Add Property
        </button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const furnishingField = document.querySelector("select[name='furnishing_status']").closest(".mb-4");
    const conditionField = document.querySelector("select[name='property_condition']").closest(".mb-4");
    const bedroomsField = document.querySelector("input[name='bedrooms']").closest(".mb-4");
    const bathroomsField = document.querySelector("input[name='bathrooms']").closest(".mb-4");
    const sizeField = document.querySelector("input[name='size']").closest(".mb-4");
    const garageField = document.querySelector("input[name='garage']").closest(".mb-4");
    const listingType = document.getElementById("listing_type");
    const priceInput = document.getElementById("price");
    const titleLabel = document.getElementById("title-label");
    const titleInput = document.getElementById("title-input");

    function updateDynamicFields(type) {
        const container = document.getElementById("dynamic-fields");
        let html = "";

        if (type === "for_sale" || type === "for_rent") {
            html += `
            <div class='mb-4'><label>Maintenance Fee (₦)</label><input type='number' name='maintenance_fee' class='w-full p-3 border rounded mt-1'></div>
            <div class='mb-4'><label>Agent Fee (₦)</label><input type='number' id='agent_fee' name='agent_fee' class='w-full p-3 border rounded mt-1' readonly></div>
            <div class='mb-4'><label>Caution Fee (₦)</label><input type='number' id='caution_fee' name='caution_fee' class='w-full p-3 border rounded mt-1' readonly></div>`;
        }

        if (type === "for_rent" || type === "short_let" || type === "hotel") {
            html += `<div class='mb-4'><label>Price Frequency</label><select name='price_frequency' class='w-full p-3 border rounded mt-1'><option value=''>Select</option><option value='per_day'>Per Day</option><option value='per_night'>Per Night</option><option value='per_month'>Per Month</option><option value='per_annum'>Per Annum</option></select></div>`;
        }

        if (type === "short_let") {
            html += `<div class='mb-4'><label>Minimum Stay (nights)</label><input type='number' name='minimum_stay' class='w-full p-3 border rounded mt-1'></div><div class='mb-4'><label>Check-in Time</label><input type='time' name='checkin_time' class='w-full p-3 border rounded mt-1'></div><div class='mb-4'><label>Check-out Time</label><input type='time' name='checkout_time' class='w-full p-3 border rounded mt-1'></div>`;
        }

        if (type === "hotel") {
            html += `<div class='mb-4'><label>Room Type</label><input type='text' name='room_type' class='w-full p-3 border rounded mt-1'></div><div class='mb-4'><label>Star Rating <small class='text-gray-500'>(1 = Basic, 5 = Luxury)</small></label><select name='star_rating' class='w-full p-3 border rounded mt-1'><option value=''>Select</option><option value='1'>1 Star</option><option value='2'>2 Stars</option><option value='3'>3 Stars</option><option value='4'>4 Stars</option><option value='5'>5 Stars</option></select></div><div class='mb-4'><label>Check-in Time</label><input type='time' name='checkin_time' class='w-full p-3 border rounded mt-1'></div><div class='mb-4'><label>Check-out Time</label><input type='time' name='checkout_time' class='w-full p-3 border rounded mt-1'></div><div class='mb-4'><label>Policies</label><textarea name='policies' class='w-full p-3 border rounded mt-1'></textarea></div>`;
        }

        container.innerHTML = html;
    }

    listingType.addEventListener("change", function() {
        if (listingType.value === "hotel") {
            titleLabel.textContent = "Hotel Name";
            titleInput.placeholder = "e.g., Sheraton Lagos Hotel";
        } else {
            titleLabel.textContent = "Title";
            titleInput.placeholder = "e.g., Luxurious Apartment";
        }
        updateDynamicFields(listingType.value);

        // Hide/Show global fields for hotel and short_let
        if (listingType.value === "hotel") {
            furnishingField.style.display = "none";
            conditionField.style.display = "none";
            bedroomsField.style.display = "none";
            bathroomsField.style.display = "none";
            sizeField.style.display = "none";
            garageField.style.display = "none";
        } else if (listingType.value === "short_let") {
            furnishingField.style.display = "";
            conditionField.style.display = "";
            bedroomsField.style.display = "";
            bathroomsField.style.display = "";
            sizeField.style.display = "none";
            garageField.style.display = "";
        } else {
            furnishingField.style.display = "";
            conditionField.style.display = "";
            bedroomsField.style.display = "";
            bathroomsField.style.display = "";
            sizeField.style.display = "";
            garageField.style.display = "";
        }
    });

    priceInput.addEventListener("input", function() {
        if (["for_sale", "for_rent"].includes(listingType.value)) {
            const price = parseFloat(priceInput.value) || 0;
            document.getElementById("agent_fee").value = (price * 0.1).toFixed(2);
            document.getElementById("caution_fee").value = (price * 0.1).toFixed(2);
        }
    });
    document.getElementById('images').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            const files = e.target.files;
            if (files.length > 7) {
                alert('You can upload a maximum of 7 images.');
                e.target.value = '';
                return;
            }
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (!file.type.startsWith('image/')) continue;
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.classList.add('w-20', 'h-20', 'object-cover', 'rounded', 'border', 'border-gray-300');
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });
});
</script>
