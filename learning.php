<?php
// learning.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learn About Crops</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f9f4;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #2e7d32;
        }

        .section {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            padding: 20px;
        }

        .section img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            margin-top: 15px;
        }

        .section h2 {
            color: #388e3c;
        }

        .books, .references {
            padding-left: 20px;
        }

        footer {
            text-align: center;
            padding: 20px;
            background-color: #e0f2f1;
            color: #00695c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Learn About Crops</h1>

        <div class="section">
            <h2>1. Maize (Corn)</h2>
            <p>Maize is a staple food in many countries. It thrives in warm climates and requires adequate rainfall. It is used for food, animal feed, and biofuel.</p>
            <img src="sokop/maize.jpg">
        </div>

        <div class="section">
            <h2>2. Rice</h2>
            <p>Rice is the most widely consumed staple food. It requires a lot of water to grow and is usually cultivated in flooded fields.</p>
            <img src="sokop/klm.jpg">
        </div>

        <div class="section">
            <h2>3. Wheat</h2>
            <p>Wheat is a cereal grain that is a major food source globally. It grows well in temperate climates and is used to make bread, pasta, and other foods.</p>
            <img src="sokop/gog.jpg">
        </div>

        <div class="section">
            <h2>Recommended Books</h2>
            <ul class="books">
                <li><strong>"Principles of Agronomy for Sustainable Agriculture"</strong> - Francisco J. Villalobos</li>
                <li><strong>"Crop Ecology"</strong> - David J. Connor et al.</li>
                <li><strong>"Plant Production and Protection"</strong> - FAO Publications</li>
            </ul>
        </div>

        <div class="section">
            <h2>References</h2>
            <ul class="references">
                <li>Food and Agriculture Organization (FAO) - <a href="https://www.fao.org" target="_blank">fao.org</a></li>
                <li>Wikipedia - <a href="https://en.wikipedia.org/wiki/Crop" target="_blank">Crop</a></li>
                <li>National Geographic Agriculture Section - <a href="https://education.nationalgeographic.org/resource/agriculture" target="_blank">nationalgeographic.org</a></li>
            </ul>
        </div>
    </div>

    <footer>
        &copy; <?php echo date("Y"); ?> My Soko - Learning Platform
    </footer>
</body>
</html>
