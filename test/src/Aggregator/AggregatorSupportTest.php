<?php declare(strict_types=1);

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program. If not, see <https://www.gnu.org/licenses/agpl-3.0.txt>.
 */

namespace Bitnix\Config\Aggregator;

use Bitnix\Config\IOException,
    PHPUnit\Framework\TestCase;

/**
 * ...
 *
 * @version 0.1.0
 */
class AggregatorSupportTest extends TestCase {

    use AggregatorSupport;

    public function testStoreCreateDirectoryError() {
        $this->expectException(IOException::CLASS);

        $path = __DIR__ . '/_cache';
        \chmod($path, 0000 & ~\umask());
        try {
            @$this->store(__DIR__ . '/_cache/_path/file.txt', 'data');
        } finally {
            \chmod($path, 0755 & ~\umask());
        }
    }

    public function testStoreCreateFileError() {
        $this->expectException(IOException::CLASS);

        $path = __DIR__ . '/_cache';
        \chmod($path, 0000 & ~\umask());
        try {
            @$this->store(__DIR__ . '/_cache/file.txt', 'data');
        } finally {
            \chmod($path, 0755 & ~\umask());
        }
    }

    public function testUnlinkError() {
        $this->expectException(IOException::CLASS);

        $path = __DIR__ . '/_cache';
        $file = $path . '/file.txt';

        $this->assertFalse(\is_file($file));
        \touch($file);
        $this->assertTrue(\is_file($file));

        \chmod($path, 0000 & ~\umask());
        try {
            @$this->unlink($file);
        } finally {
            \chmod($path, 0755 & ~\umask());
            \unlink($file);
        }
    }
}
