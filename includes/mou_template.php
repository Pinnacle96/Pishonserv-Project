<?php
if (!isset($signed_name)) $signed_name = '_________________________';
if (!isset($role)) $role = 'user';
$role = strtolower($role);
$date = date('F j, Y');

$logo_path = realpath(__DIR__ . '/../public/images/logo.png');
$logo_data = ($logo_path && file_exists($logo_path))
    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path))
    : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PISHONSERV - MOU</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; line-height: 1.6; color: #222; padding: 40px; }
        h1, h2, h3 { color: #092468; }
        h1 { text-align: center; font-size: 24px; margin-bottom: 20px; }
        h3 { margin-top: 20px; font-size: 16px; }
        p, ul { margin-bottom: 15px; }
        ul { padding-left: 20px; }
        .signature { margin-top: 50px; }
        .footer { margin-top: 60px; font-size: 12px; color: #777; text-align: center; }
        .logo { text-align: center; margin-bottom: 20px; }
        .logo img { height: 80px; }
    </style>
</head>
<body>

<div class="logo">
    <?php if ($logo_data): ?>
        <img src="<?= $logo_data ?>" alt="PISHONSERV Logo">
    <?php else: ?>
        <h2>PISHONSERV</h2>
    <?php endif; ?>
</div>

<h1>Memorandum of Understanding (MOU)</h1>

<p>
    This Memorandum of Understanding ("MOU") is made on <strong><?= $date ?></strong> between 
    <strong><?= ucwords($signed_name) ?></strong> (the "<?= ucfirst($role) ?>") and <strong>PISHONSERV</strong>, 
    an organization committed to providing trusted property solutions.
</p>

<h3>Purpose</h3>
<p>This MOU outlines the responsibilities and obligations of the <?= ucfirst($role) ?> in their relationship with PISHONSERV.</p>

<h3>General Terms</h3>
<ul>
    <li>The <?= ucfirst($role) ?> agrees to operate with integrity, transparency, and professionalism.</li>
    <li>All property data or interactions must reflect accurate, up-to-date, and verifiable information.</li>
    <li>The <?= ucfirst($role) ?> shall comply with all relevant local and platform policies.</li>
</ul>

<?php if ($role === 'agent'): ?>
    <h3>Agent-Specific Responsibilities</h3>
    <ul>
        <li>Ensure properties listed are genuine and represented with owner consent.</li>
        <li>Promptly respond to client inquiries and bookings.</li>
        <li>Coordinate viewings and maintain positive client-agent interactions.</li>
    </ul>
<?php elseif ($role === 'owner'): ?>
    <h3>Property Owner Responsibilities</h3>
    <ul>
        <li>Provide complete and truthful property details (pricing, facilities, availability).</li>
        <li>Ensure that properties listed are yours to manage, lease, or sell.</li>
        <li>Ensure property is accessible and in suitable condition when listed.</li>
    </ul>
<?php elseif ($role === 'hotel_owner'): ?>
    <h3>Hotel Owner Responsibilities</h3>
    <ul>
        <li>Ensure all room availability, pricing, and amenities are up to date.</li>
        <li>Provide excellent guest experiences as a PISHONSERV partner hotel.</li>
        <li>Comply with hospitality regulations and tax obligations.</li>
    </ul>
<?php elseif ($role === 'developer'): ?>
    <h3>Developer Responsibilities</h3>
    <ul>
        <li>Ensure that all estates or buildings listed are approved and legally registered.</li>
        <li>Provide buyers with correct and clear information (e.g., title, delivery timelines).</li>
        <li>Handle customer communication ethically during the construction and sales process.</li>
    </ul>
<?php endif; ?>

<h3>Confidentiality</h3>
<p>Both parties agree to maintain confidentiality of any sensitive information or personal data exchanged on the platform.</p>

<h3>Duration and Termination</h3>
<p>This MOU shall remain in effect unless either party terminates it in writing. Breach of terms may result in suspension or termination of platform access.</p>

<div class="signature">
    <p><strong>Signed:</strong> <?= ucwords($signed_name) ?></p>
    <p><strong>Role:</strong> <?= ucfirst($role) ?></p>
    <p><strong>Date:</strong> <?= $date ?></p>
</div>

<div class="footer">
    <p>PISHONSERV | Integrity. Excellence. Trust</p>
    <p>www.pishonserv.com | inquiry@pishonserv.com</p>
</div>

</body>
</html>
