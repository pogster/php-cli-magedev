<?php
/**
 * This file is part of the teamneusta/php-cli-magedev package.
 *
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 *
 * @license https://opensource.org/licenses/mit-license MIT License
 */

namespace TeamNeusta\Magedev\Test\Commands\Docker;

use Mockery as m;
use TeamNeusta\Magedev\Commands\Docker\StopCommand;

/**
 * Class: StopCommandTest.
 *
 * @see \PHPUnit_Framework_TestCase
 */
class StopCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $input = m::mock('\Symfony\Component\Console\Input\InputInterface');
        $output = m::mock('\Symfony\Component\Console\Output\ConsoleOutput[]', ['writeln']);

        $dockerManager = m::mock('\TeamNeusta\Magedev\Docker\Manager');
        $dockerManager->shouldReceive('stopContainers')->times(1);
        $dockerService = m::mock(
            '\TeamNeusta\Magedev\Services\DockerService',
            [
                'getManager' => $dockerManager,
            ]
        );
        $command = new StopCommand($dockerService);
        $command->execute($input, $output);
    }
}
