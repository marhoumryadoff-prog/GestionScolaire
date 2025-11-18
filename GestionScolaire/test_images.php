<?php
$images = [
    'uploads/image1.png',
    'uploads/Corrige Exam 2023(1).pdf',
    'uploads/Corrige Exam 2023(1) (2).pdf'
];

foreach($images as $img) {
    if(file_exists($img)) {
        echo "<p>✅ ملف موجود: " . $img . "</p>";
        if(pathinfo($img, PATHINFO_EXTENSION) == 'png') {
            echo '<img src="' . $img . '" width="200"><hr>';
        }
    } else {
        echo "<p>❌ ملف غير موجود: " . $img . "</p>";
    }
}
?>