<?php

define('DS', DIRECTORY_SEPARATOR);
define('CACHE_DIR', __DIR__ . DS . 'cache' . DS);

// $url = "https://downloads.dell.com/catalog/catalog.xml.gz";
$url = "https://dl.dell.com/catalog/catalog.xml.gz";

if ((isFileCacheValid(CACHE_DIR . 'catalog.xml.gz') && isFileCacheValid(CACHE_DIR . 'catalog.xml')) === FALSE) {
    download($url, CACHE_DIR . 'orig_catalog.xml.gz');
    uncompress(CACHE_DIR . 'orig_catalog.xml.gz', CACHE_DIR . 'orig_catalog.xml');

    $doc = new DOMDocument();
    $doc->load(CACHE_DIR . 'orig_catalog.xml');
    $doc->getElementsByTagName('Manifest')->item(0)->setAttribute('baseLocation', 'dl.dell.com');
    $doc->save(CACHE_DIR . 'catalog.xml');

    compress(CACHE_DIR . 'catalog.xml', CACHE_DIR . 'catalog.xml.gz');

    unlink(CACHE_DIR . 'orig_catalog.xml.gz');
    unlink(CACHE_DIR . 'orig_catalog.xml');
}

$pathInfo = filter_input(INPUT_SERVER, 'REQUEST_URI');
$filename = pathinfo($pathInfo,  PATHINFO_BASENAME);
$filename = strtolower($filename);

switch ($filename) {
    case 'catalog.xml':
        header('Content-Type: application/xml');
        echo file_get_contents(CACHE_DIR . 'catalog.xml');
        break;
    case 'catalog.xml.gz':
        header('Content-Type: application/octet-stream');
        echo file_get_contents(CACHE_DIR . 'catalog.xml.gz');
        break;
    default:
        echo "You shall not pass";
        break;
}

function uncompress($srcFile, $dstFile) {
    $sfp = gzopen($srcFile, "rb");
    $dfp = fopen($dstFile, "w");

    while ($string = gzread($sfp, 4096)) {
        fwrite($dfp, $string, strlen($string));
    }

    gzclose($sfp);
    fclose($dfp);
}

function compress($srcFile, $dstFile) {
    $sfp = fopen($srcFile, "rb");
    $dfp = gzopen($dstFile, "w");

    while ($string = fread($sfp, 4096)) {
        gzwrite($dfp, $string, strlen($string));
    }

    fclose($sfp);
    gzclose($dfp);
}

function download($url, $dstFile) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0');
	//curl_setopt($ch, CURLOPT_PROXY, '192.168.160.252:10086');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $content = curl_exec($ch);
    curl_close($ch);
    unset($ch);

    file_put_contents($dstFile, $content);
}

function isFileCacheValid($dstFile) {
    if ( ! file_exists($dstFile)) {
        return false;
    }

    if (time() - filemtime($dstFile) > 86400) {
        return false;
    }

    return true;
}
