<?php
require_once 'includes/functions.php';

// Redirect to dashboard if already logged in
if (is_logged_in()) {
    header("Location: pages/dashboard.php");
    exit();
}

// Define APP_NAME if not already defined
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Debre Birhan Digital Library');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Empowering Knowledge</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Modern Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="assets/images/debre_brhan.png" alt="Debre Birhan Digital Library Logo" style="height: 40px; margin-right: 10px;">
                <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item"><a class="btn btn-primary custom-btn-primary me-2" href="pages/login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-outline-primary custom-btn-outline" href="pages/register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <!-- Hero Section -->
        <section id="home" class="hero-section d-flex align-items-center text-center text-white">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <h1 class="display-3 fw-bold mb-4">Empowering Knowledge Through Digital Access.</h1>
                        <p class="lead mb-5">Discover, Borrow, and Manage Books Effortlessly — Anytime, Anywhere in Debre Birhan.</p>
                        <div class="input-group mb-4 search-bar-custom mx-auto">
                            <input type="text" class="form-control custom-form-control" placeholder="Search for books, authors, or categories..." aria-label="Search books">
                            <button class="btn btn-warning custom-btn-accent" type="button" id="button-addon2"><i class="fas fa-search"></i> Search</button>
                        </div>
                        <div class="hero-buttons">
                            <a href="pages/dashboard.php" class="btn btn-primary custom-btn-primary btn-lg me-3">Explore Library</a>
                            <a href="pages/register.php" class="btn btn-outline-light custom-btn-outline-light btn-lg">Join Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="features-section py-5">
            <div class="container">
                <h2 class="text-center display-5 fw-bold mb-3">Powerful Features</h2>
                <p class="text-center text-muted mb-5">Everything you need to manage Debre Birhan Wereda 4 No.2 Digital Library efficiently.</p>
                
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card-custom text-center p-4">
                            <div class="icon-wrapper mx-auto mb-3"><i class="fas fa-search fa-2x"></i></div>
                            <h4 class="fw-bold">Smart Search</h4>
                            <p class="text-muted">Find any book instantly with our advanced search system.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card-custom text-center p-4">
                            <div class="icon-wrapper mx-auto mb-3"><i class="fas fa-book-reader fa-2x"></i></div>
                            <h4 class="fw-bold">Borrow Tracking</h4>
                            <p class="text-muted">Track borrowed and returned books with ease and accuracy.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card-custom text-center p-4">
                            <div class="icon-wrapper mx-auto mb-3"><i class="fas fa-tachometer-alt fa-2x"></i></div>
                            <h4 class="fw-bold">User Dashboard</h4>
                            <p class="text-muted">Personalized space for readers to manage their library use.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card-custom text-center p-4">
                            <div class="icon-wrapper mx-auto mb-3"><i class="fas fa-globe fa-2x"></i></div>
                            <h4 class="fw-bold">Digital Catalog</h4>
                            <p class="text-muted">Access Debre Birhan’s book collection from anywhere, anytime.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="about-section py-5 bg-light">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h2 class="display-5 fw-bold mb-3">About <?php echo APP_NAME; ?></h2>
                        <p class="lead text-muted mb-4">
                            Debre Birhan Digital Library  is a modern digital platform created to make knowledge more accessible to our community. It enables users to discover, borrow, and manage books efficiently through an easy-to-use digital system.
                        </p>
                        <p class="text-muted">
                            Whether you’re a student, teacher, or researcher, this platform supports your learning by providing online resources, tracking borrowed books, and offering a digital reading experience — all in one place.
                        </p>
                    </div>
                    <div class="col-lg-6">
                        <img src="assets/images/debre_brhan.png" alt="Library Dashboard Mockup" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </section>

        <!-- Highlights Section -->
        <section class="highlights-section py-5">
            <div class="container">
                <div class="row text-center">
                    <div class="col-md-4 mb-4">
                        <div class="highlight-card p-4">
                            <h3 class="display-4 fw-bold custom-text-primary">10,000+</h3>
                            <p class="lead text-muted">Books Managed</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="highlight-card p-4">
                            <h3 class="display-4 fw-bold custom-text-primary">500+</h3>
                            <p class="lead text-muted">Active Members</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="highlight-card p-4">
                            <h3 class="display-4 fw-bold custom-text-primary">24/7</h3>
                            <p class="lead text-muted">Access Available</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Partners Section -->
        <section class="partners-section py-5 bg-light">
            <div class="container">
                <h2 class="text-center display-5 fw-bold mb-5">Our Partners</h2>
                <div class="row align-items-center justify-content-center">
                    <div class="col-md-6 text-center">
                        <img src="assets/images/debre_brhan.png" alt="Partner Logo 1" class="img-fluid partner-logo">
                    </div>
                    <div class="col-md-6 text-center">
                        <img src="assets/images/eem.jfif" alt="Partner Logo 2" class="img-fluid partner-logo">
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer id="contact" class="cta-footer py-5 text-white text-center">
        <div class="container">
            <h2 class="display-5 fw-bold mb-3">Join Debre Birhan Digital Library Today</h2>
            <p class="lead mb-4">Discover, learn, and grow through our digital reading platform.</p>
            <div class="cta-buttons">
                <a href="pages/register.php" class="btn btn-warning custom-btn-accent btn-lg me-3">Sign Up</a>
                <a href="mailto:ethcodecoders@gmail.com" class="btn btn-outline-light custom-btn-outline-light btn-lg">Contact Admin</a>
            </div>
            <hr class="my-5 border-light">
            <div class="footer-bottom">
                <p class="mb-0">&copy; <?php echo date("Y"); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                <p class="mb-0">Powered by <strong>EthcoCoders</strong> | <a href="https://ethcocoders.gt.tc" target="_blank" class="text-white text-decoration-underline">ethcocoders.gt.tc</a></p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.querySelector('.custom-navbar');
            window.addEventListener('scroll', function() {
                navbar.classList.toggle('scrolled', window.scrollY > 50);
            });

            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) target.scrollIntoView({ behavior: 'smooth' });
                });
            });

            document.querySelectorAll('.feature-card-custom').forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px)';
                    card.style.boxShadow = '0 10px 20px rgba(0,0,0,0.15)';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                    card.style.boxShadow = '0 4px 8px rgba(0,0,0,0.05)';
                });
            });
        });
    </script>
</body>
</html>
