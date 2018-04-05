<?php declare(strict_types=1);

/*
 * This processor is an add-on to the Monolog package.
 *
 * This file is based heavily on the work implemented in the `IntrospectionProcessor`
 * which is part of the main Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 * (c) Luke Waite <lwaite@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LukeWaite\MonologFullIntrospectionProcessor;

use Monolog\Logger;

/**
 * Injects a trace of where the log message came from
 *
 * Warning: This only works if the handler processes the logs directly.
 * If you put the processor on a handler that is behind a FingersCrossedHandler
 * for example, the processor will only be called once the trigger level is reached,
 * and all the log records will have the same trace data from the call that
 * triggered the FingersCrossedHandler.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Luke Waite <lwaite@gmail.com>
 */
class FullIntrospectionProcessor
{
    private $level;

    public function __construct($level = Logger::DEBUG)
    {
        $this->level = Logger::toMonologLevel($level);
    }

    public function __invoke(array $record): array
    {
        // return if the level is not high enough
        if ($record['level'] < $this->level) {
            return $record;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        array_shift($trace);
        array_shift($trace);

        $record['extra'] = array_merge(
            $record['extra'],
            [
                'trace' => $this->getTraceAsString($trace)
            ]
        );

        return $record;
    }

    private function getTraceAsString(array $trace): string
    {
        $data = '';
        for ($i = 0; $i < count($trace); $i++) {
            $data .= $this->getTraceItemAsString($i, $trace[$i]);
        }

        return $data;
    }

    private function getTraceItemAsString(int $position, array $trace): string
    {
        $string = '#' . $position . ' ';


        if (isset($trace['file'])) {
            $string .= $trace['file'] . '(' . $trace['line'] . '): ';
        } else {
            $string .= '[internal function]: ';
        }
        if (isset($trace['class'])) {
            $string .= $trace['class'] . $trace['type'];
        }

        $string .= $trace['function'] . "\n";

        return $string;
    }
}
