<?php

/*
 * The MIT License
 *
 * Copyright 2017 David Schoenbauer <dschoenbauer@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace DSchoenbauer\FileUpload;

use DSchoenbauer\Exception\Platform\RuntimeException;
use finfo;

/**
 * One class to manage all PHP file upload needs
 *
 * @author David Schoenbauer <dschoenbauer@gmail.com>
 * @since 1.0.0
 */
class FileUpload {

    private $allowedTypes = [];
    private $allowedFileSize = 0;

    public function __construct(array $allowedTypes = [], $allowedFileSize = 0) {
        $this->setAllowedTypes($allowedTypes)->setAllowedFileSize($allowedFileSize);
    }

    function getAllowedTypes() {
        return $this->allowedTypes;
    }

    /**
     * Defines which file types are allowed
     * @param array $allowedTypes expects an array with a file extention as the key and the value as the mime-type
     * @return $this
     */
    function setAllowedTypes(array $allowedTypes) {
        $this->allowedTypes = $allowedTypes;
        return $this;
    }

    /**
     * 
     * @return integer returns the maximum file size allowed
     */
    public function getAllowedFileSize() {
        return $this->allowedFileSize;
    }

    /**
     * 
     * @param integer $allowedFileSize File size in MegaBytes
     * @return $this
     */
    public function setAllowedFileSize($allowedFileSize) {
        $this->allowedFileSize = $allowedFileSize * 1024 * 1024;
        return $this;
    }

    public function handleFile($fileHandle, $targetFile) {
        if (!isset($_FILES[$fileHandle]['error']) || is_array($_FILES[$fileHandle]['error'])) {
            throw new Exception\InvalidParametersException();
        }
        switch ($_FILES[$fileHandle]['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new Exception\NoFileSentException();
            case UPLOAD_ERR_INI_SIZE:
                throw new Exception\FileTooLargeIniException();
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception\FileTooLargeFormException();
            default:
                throw new Exception\UnknownErrorException();
        }

        if ($_FILES[$fileHandle]['size'] > $this->getAllowedFileSize()) {
            throw new Exception\FileTooLargePhpException($this->getAllowedFileSize());
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
                $finfo->file($_FILES[$fileHandle]['tmp_name']), $this->getAllowedTypes(), true
                )) {
            throw new Exception\UnsupportedFileType(array_keys($this->getAllowedTypes()));
        }

        $targetFile .= "." . $ext;
        //var_dump($_FILES[$fileHandle]['tmp_name'], $targetFile, dirname($targetFile),is_writable(dirname($targetFile)));

        if (!move_uploaded_file($_FILES[$fileHandle]['tmp_name'], $targetFile)) {
            throw new Exception\FileFailedToMoveException();
        }
        return $targetFile;
    }

}
