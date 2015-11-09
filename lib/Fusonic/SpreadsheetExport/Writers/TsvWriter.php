<?php

/*
 * Copyright (c) 2012-2013 Fusonic GmbH
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

namespace Fusonic\SpreadsheetExport\Writers;

class TsvWriter extends CsvWriter
{
    public function __construct()
    {
        $this->charset = self::CHARSET_ISO;
    }

    public function getContentType()
    {
        return "text/tab-separated-values";
    }

    public function getDefaultExtension()
    {
        return "tsv";
    }

    private function normalizeValue($value)
    {
        return str_replace("\t", " ", $value);
    }

    public function getContent(array $columns, array $data, $flags = null)
    {
        // Create a temporary filestream
        $fd = fopen("php://temp", "r+");

        // Write headers
        if ($this->includeColumnHeaders) {
            $columnHeaders = array();

            foreach ($columns as $column) {
                $columnHeaders[] = $this->normalizeValue($column->title);
            }

            fputs($fd, implode("\t", $columnHeaders) . "\n");
        }

        // Write content
        foreach ($data as $row) {
            if (!is_array($row)) {
                throw new \Exception("Row is not an array.");
            }

            foreach ($row as &$field) {
                if ($field instanceof \DateTime) {
                    $field = $field->format("Y-m-d H:i:s");
                } else {
                    if (is_string($field)) {
                        $field = $this->normalizeValue($field);
                    }
                }
            }

            fputs($fd, implode("\t", $row) . "\n");
        }

        // Read content
        rewind($fd);
        $content = "";
        while ($chunk = fread($fd, self::READ_CHUNK_SIZE)) {
            $content .= $chunk;
        }

        // Clean up
        fclose($fd);

        // Return correctly encoded content
        switch ($this->charset) {
            case self::CHARSET_ISO:
                return utf8_decode($content);
            default:
                return $content;
        }
    }
}
