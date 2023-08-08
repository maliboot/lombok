<?php

declare(strict_types=1);

namespace MaliBoot\Lombok;

use Hyperf\Di\Aop\AstVisitorRegistry;
use Hyperf\Di\Aop\RegisterInjectPropertyHandler;
use MaliBoot\Lombok\Ast\LombokVisitor;
use MaliBoot\Lombok\contract\DelegateInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        if (! AstVisitorRegistry::exists(LombokVisitor::class)) {
            AstVisitorRegistry::insert(LombokVisitor::class, -1);
        }
        // Register Property Handler.
        RegisterInjectPropertyHandler::register();

        return [
            'commands' => [
            ],
            'dependencies' => [
                DelegateInterface::class => Delegate::class,
            ],
            'listeners' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
            ],
        ];
    }
}
