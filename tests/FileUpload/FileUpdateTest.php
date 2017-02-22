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
class FileUpdateTest extends \PHPUnit_Framework_TestCase {

    protected $_object;
    protected $_types = [];


    protected function setUp() {
        $this->_types = [
            'jpg' => 'image/jpeg'
        ];
        $_FILES = [
            'fileHandle' => [
                
            ]
        ];
        $this->_object = new FileUpload();
    }

    public function testAllowedTypes(){
        $this->assertEquals($this->_types, $this->_object->setAllowedTypes($this->_types)->getAllowedTypes());
    }
    
    public function testAllowedFileSize(){
        $this->assertEquals(5 * 1024 * 1024, $this->_object->setAllowedFileSize(5)->getAllowedFileSize());
    }
    
    public function testHandleFileSuccess(){
        $this->assertEquals('uploads/test.jpg',$this->_object->handleFile('fileHandle', 'uploads/test'));
    }
}
