<?php
// Ensure session is started (header.php should do this)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$page_title = "Contact Us - Rose & Gold";
require_once 'header.php';

$form_message = '';
$form_error = '';
$form_name = '';
$form_email = '';
$form_subject = '';
$form_enquiry = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $form_name = trim($_POST['name']);
    $form_email = trim($_POST['email']);
    $form_subject = trim($_POST['subject']);
    $form_enquiry = trim($_POST['enquiry']);

    if (empty($form_name) || empty($form_email) || empty($form_subject) || empty($form_enquiry)) {
        $form_error = "All fields in the contact form are required.";
    } elseif (!filter_var($form_email, FILTER_VALIDATE_EMAIL)) {
        $form_error = "Please enter a valid email address.";
    } else {
        // Simulate sending email
        // In a real application, you would use mail() function or a library like PHPMailer
        // For now, just display a success message.
        $mail_to = "contact@roseandgold.example"; // Your email
        $mail_subject = "Contact Form Submission: " . $form_subject;
        $mail_body = "Name: " . $form_name . "\n";
        $mail_body .= "Email: " . $form_email . "\n";
        $mail_body .= "Subject: " . $form_subject . "\n";
        $mail_body .= "Message:\n" . $form_enquiry . "\n";
        $mail_headers = "From: " . $form_email;

        // mail($mail_to, $mail_subject, $mail_body, $mail_headers); // Uncomment for actual email sending

        $form_message = "Thank you for your message, " . htmlspecialchars($form_name) . "! We will get back to you shortly.";
        // Clear form fields after successful "submission"
        $form_name = $form_email = $form_subject = $form_enquiry = '';
    }
}
?>

<main class="content-area contact-page">
    <section class="page-title-section animated-section">
        <h1>Get In Touch</h1>
        <p class="subtitle">We'd love to hear from you. Reach out with any questions or enquiries.</p>
    </section>

    <?php if ($form_message): ?>
        <div class="message success animated-section"><?php echo $form_message; ?></div>
    <?php endif; ?>
    <?php if ($form_error): ?>
        <div class="message error animated-section"><?php echo $form_error; ?></div>
    <?php endif; ?>

    <div class="contact-content-wrapper animated-section">
        <section class="content-section contact-form-section">
            <h3>Send Us a Message</h3>
            <form action="contact.php" method="POST">
                <div class="form-group">
                    <label for="name">Your Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($form_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Your Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form_email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($form_subject); ?>" required>
                </div>
                <div class="form-group">
                    <label for="enquiry">Your Message:</label>
                    <textarea id="enquiry" name="enquiry" rows="6" required><?php echo htmlspecialchars($form_enquiry); ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" name="send_message" class="btn-primary">Send Message</button>
                </div>
            </form>
        </section>

        <section class="content-section contact-details-section">
            <h3>Our Contact Information</h3>
            <p><strong>Rose & Gold Creations</strong></p>
            <p><span class="contact-icon">&#128205;</span> 123 Elegance Avenue, Suite 4B<br>Roseville, RGC 54321</p>
            <p><span class="contact-icon">&#128222;</span> +1 (234) 567-8900</p>
            <p><span class="contact-icon">&#128231;</span> <a href="mailto:info@roseandgold.example">info@roseandgold.example</a></p>
            
            <h4>Business Hours</h4>
            <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
            <p>Saturday: 10:00 AM - 4:00 PM</p>
            <p>Sunday: Closed</p>

            <div class="map-placeholder">
                <p><em>(Embedded Map Placeholder - e.g., Google Maps iframe)</em></p>
                <img src="https://via.placeholder.com/400x250.png?text=Map+to+Rose+&+Gold+Creations" alt="Map Placeholder" style="width:100%; max-width:400px; height:auto; border:1px solid #ddd;">
            </div>
        </section>
    </div>
</main>

<?php require_once 'footer.php'; ?>
