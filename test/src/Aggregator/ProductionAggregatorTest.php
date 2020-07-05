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

use PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class ProductionAggregatorTest extends TestCase {

    private string $file = __DIR__  . '/_cache/file.txt';

    public function tearDown() : void {
        if (\is_file($this->file)) {
            \unlink($this->file);
        }
    }

    public function testAggregator() {
        $aggregator = new ProductionAggregator();

        $this->assertFalse(\is_file($this->file));
        $this->assertFalse($aggregator->fresh($this->file));

        $extra = __DIR__ . '/_source3.txt';
        $this->assertFalse(\is_file($extra));
        \touch($extra);
        $this->assertTrue(\is_file($extra));

        try {

            $sources = [
                __DIR__ . '/_source1.txt',
                __DIR__ . '/_source2.txt',
                $extra
            ];

            $aggregator->aggregate($this->file, 'content', ...$sources);
            $this->assertTrue(\is_file($this->file));
            $this->assertTrue($aggregator->fresh($this->file));
            $this->assertEquals('content', \file_get_contents($this->file));

            \unlink($extra);
            $this->assertTrue($aggregator->fresh($this->file));

            $aggregator->destroy($this->file);
            $aggregator->destroy($this->file);
            $this->assertFalse(\is_file($this->file));
        } finally {
            if (\is_file($extra)) {
                \unlink($extra);
            }
        }
    }

    public function testToString() {
        $this->assertIsString((string) new ProductionAggregator());
    }
}
