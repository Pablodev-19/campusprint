<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CampusPrint | Campus Printing Made Simple</title>

    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a class="logo" href="index.php">Campus<span>Print</span></a>

            <button
                class="menu-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="main-navigation"
                hidden
            >
                Menu
            </button>

            <nav id="main-navigation" class="navigation" aria-label="Main navigation">
                <a href="#how-it-works">How it works</a>
                <a href="#estimate">Cost estimate</a>
                <a href="login.php">Log in</a>
                <a href="register.php">Create account</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-layout">
                <div>
                    <p class="eyebrow">YOUR CAMPUS PRINTING DESK, ONLINE</p>

                    <h1>Less time in queues.<br>More time for what matters.</h1>

                    <p class="hero-description">
                        Submit your PDF, choose your printing preferences,
                        and track your request until it is ready for collection.
                    </p>

                    <div class="button-group">
                        <a class="button button-primary" href="#estimate">
                            Estimate your cost
                        </a>

                        <a class="button button-secondary" href="#how-it-works">
                            See how it works
                        </a>
                    </div>
                </div>

                <aside class="summary-card" aria-labelledby="service-heading">
                    <span class="badge">One campus printing shop</span>

                    <h2 id="service-heading">Printing without the guesswork</h2>

                    <ul class="service-list">
                        <li>PDF document submissions</li>
                        <li>Black-and-white or colour printing</li>
                        <li>Single-sided or double-sided options</li>
                        <li>Request progress tracking</li>
                    </ul>

                    <p class="collection-note">
                        Collect your documents and pay at the shop.
                    </p>
                </aside>
            </div>
        </section>

        <section id="how-it-works" class="section">
            <div class="container">
                <p class="eyebrow">A SIMPLE PROCESS</p>
                <h2>How CampusPrint will work</h2>

                <div class="steps-grid">
                    <article class="step-card">
                        <span class="step-number">01</span>
                        <h3>Submit your document</h3>
                        <p>
                            Log in, upload your PDF, and select your printing
                            preferences and number of copies.
                        </p>
                    </article>

                    <article class="step-card">
                        <span class="step-number">02</span>
                        <h3>Track your request</h3>
                        <p>
                            Check your dashboard as staff review and process
                            your printing request.
                        </p>
                    </article>

                    <article class="step-card">
                        <span class="step-number">03</span>
                        <h3>Collect and pay</h3>
                        <p>
                            Visit the shop when your request is marked
                            ready for collection.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section id="estimate" class="section estimate-section">
            <div class="container estimate-layout">
                <div>
                    <p class="eyebrow">PLAN YOUR PRINTING</p>
                    <h2>Try the cost calculator</h2>

                    <p>
                        Enter the number of pages in one document and how many
                        copies you need.
                    </p>

                    <p class="notice">
                        Prototype only: this calculator uses sample rates of
                        GHS 0.50 per black-and-white page side and GHS 2.00 per
                        colour page side. These are not confirmed shop prices.
                    </p>

                    <p>
                        Double-sided printing uses the same per-page-side rate
                        in this version. Staff will confirm the final cost.
                    </p>
                </div>

                <form id="estimate-form" class="calculator">
                    <div class="form-field">
                        <label for="page-count">Pages in one document</label>
                        <input
                            type="number"
                            id="page-count"
                            name="page_count"
                            min="1"
                            step="1"
                            value="1"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="copies">Number of copies</label>
                        <input
                            type="number"
                            id="copies"
                            name="copies"
                            min="1"
                            step="1"
                            value="1"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="colour-mode">Printing category</label>
                        <select id="colour-mode" name="colour_mode">
                            <option value="black_white">Black and white</option>
                            <option value="colour">Colour</option>
                        </select>
                    </div>

                    <button class="button button-primary" type="submit">
                        Calculate estimate
                    </button>

                    <p
                        id="estimate-result"
                        class="estimate-result"
                        role="status"
                    >
                        Your sample estimate will appear here.
                    </p>

                    <noscript>
                        Enable JavaScript to use this calculator.
                    </noscript>
                </form>
            </div>
        </section>

        <section id="project-status" class="section">
    <div class="container">
        <div class="development-note">
            <h2>Student registration is available</h2>

            <p>
                Create your student account now. Login, document submission,
                and request tracking are being built next.
            </p>

            <div class="button-group">
                <a class="button button-primary" href="register.php">
                    Create account
                </a>
            </div>
        </div>
    </div>
</section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>
                &copy; <?= date('Y') ?> CampusPrint.
            
            </p>
        </div>
    </footer>
</body>
</html>