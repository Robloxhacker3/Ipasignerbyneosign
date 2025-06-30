<?php

$uploadDir = __DIR__ . "/uploads/";
$signedDir = __DIR__ . "/signed/";
$certPath = __DIR__ . "/certs/cert.p12";
$profilePath = __DIR__ . "/certs/profile.mobileprovision";
$certPass = "your_cert_password"; // 🔐 Replace this

if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
if (!is_dir($signedDir)) mkdir($signedDir, 0777, true);

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["github_url"])) {
    $url = trim($_POST["github_url"]);

    if (!filter_var($url, FILTER_VALIDATE_URL) || !str_ends_with($url, ".ipa")) {
        die("<div style='color:red;'>❌ Invalid GitHub IPA URL.</div>");
    }

    $ipaName = "downloaded_" . time() . ".ipa";
    $ipaPath = $uploadDir . $ipaName;

    // Download IPA from GitHub
    $ipaData = @file_get_contents($url);
    if (!$ipaData) {
        die("<div style='color:red;'>❌ Could not download IPA from GitHub. Make sure it's a direct link.</div>");
    }
    file_put_contents($ipaPath, $ipaData);

    // Output path
    $signedName = "signed_" . time() . ".ipa";
    $signedPath = $signedDir . $signedName;

    // Sign using zsign
    $cmd = escapeshellcmd("zsign -k '$certPath' -p '$certPass' -m '$profilePath' -o '$signedPath' '$ipaPath'");
    exec($cmd, $out, $returnCode);

    if ($returnCode === 0) {
        echo "<div style='text-align:center; color:lime; font-family:sans-serif;'>
              ✅ Signed Successfully!<br><br>
              <a href='signed/$signedName' download>Download Signed IPA</a>
              </div>";
    } else {
        echo "<div style='color:red;'>❌ Signing failed. Check ZSign, cert, or profile.</div>";
    }
} else {
    echo "<div style='color:red;'>❌ No GitHub URL provided.</div>";
}

function str_ends_with($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
}
