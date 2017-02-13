<?php

namespace PhpOffice\PhpSpreadsheet\Shared;

/**
 * Copyright (c) 2006 - 2016 PhpSpreadsheet.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @category   PhpSpreadsheet
 *
 * @copyright  Copyright (c) 2006 - 2016 PhpSpreadsheet (https://github.com/PHPOffice/PhpSpreadsheet)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class File
{
    /*
     * Use Temp or File Upload Temp for temporary files
     *
     * @protected
     * @var boolean
     */

    protected static $useUploadTempDirectory = false;

    /**
     * Set the flag indicating whether the File Upload Temp directory should be used for temporary files.
     *
     * @param bool $useUploadTempDir Use File Upload Temporary directory (true or false)
     */
    public static function setUseUploadTempDirectory($useUploadTempDir = false)
    {
        self::$useUploadTempDirectory = (bool) $useUploadTempDir;
    }

    /**
     * Get the flag indicating whether the File Upload Temp directory should be used for temporary files.
     *
     * @return bool Use File Upload Temporary directory (true or false)
     */
    public static function getUseUploadTempDirectory()
    {
        return self::$useUploadTempDirectory;
    }

    /**
     * Verify if a file exists.
     *
     * @param string $pFilename Filename
     *
     * @return bool
     */
    public static function fileExists($pFilename)
    {
        // Sick construction, but it seems that
        // file_exists returns strange values when
        // doing the original file_exists on ZIP archives...
        if (strtolower(substr($pFilename, 0, 3)) == 'zip') {
            // Open ZIP file and verify if the file exists
            $zipFile = substr($pFilename, 6, strpos($pFilename, '#') - 6);
            $archiveFile = substr($pFilename, strpos($pFilename, '#') + 1);

            $zipClass = \PhpOffice\PhpSpreadsheet\Settings::getZipClass();
            $zip = new $zipClass();
            if ($zip->open($zipFile) === true) {
                $returnValue = ($zip->getFromName($archiveFile) !== false);
                $zip->close();

                return $returnValue;
            }

            return false;
        }
            // Regular file_exists
            return file_exists($pFilename);
    }

    /**
     * Returns canonicalized absolute pathname, also for ZIP archives.
     *
     * @param string $pFilename
     *
     * @return string
     */
    public static function realpath($pFilename)
    {
        // Returnvalue
        $returnValue = '';

        // Try using realpath()
        if (file_exists($pFilename)) {
            $returnValue = realpath($pFilename);
        }

        // Found something?
        if ($returnValue == '' || ($returnValue === null)) {
            $pathArray = explode('/', $pFilename);
            while (in_array('..', $pathArray) && $pathArray[0] != '..') {
                for ($i = 0; $i < count($pathArray); ++$i) {
                    if ($pathArray[$i] == '..' && $i > 0) {
                        unset($pathArray[$i], $pathArray[$i - 1]);

                        break;
                    }
                }
            }
            $returnValue = implode('/', $pathArray);
        }

        // Return
        return $returnValue;
    }

    /**
     * Get the systems temporary directory.
     *
     * @return string
     */
    public static function sysGetTempDir()
    {
        if (self::$useUploadTempDirectory) {
            //  use upload-directory when defined to allow running on environments having very restricted
            //      open_basedir configs
            if (ini_get('upload_tmp_dir') !== false) {
                if ($temp = ini_get('upload_tmp_dir')) {
                    if (file_exists($temp)) {
                        return realpath($temp);
                    }
                }
            }
        }

        // sys_get_temp_dir is only available since PHP 5.2.1
        // http://php.net/manual/en/function.sys-get-temp-dir.php#94119
        if (!function_exists('sys_get_temp_dir')) {
            if ($temp = getenv('TMP')) {
                if ((!empty($temp)) && (file_exists($temp))) {
                    return realpath($temp);
                }
            }
            if ($temp = getenv('TEMP')) {
                if ((!empty($temp)) && (file_exists($temp))) {
                    return realpath($temp);
                }
            }
            if ($temp = getenv('TMPDIR')) {
                if ((!empty($temp)) && (file_exists($temp))) {
                    return realpath($temp);
                }
            }

            // trick for creating a file in system's temporary dir
            // without knowing the path of the system's temporary dir
            $temp = tempnam(__FILE__, '');
            if (file_exists($temp)) {
                unlink($temp);

                return realpath(dirname($temp));
            }

            return null;
        }

        // use ordinary built-in PHP function
        //    There should be no problem with the 5.2.4 Suhosin realpath() bug, because this line should only
        //        be called if we're running 5.2.1 or earlier
        return realpath(sys_get_temp_dir());
    }

    /**
     * Assert that given path is an existing file and is readable, otherwise throw exception.
     *
     * @param string $filename
     *
     * @throws \InvalidArgumentException
     */
    public static function assertFile($filename)
    {
        if (!is_file($filename)) {
            throw new \InvalidArgumentException('File "' . $filename . '" does not exist.');
        }

        if (!is_readable($filename)) {
            throw new \InvalidArgumentException('Could not open "' . $filename . '" for reading.');
        }
    }
}