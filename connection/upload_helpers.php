<?php
/**
 * Shared helpers for upload handling.
 * The stored file extension is always derived from the detected MIME type,
 * never from the client-supplied filename.
 */

const FAMS_IMAGE_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

const FAMS_VIDEO_TYPES = [
    'video/mp4'        => 'mp4',
    'video/mpeg'       => 'mpeg',
    'video/quicktime'  => 'mov',
    'video/x-msvideo'  => 'avi',
    'video/webm'       => 'webm',
];

/** Detect the real MIME type from file contents. */
function famsDetectMime($tmpPath) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    return $finfo->file($tmpPath) ?: '';
}

/** Create an upload folder that cannot execute scripts or be listed. */
function famsEnsureUploadDir($dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $htaccess = $dir . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, famsUploadHtaccess());
    }
    $index = $dir . '/index.php';
    if (!file_exists($index)) {
        file_put_contents($index, "<?php\nheader(\"HTTP/1.0 403 Forbidden\");\necho \"Access Denied\";\n");
    }
}

function famsUploadHtaccess() {
    return "# Uploaded files must never be executed as scripts\n"
         . "<FilesMatch \"(?i)\\.(php[0-9]?|phtml|phar|pl|py|cgi|sh)\$\">\n"
         . "    Require all denied\n"
         . "</FilesMatch>\n"
         . "<IfModule mod_php.c>\n    php_flag engine off\n</IfModule>\n"
         . "<IfModule mod_php7.c>\n    php_flag engine off\n</IfModule>\n";
}

/** Unpredictable, collision-free file name. */
function famsRandomName($prefix, $ext) {
    return $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
}
