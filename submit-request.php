<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();

header('Cache-Control: no-store');

require_once __DIR__ . '/config/database.php';

$errors = [];
$success = '';
$printType = '';
$copies = '1';
$colorMode = 'black_white';
$sides = 'single';
$specialInstructions = '';
$detectedPages = '';
$estimatedCost = 0.00;
$rateId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validCsrfToken()) {
        $errors[] = 'The form could not be verified. Refresh and try again.';
    }

    $printType = postText('print_type');
    $copies = postText('copies');
    $colorMode = postText('color_mode');
    $sides = postText('sides');
    $specialInstructions = postText('special_instructions');
    $detectedPages = postText('pages');

    if (!in_array($printType, ['document', 'presentation', 'poster'], true)) {
        $errors[] = 'Choose a valid print type.';
    }

    if (!in_array($colorMode, ['black_white', 'colour'], true)) {
        $errors[] = 'Choose a valid colour mode.';
    }
    if (!in_array($sides, ['single', 'double'], true)) {
        $errors[] = 'Choose a valid printing side option.';
    }
    if (!preg_match('/^[1-9][0-9]{0,3}$/', $copies)) {
        $errors[] = 'Enter a copy count between 1 and 999.';
    }

    if (!preg_match('/^[1-9][0-9]{0,3}$/', $detectedPages)) {
        $errors[] = 'The PDF page count could not be detected. Please upload the PDF again.';
    }

    if (mb_strlen($specialInstructions) > 500) {
        $errors[] = 'Special instructions must be 500 characters or fewer.';
    }

    if (!isset($_FILES['document'])) {
        $errors[] = 'Choose a PDF file to upload.';
    } else {
        $file = $_FILES['document'];

        if (
            $file['error'] !== UPLOAD_ERR_OK ||
            !is_uploaded_file($file['tmp_name'])
        ) {
            $errors[] = 'The upload failed. Please try again.';
        } elseif ($file['size'] <= 0 || $file['size'] > 20 * 1024 * 1024) {
            $errors[] = 'The PDF must be larger than zero and no more than 20 MB.';
        } else {
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $fileInfo->file($file['tmp_name']);

            if ($mimeType !== 'application/pdf') {
                $errors[] = 'Only PDF files are accepted.';
            }
        }
    }

    if ($errors === []) {
        $rateStatement = $pdo->prepare(
            'SELECT id, price_per_page
             FROM print_rates
             WHERE colour_mode = :colourmode
             LIMIT 1'
        );

        $rateStatement->execute([
            'colourmode' => $colorMode,
        ]);

        $rate = $rateStatement->fetch();

        if (!$rate) {
            $errors[] = 'Printing is currently unavailable for the selected colour mode.';
        } else {
           $rateId = (int) $rate['id'];

           $pageCount = (int) $detectedPages;
           $copyCount = (int) $copies;

        if ($sides === 'double') {
         $printedSides = (int) ceil($pageCount / 2);
         } else {
         $printedSides = $pageCount;
    }

           $estimatedCost = (float) $rate['price_per_page']
          * $printedSides
          * $copyCount;
        }
    }

    if ($errors === []) {
        $extension = '.pdf';
        $storedName = bin2hex(random_bytes(16)) . $extension;
        $uploadPath = __DIR__ . '/uploads/requests/' . $storedName;

        if (!is_dir(__DIR__ . '/uploads/requests')) {
            mkdir(__DIR__ . '/uploads/requests', 0775, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $errors[] = 'The file could not be saved. Please try again.';
        }
    }

    if ($errors === []) {
       $statement = $pdo->prepare(
    'INSERT INTO print_requests
        (student_id, rate_id, print_type, original_filename, stored_filename,
         page_count, copies, color_mode, sides, instructions,
         estimated_cost, status)
     VALUES
        (:student_id, :rate_id, :print_type, :original_filename, :stored_filename,
         :page_count, :copies, :color_mode, :sides, :instructions,
         :estimated_cost, :status)'
);

$statement->execute([
    'student_id' => $_SESSION['user_id'],
    'rate_id' => $rateId,
    'print_type' => $printType,
    'original_filename' => $file['name'],
    'stored_filename' => $storedName,
    'page_count' => (int) $detectedPages,
    'copies' => (int) $copies,
    'color_mode' => $colorMode,
    'sides' => $sides,
    'instructions' => $specialInstructions,
    'estimated_cost' => number_format($estimatedCost, 2, '.', ''),
    'status' => 'pending',
]);

        $success = 'Your printing request was submitted successfully. The system detected '
            . (int) $detectedPages . ' page(s). Estimated cost: GH₵'
            . number_format($estimatedCost, 2) . '.';

        $printType = '';
        $copies = '1';
        $colorMode = 'black_white';
        $specialInstructions = '';
        $detectedPages = '';
        $estimatedCost = 0.00;
        $rateId = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Submit a Print Request | CampusPrint</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a class="logo" href="index.php">
                Campus<span>Print</span>
            </a>

            <div class="header-actions">
                <a class="button button-secondary" href="dashboard.php">
                    Dashboard
                </a>

                <form action="logout.php" method="post">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= escape($_SESSION['csrf_token']) ?>"
                    >

                    <button class="button button-secondary" type="submit">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="form-page">
        <div class="container">
            <section class="form-card" aria-labelledby="request-heading">
                <p class="eyebrow">PRINTING REQUEST</p>

                <h1 id="request-heading">Submit a document for printing</h1>

                <p class="form-description">
                    Upload a PDF and provide the printing details.
                    The system will detect the page count and calculate the estimated cost.
                </p>

                <?php if ($success !== ''): ?>
                    <div class="alert alert-success" role="status">
                        <?= escape($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($errors !== []): ?>
                    <div class="alert alert-error" role="alert">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= escape($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form
                    id="print-request-form"
                    action="submit-request.php"
                    method="post"
                    enctype="multipart/form-data"
                >
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= escape($_SESSION['csrf_token']) ?>"
                    >

                    <div class="form-field">
                        <label for="print_type">Print type</label>

                        <select id="print_type" name="print_type" required>
                            <option value="">Choose a print type</option>
                            <option value="document" <?= $printType === 'document' ? 'selected' : '' ?>>
                                Document
                            </option>
                            <option value="presentation" <?= $printType === 'presentation' ? 'selected' : '' ?>>
                                Presentation
                            </option>
                            <option value="poster" <?= $printType === 'poster' ? 'selected' : '' ?>>
                                Poster
                            </option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="copies">Number of copies</label>

                        <input
                            type="number"
                            id="copies"
                            name="copies"
                            min="1"
                            max="999"
                            value="<?= escape($copies) ?>"
                            required
                        >
                    </div>
                    <div class="form-field">
                      <label for="sides">Printing sides</label>

                      <select id="sides" name="sides" required>
                      <option value="single" <?= $sides === 'single' ? 'selected' : '' ?>>
                         Single-sided
                     </option>
                       <option value="double" <?= $sides === 'double' ? 'selected' : '' ?>>
                       Double-sided
                       </option>
                     </select>
                   </div>        
                    <div class="form-field">
                        <label for="color_mode">Colour mode</label>

                        <select id="color_mode" name="color_mode" required>
                            <option value="black_white" <?= $colorMode === 'black_white' ? 'selected' : '' ?>>
                                Black and white
                            </option>
                            <option value="colour" <?= $colorMode === 'colour' ? 'selected' : '' ?>>
                                Colour
                            </option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="document">PDF document</label>

                        <input
                            type="file"
                            id="document"
                            name="document"
                            accept="application/pdf"
                            required
                        >

                        <p class="field-help">PDF only. Maximum size: 20 MB.</p>
                    </div>

                   <div class="form-field">
                      <label for="pages">Detected page count</label>

                       <input
                         type="text"
                         id="pages"
                         name="pages"
                         value="<?= escape($detectedPages) ?>"
                         readonly
                        >

                        <p class="field-help">
                              This is detected automatically from your PDF.
                           </p>
                     </div>
                    <div class="form-field">
                        <label for="special_instructions">Special instructions</label>

                        <textarea
                            id="special_instructions"
                            name="special_instructions"
                            maxlength="500"
                            rows="4"
                            placeholder="For example: print double-sided, staple the pages, or use A4 paper."
                        ><?= escape($specialInstructions) ?></textarea>
                    </div>

                    <div class="estimate-box">
                        <p>
                           Estimated cost:
                            <strong id="estimated-cost">GH₵0.00</strong>
                        </p>

                        <p class="field-help">
                            The final estimate is confirmed after the PDF is uploaded and checked.
                        </p>
                    </div>

                    <button class="button button-primary" type="submit">
                        Submit request
                    </button>
                </form>
            </section>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const documentInput = document.getElementById('document');
        const pagesInput = document.getElementById('pages');
        const copiesInput = document.getElementById('copies');
        const colorModeInput = document.getElementById('color_mode');
        const sidesInput = document.getElementById('sides');
        const estimatedCostDisplay = document.getElementById('estimated-cost');

        const rates = {
            black_white: 2.00,
            colour: 3.00,
        };

       function updateEstimate() {
       const pages = parseInt(pagesInput.value, 10) || 0;
       const copies = parseInt(copiesInput.value, 10) || 0;
       const colorMode = colorModeInput.value;
       const sides = sidesInput.value;
       const rate = rates[colorMode] || 0;

      let printedSides = pages;

      if (sides === 'double' && pages > 0) {
        printedSides = Math.ceil(pages / 2);
     }

     const estimate = rate * printedSides * copies;

      estimatedCostDisplay.textContent =
        'GH₵' + estimate.toFixed(2);
     }

        async function countPdfPages(file) {
            const arrayBuffer = await file.arrayBuffer();

            const pdf = await pdfjsLib.getDocument({
                data: arrayBuffer
            }).promise;

            return pdf.numPages;
        }

        documentInput.addEventListener('change', async () => {
            pagesInput.value = '';
            estimatedCostDisplay.textContent = 'GH₵0.00';

            const file = documentInput.files[0];

            if (!file) {
                return;
            }

            if (file.type !== 'application/pdf') {
                alert('Please choose a PDF file.');
                documentInput.value = '';
                return;
            }

            try {
                const pageCount = await countPdfPages(file);

                pagesInput.value = pageCount;
                updateEstimate();
            } catch (error) {
                alert('The PDF page count could not be read. Please try another PDF file.');
                documentInput.value = '';
            }
        });

        copiesInput.addEventListener('input', updateEstimate);
        colorModeInput.addEventListener('change', updateEstimate);
        sidesInput.addEventListener('change', updateEstimate);
    </script>
</body>
</html>