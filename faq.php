<?php
$pageTitle = 'FAQ';
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

// Get FAQs from database
$faqs = $conn->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY created_at DESC");
?>

<!-- Mobile AppBar -->
<nav class="navbar navbar-dark bg-dark d-md-none">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><?php echo SITE_NAME; ?></a>
    </div>
</nav>

<!-- Desktop Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark d-none d-md-block">
    <div class="container">
        <a class="navbar-brand" href="index.php"><?php echo SITE_NAME; ?></a>
        <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#navbarNav">
            <i class="fas fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                <li class="nav-item"><a class="nav-link active" href="faq.php">FAQ</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="fw-bold mb-4 text-center">Frequently Asked Questions</h1>
            <p class="text-muted text-center mb-5">Find answers to common questions about our products and services.</p>
            
            <?php if ($faqs->num_rows > 0): ?>
                <div class="accordion" id="faqAccordion">
                    <?php $index = 0; while ($faq = $faqs->fetch_assoc()): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-mdb-toggle="collapse" data-mdb-target="#collapse<?php echo $index; ?>" <?php echo $index > 0 ? 'aria-expanded="false"' : 'aria-expanded="true"'; ?>>
                                    <?php echo htmlspecialchars($faq['question']); ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index == 0 ? 'show' : ''; ?>" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php $index++; endwhile; ?>
                </div>
            <?php else: ?>
                <!-- Default FAQs -->
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading1">
                            <button class="accordion-button" type="button" data-mdb-toggle="collapse" data-mdb-target="#collapse1" aria-expanded="true">
                                How do I download my purchased products?
                            </button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse show" data-mdb-parent="#faqAccordion">
                            <div class="accordion-body">
                                After completing your purchase, you can download your products from the "My Orders" section in your profile. Simply click the "Download" button next to each purchased item.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading2">
                            <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#collapse2" aria-expanded="false">
                                What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept various payment methods including credit/debit cards via Stripe, PayPal, and Razorpay. You can select your preferred payment method during checkout.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading3">
                            <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#collapse3" aria-expanded="false">
                                Can I get a refund?
                            </button>
                        </h2>
                        <div id="collapse3" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                            <div class="accordion-body">
                                Due to the nature of digital products, we generally do not offer refunds. However, if you encounter any issues with your purchase, please contact our support team and we'll do our best to assist you.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading4">
                            <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#collapse4" aria-expanded="false">
                                How do I use coupon codes?
                            </button>
                        </h2>
                        <div id="collapse4" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                            <div class="accordion-body">
                                During checkout, you'll find a coupon code field. Enter your code and click "Apply" to receive your discount. Only one coupon code can be used per order.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading5">
                            <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#collapse5" aria-expanded="false">
                                Do you offer free updates?
                            </button>
                        </h2>
                        <div id="collapse5" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! Most of our products come with free lifetime updates. You can download the latest version from your "My Orders" section whenever updates are available.
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-5">
                <p class="mb-3">Still have questions?</p>
                <a href="contact.php" class="btn btn-primary">Contact Support</a>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-bottom d-md-none pb-4">
    <div class="container-fluid justify-content-around">
        <a class="nav-link" href="index.php"><i class="fas fa-home"></i><br><small>Home</small></a>
        <a class="nav-link" href="products.php"><i class="fas fa-box"></i><br><small>Products</small></a>
        <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i><br><small>Cart</small></a>
        <a class="nav-link" href="profile.php"><i class="fas fa-user"></i><br><small>Profile</small></a>
    </div>
</nav>

<?php require_once 'includes/footer.php'; ?>
