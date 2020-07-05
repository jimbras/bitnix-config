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

use Throwable,
    Bitnix\Config\ConfigException,
    Bitnix\Config\IOException,
    Bitnix\Config\Reader;

/**
 * @version 0.1.0
 */
abstract class AbstractReader implements Reader {

    /**
     * @param string $file
     * @return mixed
     * @throws \Throwable
     */
    protected abstract function process(string $file);

    /**
     * @param string $file
     * @return array
     * @throws IOException
     * @throws ConfigException
     */
    public function read(string $file) : array {

        if (!($path = \realpath($file)) || !\is_file($path) || !\is_readable($path)) {
            throw new IOException(\sprintf(
                'Unable to find or read configuration file "%s"', $file
            ));
        }

        \set_error_handler(function($code, $message, $file, $line) use ($path) {
            throw new ConfigException(\sprintf(
                'Failed to parse configuration file "%s": %s',
                    $path,
                    $message
            ));
        });

        try {
            $config = $this->process($path);
        } catch (ConfigException $cx) {
            throw $cx;
        } catch (Throwable $x) {
            throw new ConfigException(\sprintf(
                'Unexpected error while reading config file "%s"', $path
            ), 0, $x);
        } finally {
            \restore_error_handler();
        }

        if (!\is_array($config)) {
            throw new ConfigException(\sprintf(
                'Configuration file "%s" return format error, expected array but got %s',
                    $path,
                    \is_object($config) ? \get_class($config) : \gettype($config)
            ));
        }

        return $config;
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return self::CLASS;
    }

}
