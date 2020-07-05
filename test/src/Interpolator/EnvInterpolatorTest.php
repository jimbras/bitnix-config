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
class EnvInterpolatorTest extends TestCase {

    public function testIterpolate() {

        $_SERVER['__FOO__'] = 'bar';
        $_SERVER['__BAR__'] = ['baz', 'bam'];
        $_ENV['__ZIG__'] = 'zag';
        \putenv('__ZOID__=berg');

        $env = new EnvInterpolator();

        try {

            $this->assertEquals('bar', $env->interpolate('__FOO__'));
            $this->assertEquals('bar', $env->interpolate('__FOO__', 'baz'));
            $this->assertEquals('', $env->interpolate('__BAR__'));
            $this->assertEquals('baz', $env->interpolate('__BAR__', 'baz'));

            $this->assertEquals('zag', $env->interpolate('__ZIG__'));
            $this->assertEquals('zag', $env->interpolate('__ZIG__', 'zoid'));

            $this->assertEquals('berg', $env->interpolate('__ZOID__'));
            $this->assertEquals('berg', $env->interpolate('__ZOID__', 'nerd'));

            $this->assertEquals('', $env->interpolate('__UNDEF__'));
            $this->assertEquals('def', $env->interpolate('__UNDEF__', 'def'));
        } finally {
            unset($_SERVER['__FOO__']);
            unset($_SERVER['__BAR__']);
            unset($_ENV['__ZIG__']);
            putenv('__ZOID__');
        }
    }

    public function testToString() {
        $this->assertIsString((string) new EnvInterpolator());
    }

}
