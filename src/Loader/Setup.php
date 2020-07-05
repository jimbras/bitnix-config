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

use Throwable,
    Bitnix\Config\ConfigException,
    Bitnix\Config\Interpolator,
    Bitnix\Config\IOException,
    Bitnix\Config\Loader,
    Bitnix\Config\Reader,
    Bitnix\Config\Settings,
    Bitnix\Config\Interpolator\ConstInterpolator,
    Bitnix\Config\Interpolator\EnvInterpolator,
    Bitnix\Config\Interpolator\InterpolatorSupport,
    Bitnix\Config\Reader\IniReader,
    Bitnix\Config\Reader\JsonReader,
    Bitnix\Config\Reader\PhpReader,
    Bitnix\Config\Reader\YamlReader;

/**
 * @version 0.1.0
 */
final class Setup {

    private const DEFAULT_READERS = [
        'ini'  => IniReader::CLASS,
        'json' => JsonReader::CLASS,
        'php'  => PhpReader::CLASS,
        'yml'  => YamlReader::CLASS
    ];

    private const DEFAULT_INTERPOLATORS = [
        'const' => ConstInterpolator::CLASS,
        'env'   => EnvInterpolator::CLASS
    ];

    /**
     * @var array
     */
    private array $readers = [];

    /**
     * @var Expander
     */
    private ?Expander $expander = null;

    /**
     * @var array
     */
    private array $interpolators = [];

    /**
     * @var array
     */
    private array $config = [];

    /**
     * ...
     */
    private function reset() : void {
        $this->expander = null;
        $this->readers = [];
        $this->interpolators = [];
        $this->config = [];
    }

    /**
     * @param Expander $expander
     * @return self
     */
    public function setExpander(Expander $expander) : self {
        $this->expander = $expander;
        return $this;
    }

    /**
     * @param array $defauls
     * @return self
     */
    public function setDefaultConfig(array $defaults) : self {
        $this->config = \array_merge_recursive($this->config, $defaults);
        return $this;
    }

    /**
     * @param string $ext
     * @param Reader $reader
     * @return self
     */
    public function addReader(string $ext, Reader $reader) : self {
        $this->readers[$ext] = $reader;
        return $this;
    }

    /**
     * @return self
     */
    public function addDefaultReaders() : self {
        foreach (self::DEFAULT_READERS as $ext => $fqcn) {
            $this->addReader($ext, new $fqcn());
        }
        return $this;
    }

    /**
     * @param string $ext
     * @param Interpolator $interpolator
     * @return self
     */
    public function addInterpolator(string $tag, Interpolator $interpolator) : self {
        $this->interpolators[$tag] = $interpolator;
        return $this;
    }

    /**
     * @return self
     */
    public function addDefaultInterpolators() : self {
        foreach (self::DEFAULT_INTERPOLATORS as $tag => $fqcn) {
            $this->addInterpolator($tag, new $fqcn());
        }
        return $this;
    }

    /**
     * @return Loader
     * @throws ConfigException
     */
    public function loader() : Loader {

        if (empty($this->readers)) {
            $this->addDefaultReaders();
        }

        if (empty($this->interpolators)) {
            $this->addDefaultInterpolators();
        }

        $expander = $this->expander ?: new class() implements Expander {
            public function expand(string $file) : array { return [$file]; }
            public function __toString() : string { return self::CLASS; }
        };

        try {
            return new class(
                $expander,
                $this->config,
                $this->readers,
                $this->interpolators
            ) implements Loader {

                use InterpolatorSupport;

                private const INTERPOLATE = '~
                    \$\{
                        (?:(?<tag>[^:]+):)?
                        (?<key>[^:}]+)
                        (?:\:-(?<default>[^}]+))?
                    \}~x';

                private Expander $expander;
                private array $files = [];
                private array $config;
                private array $readers;
                private array $interpolators;

                public function __construct(
                    Expander $expander,
                    array $config,
                    array $readers,
                    array $interpolators
                ) {
                    $this->expander = $expander;
                    $this->config = $config;
                    $this->readers = $readers;
                    $this->interpolators = $interpolators;
                }

                private function file($file) : string {
                    if (\is_file($file) && \is_readable($file)) {
                        return \realpath($file);
                    }

                    throw new IOException(\sprintf(
                        'Unable to find or read config file: %s', $file
                    ));
                }

                /**
                 * @return array
                 */
                public function files() : array {
                    return \array_keys($this->files);
                }

                /**
                 * @return array
                 */
                public function config() : array {
                    return $this->config;
                }

                private function read(string $file, array $config) : array {
                    if (isset($this->files[$file])) {
                        return $config;
                    }

                    $ext = \pathinfo($file, \PATHINFO_EXTENSION);

                    if (isset($this->readers[$ext])) {
                        return \array_replace_recursive(
                            $config, $this->readers[$ext]->read($file)
                        );
                    }

                    throw new IOException(\sprintf(
                        'Unable to find reader for config file: %s', $file
                    ));
                }

                private function find(string $key, array $config) {

                    if (\array_key_exists($key, $config)) {
                        return $config[$key];
                    }

                    if (false !== \strpos($key, '.')) {
                        $value = $config;
                        foreach (\explode('.', $key) as $k) {
                            if (!\is_array($value) || !\array_key_exists($k, $value)) {
                                return null;
                            }
                            $value = $value[$k];
                        }
                        return $value;
                    }

                    return null;
                }

                private function interpolate(array $config, array $matches) : string {
                    $tag = \trim($matches['tag']);
                    $key = \trim($matches['key']);
                    $default = \trim($matches['default'] ?? '');

                    if ($tag) {
                        if (!isset($this->interpolators[$tag])) {
                            throw new ConfigException(\sprintf(
                                'Unknown config interpolator: %s', $tag
                            ));
                        }
                        return $this->interpolators[$tag]->interpolate($key, $default);
                    }

                    if (null !== ($found = $this->find($key, $config))) {
                        return $this->value($found, '');
                    }

                    if (null !== ($found = $this->find($key, $this->config))) {
                        return $this->value($found, '');
                    }

                    return $default;
                }

                private function process(array $config, $value) {
                    if (\is_string($value)) {
                        $value = \preg_replace_callback(
                            self::INTERPOLATE,
                            fn($matches) => $this->interpolate($config, $matches),
                            $value
                        );
                    } else if (\is_array($value)) {
                        foreach ($value as $k => $v) {
                            $value[$k] = $this->process($config, $v);
                        }
                    }

                    return $value;
                }

                public function load(string $file, callable $filter = null) : Settings {
                    $file = $this->file($file);

                    if (isset($this->files[$file])) {
                        return new Settings($this->files[$file]);
                    }

                    try {

                        $config = [];
                        foreach ($this->expander->expand($file) as $source) {
                            $source = $this->file($source);
                            $config = $this->read($source, $config);
                            $this->files[$source] = $config;
                        }

                        foreach ($config as $key => $value) {
                            $config[$key] = $this->process($config, $value);
                        }

                        if ($filter) {
                            $config = $filter($config);
                            if (!\is_array($config)) {
                                throw new ConfigException(\sprintf(
                                    'Unexpected return value from config filter, '
                                        . 'expecting array but got %s',
                                            \gettype($config)
                                ));
                            }
                        }

                        $this->config = \array_replace_recursive(
                            $this->config, $config
                        );

                    } catch (ConfigException $cx) {
                        throw $cx;
                    } catch (Throwable $x) {
                        throw new ConfigException(
                            $x->getMessage(), $x->getCode(), $x
                        );
                    }

                    $this->files[$file] = $config;
                    return new Settings($config);
                }

                public function __toString() : string { return self::CLASS; }
            };
        } finally {
            $this->reset();
        }
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return self::CLASS;
    }
}
