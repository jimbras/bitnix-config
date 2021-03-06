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

use Bitnix\Config\Interpolator;

/**
 * @version 0.1.0
 */
final class EnvInterpolator implements Interpolator {

    use InterpolatorSupport;

    /**
     * @param string $value
     * @param null|string $default
     * @return null|string
     */
    public function interpolate(string $value, string $default = null) : ?string {
        $found = null;

        if (\array_key_exists($value, $_SERVER)) {
            $found = $_SERVER[$value];
        } else if (\array_key_exists($value, $_ENV)) {
            $found = $_ENV[$value];
        } else if (false !== ($env = \getenv($value))) {
            $found = $env;
        }

        return $this->value($found, $default);
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return self::CLASS;
    }

}
