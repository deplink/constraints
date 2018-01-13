<?php

namespace Deplink\Constraints\Tests;

use Deplink\Constraints\Exceptions\TraversePathNotFoundException;
use Deplink\Constraints\Json;
use PHPUnit\Framework\Assert;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    private $json = [
        'name' => 'org/package',
        'scripts' => [
            'before-build:windows' => 'download dependencies',
            'before-build' => ['prepare solution', 'remove temp directory'],
            'after-build:linux' => 'deploy to cloud',
            'after-build:windows' => 'make installer',
            'after-build' => 'clean solution',
        ],
        'config' => [
            'compilers:linux' => [
                'gcc' => '-fPIC',
            ],
            'compilers:windows' => [
                'gcc' => '-Wall',
            ],
        ],
        'repositories' => [
            [
                'type' => 'local',
                'src:linux,mac' => '/path/to/repo',
                'src:windows' => 'c:/path/to/repo',
            ],
        ],
    ];

    private $constraintGroups = [
        ['static', 'dynamic'],
        ['windows', 'linux', 'mac'],
        ['x86', 'x64'],
    ];

    /**
     * Tested object instance.
     *
     * @var Json
     */
    protected $obj;

    /**
     * @before
     */
    public function prepareJson()
    {
        $this->obj = new Json($this->json);
        foreach ($this->constraintGroups as $group) {
            $this->obj->getContext()->group($group);
        }
    }

    /**
     * @dataProvider getMethodTestsProvider
     * @param string $key
     * @param string|string[] $constraints
     * @param mixed $expected
     * @throws TraversePathNotFoundException
     */
    public function testJson($key, $constraints, $expected)
    {
        $output = $this->obj->get($key, $constraints);
        Assert::assertEquals($expected, $output);
    }

    /**
     * @return array
     */
    public function getMethodTestsProvider()
    {
        return [
            'name #1' => [
                'name', [],
                'org/package',
            ],
            'scripts.before-build #1' => [
                'scripts.before-build', 'windows',
                ['download dependencies', 'prepare solution', 'remove temp directory'],
            ],
            'scripts.before-build #2' => [
                'scripts.before-build', 'linux',
                ['prepare solution', 'remove temp directory'],
            ],
            'scripts.before-build #3' => [
                'scripts.after-build', 'windows',
                ['make installer', 'clean solution'],
            ],
            'scripts.after-build #1' => [
                'scripts.after-build', [],
                ['deploy to cloud', 'make installer', 'clean solution'],
            ],
            'scripts.after-build #2' => [
                'scripts.after-build', 'linux',
                ['deploy to cloud', 'clean solution'],
            ],
            'scripts.after-build #3' => [
                'scripts.after-build', ['windows', 'linux'],
                ['deploy to cloud', 'make installer', 'clean solution'],
            ],
            'scripts.after-build #4' => [
                'scripts.after-build', 'mac',
                'clean solution',
            ],
            'scripts #1' => [
                'scripts', ['windows'],
                [
                    'before-build' => ['download dependencies', 'prepare solution', 'remove temp directory'],
                    'after-build' => ['make installer', 'clean solution'],
                ],
            ],
            'scripts #2' => [
                'scripts', 'mac',
                [
                    'before-build' => ['prepare solution', 'remove temp directory'],
                    'after-build' => 'clean solution',
                ],
            ],
            'config #1' => [
                'config', [],
                [
                    'compilers' => [
                        'gcc' => ['-fPIC', '-Wall'],
                    ],
                ],
            ],
            'repositories #1' => [
                'repositories', ['linux'],
                [
                    [
                        'type' => 'local',
                        'src' => '/path/to/repo',
                    ],
                ],
            ],
            'repositories.0 #1' => [
                'repositories.0', ['linux'],
                [
                    'type' => 'local',
                    'src' => '/path/to/repo',
                ],
            ],
            'repositories.0.src #1' => [
                'repositories.0.src', [],
                ['/path/to/repo', 'c:/path/to/repo'],
            ],
            'repositories.0.src #2' => [
                'repositories.0.src', ['mac'],
                '/path/to/repo',
            ],
        ];
    }
}
