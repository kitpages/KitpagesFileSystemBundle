<?php

namespace Kitpages\FileSystemBundle\Util;

class Util
{

    /**
     * Sets the filter options
     * Allowed options are
     *     'allowTags'     => Tags which are allowed
     *     'allowAttribs'  => Attributes which are allowed
     *     'allowComments' => Are comments allowed ?
     *
     * @param  string|array $options
     * @param string $text text to filter
     * @return string text filtered
     */
    public function stripTags($option, $text)
    {
        $filter = new StripTags($option);
        return $filter->filter($text);
    }

    /**
     * Create a directory and all subdirectories needed.
     * @param string $pathname
     * @param octal $mode example 0666
     */
    public function mkdirr($pathname, $mode = null)
    {
        // Check if directory already exists
        if (is_dir($pathname) || empty($pathname)) {
            return true;
        }
        // Ensure a file does not already exist with the same name
        if (is_file($pathname)) {
            return false;
        }
        // Crawl up the directory tree
        $nextPathname = substr($pathname, 0, strrpos($pathname, "/"));
        if ($this->mkdirr($nextPathname, $mode)) {
            if (!file_exists($pathname)) {
                if (is_null($mode)) {
                    return mkdir($pathname);
                } else {
                    return mkdir($pathname, $mode);
                }
            }
        } else {
            throw new \Exception (
                "intermediate mkdirr $nextPathname failed"
            );
        }
        return false;
    }

    /**
     * remove recursively directory
     * @param string $dir Physical directory to remove
     */
    public function rmdirr($dir)
    {
        if ($handle = opendir("$dir")) {
            while ($item = readdir($handle)) {
                if (($item != ".") && ($item != "..")) {
                    if (is_dir("$dir/$item")) {
                        $this->rmdirr("$dir/$item");
                    } else {
                        unlink("$dir/$item");
                    }
                }
            }
            closedir($handle);
            rmdir($dir);
        }
    }

    /**
     * send the content of a file to the output by chuncks in order to
     * limite the memory consumption.
     * @param $filename
     * @param $retbytes
     * @return stream of bytes by chunks of 1Mo
     */
    public function readfileChunked($filename, $retbytes = false)
    {
        $chunksize = 1 * (1024 * 1024); // how many bytes per chunk
        $buffer = '';
        $cnt = 0;
        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            echo $buffer;
            ob_flush();
            flush();
            if ($retbytes) {
                $cnt += $this->binaryStrLen($buffer);
            }
        }
        $status = fclose($handle);
        if ($retbytes && $status) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }
        return $status;
    }

    /**
     *
     * @param $file file to send (typically an image)
     * @param $cacheTime , cache time in seconds for the browser
     * @param $mime mime type
     * @return void
     */
    public function getFile($file, $cacheTime, $mime = null, $name = null)
    {
        //First, see if the file exists
        if (!is_file($file)) {
            throw new \Exception(
                "Download Manager : file [$file] doesn't exist"
            );
        }

        // ENO modif, required for IE
        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        $ctype = $this->getMimeContentType($file);
        if ($mime != null) {
            $ctype = $mime;
        }

        header('Cache-Control: public, max-age=' . $cacheTime);
        header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $cacheTime) . " GMT");
        header('Pragma: cache');
        header('Content-type: ' . $ctype);
        header('Content-length: ' . filesize($file));
        if ($name != null) {
            header("Content-Disposition: attachment; filename=\"" . $name . "\"");
        }
        $this->readfileChunked($file);
        exit;
    }

    /**
     * returns the mime content type of a $file. Use file_info if it is
     * installed
     * @param string $fileName
     * @return string mime content type
     */
    public function getMimeContentType($file, $fileName = null)
    {

        if ($fileName == null) {
            $fileName = $file;
        }

        $mimeTypes = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            'avi' => "video/x-msvideo",

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',

            // application store
            // over the air blackberry
            'jad' => 'text/vnd.sun.j2me.app-descriptor',
            // over the air blackberry
            'cod' => 'application/vnd.rim.cod',
            // over the air Android
            'apk' => 'application/vnd.android.package-archive',
            // blackberry over the air
            'jar' => 'application/java-archive'
        );

        $ext = strtolower(pathinfo("$fileName", PATHINFO_EXTENSION));
        if (array_key_exists($ext, $mimeTypes)) {
            return $mimeTypes[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimetype = finfo_file($finfo, $file);
            finfo_close($finfo);
            return $mimetype;
        }
        return 'application/octet-stream';
    }

    /**
     * returns encode a sring in url
     * @param string $value
     * @return string
     */
    public function urlPathEncode($value)
    {
        // cas de la valeur null
        if (is_null($value)) {
            return "";
        }
        // plus d'accents
        $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕç';
        $b = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRrc';
        $value = utf8_decode($value);
        $value = strtr($value, utf8_decode($a), $b);
        $value = strtolower($value);
        // ponctuation
        $value = strtr($value, utf8_decode("'\""), "__");
        // autres caracteres
        $value = preg_replace("/[^a-z0-9\-\_]/", "-", $value);
        return $value;
    }
}

?>
