<?php
require 'includes/db_connect.php'; // Your DB connection
require 'includes/config.php';   // Contains LOCATIONIQ_API_KEY

// Function to get coordinates from LocationIQ
function getLocationCoordinates($location, $api_key)
{
    $url = "https://api.locationiq.com/v1/autocomplete.php?key=" . $api_key . "&q=" . urlencode($location) . "&limit=1";
    $response = @file_get_contents($url);
    if ($response === false) {
        echo "Failed to fetch coordinates for: $location\n";
        return null;
    }
    $data = json_decode($response, true);
    if ($data && !empty($data)) {
        return ['lat' => $data[0]['lat'], 'lon' => $data[0]['lon']];
    }
    echo "No coordinates found for: $location\n";
    return null;
}

// Fetch all properties without coordinates
$query = "SELECT id, location FROM properties WHERE latitude IS NULL OR longitude IS NULL";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "Updating " . $result->num_rows . " properties...\n";

    // Prepare update statement
    $stmt = $conn->prepare("UPDATE properties SET latitude = ?, longitude = ? WHERE id = ?");
    $stmt->bind_param("ddi", $lat, $lon, $id);

    while ($property = $result->fetch_assoc()) {
        $id = $property['id'];
        $location = $property['location'];

        echo "Processing: $location (ID: $id)\n";

        // Get coordinates
        $coords = getLocationCoordinates($location, LOCATIONIQ_API_KEY);
        if ($coords) {
            $lat = $coords['lat'];
            $lon = $coords['lon'];

            // Update the database
            if ($stmt->execute()) {
                echo "Updated: $location -> Lat: $lat, Lon: $lon\n";
            } else {
                echo "Database update failed for ID $id: " . $stmt->error . "\n";
            }

            // Rate limit: LocationIQ free tier allows 2 requests/second
            sleep(1); // Wait 1 second between requests
        } else {
            echo "Skipping ID $id due to no coordinates.\n";
        }
    }

    $stmt->close();
} else {
    echo "No properties need updating.\n";
}

$conn->close();
echo "Update complete.\n";
