<?php

$uploadDir = __DIR__ . "/uploads/";
$signedDir = __DIR__ . "/signed/";
$certPath = __DIR__ . "/certs/cert.p12";
$profilePath = __DIR__ . "/certs/profile.mobileprovision";
$certPass = "your_cert_password"; // Change this

if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
if (!is_dir($signedDir)) mkdir($signedDir, 0777, true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["ipa"])) {
    $ipa = $_FILES["ipa"];
    $ipaTmpPath = $ipa["tmp_name"];
    $ipaName = basename($ipa["name"]);
    $uploadedPath = $uploadDir . time() . "_" . $ipaName;

    if (move_uploaded_file($ipaTmpPath, $uploadedPath)) {
        $signedFileName = "signed_" . time() . "_" . $ipaName;
        $signedFilePath = $signedDir . $signedFileName;

        // Sign with zsign
        $cmd = escapeshellcmd("zsign -k '$certPath' -p '$certPass' -m '$profilePath' -o '$signedFilePath' '$uploadedPath'");
        exec($cmd, $out, $code);

        if ($code === 0) {
            header("Location: index.html?file=" . urlencode($signedFileName));
            exit;
        } else {
            echo "<div style='color:red;'>❌ Signing failed. Check cert or provisioning profile.</div>";
        }

    } else {
        echo "<div style='color:red;'>❌ Failed to upload IPA.</div>";
    }
} else {
    echo "<div style='color:red;'>❌ Invalid request.</div>";
}
