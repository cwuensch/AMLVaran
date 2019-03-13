<?php
session_start();

include_once "inc/constants.inc.php";
include_once "inc/class.samples.inc.php";

if(isset($_GET['pid']) && isset($_GET['sid']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1)
{
    $samples = new DkhSamples();
    $pid=$_GET['pid'];
    $sid=$_GET['sid'];

    $validsid = $samples->checkSampleID($sid);

    if ($validsid[0] == 1) {
        if ($validsid[1] == true) {
            switch($_GET['type'])
            {
                case 'bam':
                    $filename='Sample.bam';
                    break;
                case 'bai':
                    $filename='Sample.bai';
                    break;
                case 'vcf':
                    $filename='Variants.vcf';
                    break;
                default:
                    $filename='Sample' . $_GET['type']; 
                    break;
            }

            $location="../samples/$pid/$sid/$filename";
            $mimeType='application/octet-stream';

            if (!file_exists($location))
            {
                header ("HTTP/1.1 404 Not Found");
                return;
            }

            date_default_timezone_set("Europe/Berlin");
            $size  = filesize($location);
            $time  = date('r', filemtime($location));

            $fm = @fopen($location, 'rb');
            if (!$fm)
            {
                header ("HTTP/1.1 505 Internal server error");
                return;
            }

            $begin  = 0;
            $end  = $size - 1;

            if (isset($_SERVER['HTTP_RANGE']))
            {
                if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches))
                {
                    $begin  = intval($matches[1]);
                    if (!empty($matches[2]))
                {
                    $end  = intval($matches[2]);
                }
                }
            }
            if (isset($_SERVER['HTTP_RANGE']))
            {
                header('HTTP/1.1 206 Partial Content');
            }
            else
            {
                header('HTTP/1.1 200 OK');
            }

            header("Content-Type: $mimeType");
            header('Cache-Control: public, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Accept-Ranges: bytes');
            header('Content-Length:' . (($end - $begin) + 1));
            if (isset($_SERVER['HTTP_RANGE']))
            {
                header("Content-Range: bytes $begin-$end/$size");
            }
            header("Content-Disposition: inline; filename=$filename");
            header("Content-Transfer-Encoding: binary");
            header("Last-Modified: $time");

            $cur  = $begin;
            fseek($fm, $begin, 0);

            while(!feof($fm) && $cur <= $end && (connection_status() == 0))
            {
                print fread($fm, min(1024 * 16, ($end - $cur) + 1));
                $cur += 1024 * 16;
            }

        } else {
            header ("HTTP/1.1 403 Forbidden");
            return;
        }
    } else {
        header ("HTTP/1.1 403 Forbidden");
        return;
    }
} else {
    header ("HTTP/1.1 403 Forbidden");
    return;
}
?>
