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

/**
 * Description of FileUpdateTest
 *
 * @author David Schoenbauer <dschoenbauer@gmail.com>
 */

function is_uploaded_file($filename) {
    if(FileUploadTest::$allowSuccess){
        return file_exists($filename);
    }
    return FileUploadTest::$allowSuccess;
}

function move_uploaded_file($filename, $destination) {
    if(FileUploadTest::$allowSuccess){
        return copy($filename, $destination);
    }
    return FileUploadTest::$allowSuccess;
}

class FileUploadTest extends \PHPUnit_Framework_TestCase {

    static $allowSuccess = false;
    protected $_object;
    protected $_types = [];
    protected $_targetFile;
    protected $_tempFile;

    protected function setUp() {
        self::$allowSuccess = true;
        $this->_targetFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test2';
        $this->_tempFile = tempnam(sys_get_temp_dir(), 'test');
        $this->_types = [
            'tmp' => 'inode/x-empty'
        ];
        $_FILES = [
            'fileHandle' => [
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($this->_tempFile),
                'tmp_name' => $this->_tempFile
            ]
        ];
        $this->_object = new FileUpload($this->_types, 5);
    }

    protected function tearDown() {
        if (file_exists($filename = $this->_targetFile . '.tmp')) {
            chmod($filename, '0755');
            unlink($filename);
        }
        if (file_exists($filename = $this->_tempFile)) {
            chmod($filename, '0755');
            unlink($filename);
        }
    }

    public function testAllowedTypes() {
        $this->assertEquals($this->_types, $this->_object->setAllowedTypes($this->_types)->getAllowedTypes());
    }

    public function testAllowedFileSize() {
        $this->assertEquals(5 * 1024 * 1024, $this->_object->setAllowedFileSize(5)->getAllowedFileSize());
    }

    public function testHandleFileSuccess() {

        $this->assertEquals(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test2.tmp', $this->_object->setAllowedTypes($this->_types)->handleFile('fileHandle', $this->_targetFile));
    }

    public function testHandleFileNoInvalidFiles() {
        unset($_FILES['fileHandle']['error']);
        $this->expectException(Exception\InvalidParametersException::class);
        $this->_object->handleFile('fileHandle', $this->_targetFile);
    }

    public function testHandleFileNoInvalidFilesArray() {
        $_FILES['fileHandle']['error'] = [];
        $this->expectException(Exception\InvalidParametersException::class);
        $this->_object->handleFile('fileHandle', $this->_targetFile);
    }

    public function testHandleFileNoFileSent() {
        $_FILES['fileHandle']['error'] = UPLOAD_ERR_NO_FILE;
        $this->expectExceptionMessage('');
        $this->expectException(Exception\NoFileSentException::class);
        $this->_object->handleFile('fileHandle', $this->_targetFile);
    }

    public function testHandleFileIniSize() {
        $_FILES['fileHandle']['error'] = UPLOAD_ERR_INI_SIZE;
        $this->expectExceptionMessage('');
        $this->expectException(Exception\FileTooLargeIniException::class);
        $this->_object->handleFile('fileHandle', $this->_targetFile);
    }

    public function testHandleFileFormSize() {
        $_FILES['fileHandle']['error'] = UPLOAD_ERR_FORM_SIZE;
        $this->expectExceptionMessage('');
        $this->expectException(Exception\FileTooLargeFormException::class);
        $this->_object->handleFile('fileHandle', $this->_targetFile);
    }

    public function testHandleFileUnknownError() {
        $_FILES['fileHandle']['error'] = 99;
        $this->expectExceptionMessage('');
        $this->expectException(Exception\UnknownErrorException::class);
        $this->_object->handleFile('fileHandle', $this->_targetFile);
    }

    public function testHandleFilePhpSize() {
        $_FILES['fileHandle']['size'] = 10 * 1024 * 1024;
        $this->expectExceptionMessage('');
        $this->expectException(Exception\FileTooLargePhpException::class);
        $this->_object->handleFile('fileHandle', $this->_targetFile);
    }

    public function testHandleFileUnsupportedMediaType() {
        $this->_object->setAllowedTypes(['jpg' => 'image/jpg']);
        $this->expectExceptionMessage('');
        $this->expectException(Exception\UnsupportedFileType::class);
        $this->_object->handleFile('fileHandle', $this->_targetFile);
    }

    public function testHandleFileFailedMove() {
        self::$allowSuccess = false;
        $this->expectExceptionMessage('');

        $this->expectException(Exception\FileFailedToMoveException::class);
        $this->_object->handleFile('fileHandle', $this->_targetFile);
    }

}
