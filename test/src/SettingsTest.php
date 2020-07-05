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

namespace Bitnix\Config;

use PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class SettingsTest extends TestCase {

    private ?Settings $settings = null;

    public function setUp() : void {
        $this->settings = new Settings([
            'foo' => [
                'bar' => [
                    'baz' => 'bam'
                ]
            ],
            'zig'  => 'zag',
            'list' => [1, 2, 3],
            'void' => null
        ]);
    }

    public function testAll() {
        $this->assertEquals([
            'foo' => [
                'bar' => [
                    'baz' => 'bam'
                ]
            ],
            'zig'  => 'zag',
            'list' => [1, 2, 3],
            'void' => null
        ], $this->settings->all());
    }

    public function testGet() {
        $this->assertNull($this->settings->get('missing'));
        $this->assertEquals('value', $this->settings->get('missing', 'value'));

        $this->assertNull($this->settings->get('void'));
        $this->assertNull($this->settings->get('void', 'void'));

        $this->assertEquals('bam', $this->settings->get('foo.bar.baz'));
        $this->assertEquals(['baz' => 'bam'], $this->settings->get('foo.bar'));
        $this->assertEquals(['bar' => ['baz' => 'bam']], $this->settings->get('foo'));

        $this->assertNull($this->settings->get('foo.baz'));
        $this->assertEquals('bam', $this->settings->get('foo.baz', 'bam'));

        $this->assertEquals('zag', $this->settings->get('zig'));

        $this->assertEquals(3, $this->settings->get('list.2'));
        $this->assertNull($this->settings->get('list.2.value'));
        $this->assertEquals('default', $this->settings->get('list.2.value', 'default'));
    }

    public function testToString() {
        $this->assertIsString((string) $this->settings);
    }
}
