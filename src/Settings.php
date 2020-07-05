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

/**
 * @version 0.1.0
 */
final class Settings {

    /**
     * @var array
     */
    private array $params;

    /**
     * @param array $params
     */
    public function __construct(array $params = []) {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function all() : array {
        return $this->params;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null) {

        if (\array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }

        if (false !== \strpos($key, '.')) {
            $params = $this->params;
            foreach (\explode('.', $key) as $k) {
                if (!\is_array($params) || !\array_key_exists($k, $params)) {
                    return $default;
                }
                $params = $params[$k];
            }
            return $params;
        }

        return $default;
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return self::CLASS;
    }
}
