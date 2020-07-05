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

namespace Bitnix\Config\Reader;

use Exception,
    Bitnix\Config\ConfigException,
    Bitnix\Config\IOException,
    Bitnix\Config\Reader,
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class AbstractReaderTest extends TestCase {

    private ?Reader $reader = null;

    public function setUp() : void {
        $this->reader = $this
            ->getMockBuilder(AbstractReader::CLASS)
            ->getMockForAbstractClass();
    }

    public function testReadValidFile() {
        $this->reader
            ->expects($this->once())
            ->method('process')
            ->will($this->returnCallback(function() {
                return ['foo' => 'bar'];
            }));
        $this->assertEquals(['foo' => 'bar'], $this->reader->read(__FILE__));
    }

    public function testReadInvalidFile() {
        $this->expectException(IOException::CLASS);
        $this->reader->read(__DIR__);
    }

    public function testReadError() {
        $this->expectException(ConfigException::CLASS);
        $this->reader
            ->expects($this->once())
            ->method('process')
            ->will($this->returnCallback(function() {
                echo $a; // no $a
                return [];
            }));
        $this->reader->read(__FILE__);
    }

    public function testReadRethrowConfigException() {
        $this->expectException(ConfigException::CLASS);
        $this->reader
            ->expects($this->once())
            ->method('process')
            ->will($this->throwException(new ConfigException()));
        $this->reader->read(__FILE__);
    }

    public function testReadWrapConfigException() {
        $this->expectException(ConfigException::CLASS);
        $this->reader
            ->expects($this->once())
            ->method('process')
            ->will($this->throwException(new Exception()));
        $this->reader->read(__FILE__);
    }

    public function testReadInvalidReturnType() {
        $this->expectException(ConfigException::CLASS);
        $this->reader
            ->expects($this->once())
            ->method('process')
            ->will($this->returnCallback(function() {
                return 1;
            }));
        $this->reader->read(__FILE__);
    }

    public function testToString() {
        $this->assertIsString((string) $this->reader);
    }

}
