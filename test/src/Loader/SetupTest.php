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

namespace Bitnix\Config\Loader;

use Bitnix\Config\ConfigException,
    Bitnix\Config\IOException,
    Bitnix\Config\Settings,
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class SetupTest extends TestCase {

    public function testToString() {
        $this->assertIsString((string) new Setup());
    }

    public function testBasicLoader() {
        $setup = new Setup();
        $loader = $setup->loader();

        $this->assertEquals([], $loader->config());
        $this->assertEquals([], $loader->files());

        $foo = $loader->load(__DIR__ . '/_config/foo.php');
        $this->assertInstanceOf(Settings::CLASS, $foo);
        $this->assertEquals(['foo' => ['bar' => 'baz']], $foo->all());

        $this->assertEquals(['foo' => ['bar' => 'baz']], $loader->config());
        $this->assertEquals([__DIR__ . '/_config/foo.php'], $loader->files());

        $foo2 = $loader->load(__DIR__ . '/_config/foo.php');
        $this->assertNotSame($foo, $foo2);
        $this->assertEquals(['foo' => ['bar' => 'baz']], $foo2->all());
        $this->assertEquals(['foo' => ['bar' => 'baz']], $loader->config());
        $this->assertEquals([__DIR__ . '/_config/foo.php'], $loader->files());


        $foo = $loader->load(__DIR__ . '/_config/foo_dev.php');
        $this->assertInstanceOf(Settings::CLASS, $foo);
        $this->assertEquals(['foo' => ['bar' => 'bam']], $foo->all());

        $this->assertEquals(['foo' => ['bar' => 'bam']], $loader->config());
        $this->assertEquals([
            __DIR__ . '/_config/foo.php',
            __DIR__ . '/_config/foo_dev.php'
        ], $loader->files());
    }

    public function testLoadWithFilter() {
        $loader = (new Setup())->loader();
        $foo = $loader->load(__DIR__ . '/_config/foo.php', fn($data) => ['zig' => 'zag']);
        $this->assertEquals(['zig' => 'zag'], $foo->all());
        $this->assertEquals(['zig' => 'zag'], $loader->config());
    }

    public function testLoaderWithFileExpander() {
        $expander = new class() implements Expander {
            public function expand($file) : array {
                return [
                    $file,
                    \str_replace('.php', '_dev.php', $file),
                    \str_replace('.php', '_dev.php', $file)
                ];
            }
        };
        $loader = (new Setup())
            ->setExpander($expander)
            ->loader();

        $foo = $loader->load(__DIR__ . '/_config/foo.php');
        $this->assertEquals(['foo' => ['bar' => 'bam']], $foo->all());

        $this->assertEquals(['foo' => ['bar' => 'bam']], $loader->config());
        $this->assertEquals([
            __DIR__ . '/_config/foo.php',
            __DIR__ . '/_config/foo_dev.php'
        ], $loader->files());
    }

    public function testLoaderWithDefaultConfig() {
        $config = [
            'app' => [
                'name'    => 'test',
                'version' => '1.2.3'
            ]
        ];

        $loader = (new Setup())
            ->setDefaultConfig($config)
            ->loader();

        $this->assertEquals($config, $loader->config());
    }

    public function testBakedInInterpolation() {
        $config = [
            'app' => [
                'name'    => 'test',
                'version' => '1.2.3'
            ]
        ];

        $loader = (new Setup())
            ->setDefaultConfig($config)
            ->loader();

        $app = $loader->load(__DIR__ . '/_config/app.php');
        $this->assertEquals([
            'app' => [
                'data' => '...',
                'full_name' => 'test/1.2.3',
                'local' => '...',
                'bad' => '',
                'missing' => ''
            ]
        ], $app->all());

        $this->assertEquals([
            'app' => [
                'name'    => 'test',
                'version' => '1.2.3',
                'data' => '...',
                'full_name' => 'test/1.2.3',
                'local' => '...',
                'bad' => '',
                'missing' => ''
            ]
        ], $loader->config());
    }

    public function testDefaultInterpolators() {
        $loader = (new Setup())
            ->addDefaultInterpolators()
            ->loader();

        try {
            $env = $_ENV;
            $_ENV['APP_ZIG'] = 'zag';
            $zig = $loader->load(__DIR__ . '/_config/zig.ini');
        } finally {
            $_ENV = $env;
        }

        $this->assertEquals([
            'eol' => \PHP_EOL,
            'zig' => 'zag/zoid'
        ], $zig->all());
    }

    public function testLoadMissingFile() {
        $this->expectException(IOException::CLASS);
        $loader = (new Setup())->loader();
        $loader->load(__DIR__ . '/_config/not_a_file.ini');
    }

    public function testLoadMissingReader() {
        $this->expectException(IOException::CLASS);
        $loader = (new Setup())
            ->loader();
        $loader->load(__DIR__ . '/_config/no_reader.toml');
    }

    public function testLoadMissingInterpolator() {
        $this->expectException(ConfigException::CLASS);
        $loader = (new Setup())->loader();
        $loader->load(__DIR__ . '/_config/no_interpolator.ini');
    }

    public function testBadFilterReturnValue() {
        $this->expectException(ConfigException::CLASS);
        $loader = (new Setup())->loader();
        $loader->load(__DIR__ . '/_config/foo.php', fn($data) => null);
    }

    public function testWrapUnexpectedError() {
        $this->expectException(ConfigException::CLASS);
        $loader = (new Setup())->loader();
        $loader->load(__DIR__ . '/_config/foo.php', function($data) {
            throw new \Exception('kaput');
        });
    }

    public function testLoaderToString() {
        $this->assertIsString((string) (new Setup())->loader());
    }
}
