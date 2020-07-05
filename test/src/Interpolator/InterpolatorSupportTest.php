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

namespace Bitnix\Config\Interpolator;

use PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class InterpolatorSupportTest extends TestCase {

    use InterpolatorSupport;

    public function testValue() {
        $this->assertNull($this->value([]));
        $this->assertEquals('foo', $this->value([], 'foo'));

        $this->assertSame('1', $this->value(true));
        $this->assertSame('0', $this->value(false));

        $this->assertSame('foo', $this->value('foo'));
        $this->assertSame('foo', $this->value('foo', 'bar'));

        $this->assertSame('123', $this->value(123));
        $this->assertSame('123', $this->value(123, '456'));

        $this->assertSame('1.23', $this->value(1.23));
        $this->assertSame('1.23', $this->value(1.23, '4.56'));

        $this->assertNull($this->value(\log(0)));
    }
}
