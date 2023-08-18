## Lombok

### 简介
这是一个通过`注解`来减少`重复代码`的工具。
本插件参考了[Java库-Lombok](https://projectlombok.org/)奇思妙想编写而成，与其一样，致力于解放开发者的双手☕️～。最后，本插件的名称也以`lombok`向其致敬。

### 功能
* `Setter`
* `Getter`
* 日志
* 工具类方法，如`toArray`、`toCollection`等
* 类委托
* 类属性委托
* 增强已有注解功能
* 自定义组合注解功能
* 自定义扩展`lombok`
* ....

### 依赖
* `hyperf/di`

### 安装
- PHP包安装（必须）：`composer require maliboot/lombok`
- PHPStorm插件支持（可选）：`MaliBoot Support`，[下载传送门]()

### 使用
#### 1.1 `#[Getter]`基本使用，`#[ToArray]`、`#[ToCollection]`同理
1.1.1、在不使用`lombok`以前，我们实现一个`Getter`功能, 需要写如下代码：
```
// app/Foo.php
<?php
declare(strict_types=1);

namespace App;

class Foo
{
    private int $id = 1;
    
    public function getId(): int
    {
        return $this->id;
    }
}

var_dump((new Foo)->getId()); // output: 1
```

使用`lombok`后，我们只需要添加一个`Getter`注解，皆可省掉以上方法：
```
// app/Foo.php
<?php
declare(strict_types=1);

namespace App;

use MaliBoot\Lombok\Annotation\Getter;

// 当Getter为类注解时，会为所有的类属性添加Getter方法，无需要一个一个添加
// #[Getter]
class Foo
{
    // Getter为类属性注解时
    #[Getter]
    private int $id;
}
var_dump((new Foo)->getId()); // output: 1
```
#### 1.2 `#[Logger]`基本使用
* `#[Logger]`注解集成了`hyperf`日志系统，会自动给类添加`public \Psr\Log\LoggerInterface $logger`属性。
* `#[Logger(name:"CALL_CLASS_NAME", group: "default")]`中给了默认参数。其中`CALL_CLASS_NAME`会被插件自动替换为当前类名称。如下示例
```
// app/Foo.php
<?php
declare(strict_types=1);

namespace App;

use MaliBoot\Lombok\Annotation\Logger;

#[Logger]
class Foo
{
    private int $id;
    
    public function testLog(): void
    {
        $this-logger->error('errLog::' . __FUNCTION__);
    }
}
(new Foo)->testLog(); // runtime/hyperf.log记录: [2023-06-21 11:46:31] APP_Foo.ERROR: errLog::testLog [] []
```

#### 1.3 进阶用法
###### 1.3.1、可用注解如下

| 注解                                         | 功能                                                                      |
|:-------------------------------------------|:------------------------------------------------------------------------|
| `\MaliBoot\Lombok\Annotation\Getter`       | 生成类属性`Getter`方法                                                         |
| `\MaliBoot\Lombok\Annotation\Setter`       | 生成类属性`Setter`方法                                                         |
| `\MaliBoot\Lombok\Annotation\GetterSetter` | 集成`Setter`与`Getter`功能                                                   |
| `\MaliBoot\Lombok\Annotation\Logger`       | 生成类属性`public \Psr\Log\LoggerInterface $logger`                          |
| `\MaliBoot\Lombok\Annotation\ToArray`      | 生成类方法`public static function toArray(object $class): array`             |
| `\MaliBoot\Lombok\Annotation\ToCollection` | 生成类方法`public static function toCollection(object $class): Collection`方法 |
| `\MaliBoot\Lombok\Annotation\Of`           | 生成类方法`public static function of(array $fieldMap): self`方法                            |
| `\MaliBoot\Lombok\Annotation\Lombok`       | 集成以上所有注解功能                                                              |
| `\MaliBoot\Lombok\Annotation\Delegate`     | 生成类委托，可将本类不存在的常量、属性、方法委托给其它`interface`、`abstract class`、`class`处理（暂不支持含有抽象方法的接口与抽象类） |


###### 1.3.2、注解增强
当我们的项目里已有`DTO`注解，想为此注解支持`Getter`功能时，可以如下操作
* 在原有注解上，继承`\MaliBoot\Lombok\Contract\GetterAnnotationInterface`
```
// 原注解类
<?php

namespace App\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\GetterAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class DTO extends AbstractAnnotation implements GetterAnnotationInterface
{
}
```

* 注册AOP（本组件的功能基于`hyperf/di`的AOP代理文件，所以必须生成代理文件）
```
<?php
namespace App\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
class LombokAspect extends AbstractAspect
{
    // 此处注册所有的Lombok注解
    public array $annotations = [
        \App\Annotation\DTO::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // Do nothing, just to mark the class should be generated to the proxy classes.
        return $proceedingJoinPoint->process();
    }
}
```

* 完成以上两步骤，`\App\Annotation\DTO`此注解将自动拥有原`\MaliBoot\Lombok\Annotation\Getter`的功能
```
// app/Foo.php
<?php
declare(strict_types=1);

namespace App;

use MaliBoot\Lombok\Annotation\Getter;

// DTO为类属性注解时
// #[DTO]
class Foo
{
    // DTO为类属性注解时
    #[DTO]
    private int $id;
}
var_dump((new Foo)->getId()); // output: 1
```

###### 1.3.3、注解增强 - 自定义组合
当然，同时我们需要自定义一个（或者老项目里已有的一个类注解扩展支持）同时拥有`Setter`,`Getter`, `Logger`等多功能的注解，并且可以自定义组合这些功能时。那么同理，只需要在某注解上继续继承相应的注解接口皆可。可用的接口如下

| 注解接口                                                        | 功能                                                                                   |
|:------------------------------------------------------------|:-------------------------------------------------------------------------------------|
| `\MaliBoot\Lombok\Contract\GetterAnnotationInterface`       | 生成类属性`Getter`方法                                                                      |
| `\MaliBoot\Lombok\Contract\SetterAnnotationInterface`       | 生成类属性`Setter`方法                                                                      |
| `\MaliBoot\Lombok\Contract\LoggerAnnotationInterface`       | 生成类属性`public \Psr\Log\LoggerInterface $logger`                                       |
| `\MaliBoot\Lombok\Contract\ToArrayAnnotationInterface`      | 生成类方法`public static function toArray(): array`                                       |
| `\MaliBoot\Lombok\Contract\ToCollectionAnnotationInterface` | 生成类方法`public static function toCollection(): Collection`方法                           |
| `\MaliBoot\Lombok\Contract\OfAnnotationInterface`           | 生成类方法`public static function of(array $fieldMap): self`方法                            |
| `\MaliBoot\Lombok\Contract\DelegateAnnotationInterface`     | 生成类委托，可将本类不存在的常量、属性、方法委托给其它`interface`、`abstract class`、`class`处理（暂不支持含有抽象方法的接口与抽象类） |

###### 1.3.4、自定义`lombok`注解
当然，我们也可以自己做一个`lombok`功能。比如，我们给`./app/Foo.php`扩展一个`swap`方法。
*  第1步，添加一个注解接口，用于生成代理代码与自定义组合的凭据
```
<?php

namespace App\Contract;

interface SwapAnnotationInterface
{
}
```

* 第2步，添加`lombok`注解，并继承`\App\Contract\SwapAnnotationInterface`接口。
> 注意：此步骤非必须，如果已有现成的其它注解如`\App\Annotation\Controller`，可以直接在该注解内继承`swap`接口也可以
>> \App\Annotation\Controller implements \App\Contract\SwapAnnotationInterface
```
<?php

declare(strict_types=1);

namespace App\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use App\Contract\SwapAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Swap extends AbstractAnnotation implements SwapAnnotationInterface
{
}
```

*  第3步，`lombok注解`注册`AOP`
```
<?php

declare(strict_types=1);

namespace App\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use App\Annotation\Swap;

#[Aspect]
class SampleAspect extends AbstractAspect
{
    public array $annotations = [
        Swap::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // Do nothing, just to mark the class should be generated to the proxy classes.
        return $proceedingJoinPoint->process();
    }
}

```

* 第4步，`lombok`代码模板
```
<?php

declare(strict_types=1);

namespace App\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use App\Contract\SwapAnnotationInterface;

// 1、此处需要添加\MaliBoot\Lombok\Annotation\LombokGenerator注解
// 2、需要继承 \MaliBoot\Lombok\Ast\AbstractClassVisitor 或者 \MaliBoot\Lombok\Ast\AbstractClassFieldVisitor
// 2.1、\MaliBoot\Lombok\Ast\AbstractClassVisitor 会内置目标类的反射变量，以支持模版相关的变量替换用处
// 2.2、\MaliBoot\Lombok\Ast\AbstractClassFieldVisitor 会内置目标类所有属性的反射变量，以支持模版相关的变量替换用处
#[LombokGenerator]
class SwapGenerator extends AbstractClassVisitor
{
    // 自定义的方法OR属性名称
    protected function getClassMemberName(): string
    {
        return 'swap';
    }

    // 自定义组合注解功能时的接口凭据
    protected function getAnnotationInterface(): string
    {
        return SwapAnnotationInterface::class;
    }

    // 自定义的方法OR属性模板
    // 可自定义变量替换：提供有 AbstractClassVisitor::$reflectionClass 和 AbstractClassFieldVisitor::$reflectionProperty 配合使用
    protected function getClassCodeSnippet(): string
    {
        // 这里返回swap的方法的代码模板。类名称可以自定义，只是为看着整齐
        return <<<'CODE'
<?php
class Collection {
    public function swap (int &$a, int &$b): void {
        $tmp = $a;
        $a = $b;
        $b = $tmp;
    }
}
CODE;
    }
}

```

*  以上是扩展`lombok`的所有步骤，相关代码在`lombok/sample`内可见。那么下面可以直接使用了
```
// app/Foo.php
<?php
declare(strict_types=1);

namespace App;

use App\Annotation\Swap;

#[Swap]
class Foo
{
}

$left = 1;
$right = 2;
(new Foo)->swap($left, $right);
var_dump($left, $right); // output: 2, 1
```

### 注意事项
* 为了保证代码安全，当功能注解生成的代码与原代码冲突时，将放弃生成相应代码，以原代码为准。如`Setter`、`toArray`方法已存在，`public $logger`类属性已经存在等等